#!/usr/bin/env php
<?php

/**
 * Deploy pack installer.
 *
 * Looks at a Laravel app (composer.lock, package.json, routes, .env.example)
 * and installs the deploy pack into it, already adjusted for that app:
 *
 *   - PHP extensions that composer packages require  -> Dockerfile
 *   - queue worker / scheduler / Reverb needed?      -> docker-compose.yaml
 *   - VITE_* variables the frontend reads            -> Dockerfile + .env.build
 *   - image name (from git remote + folder name)     -> compose + build scripts
 *   - a Coolify environment checklist for THIS app   -> .env.coolify
 *
 * Usage:  php init.php [app-folder] [--image=ghcr.io/user/name] [--db=mysql] [--dry-run] [--force] [--yes]
 * Needs PHP 8.1+ (any Laravel developer machine has it).
 */

declare(strict_types=1);

const PLACEHOLDER_IMAGE = 'ghcr.io/your-github-user/your-app';

/** Present in the official php:*-fpm-alpine image without any extra work. */
const BUNDLED_EXTENSIONS = [
    'core', 'ctype', 'curl', 'date', 'dom', 'fileinfo', 'filter', 'hash', 'iconv', 'json', 'libxml',
    'mbstring', 'mysqlnd', 'openssl', 'pcre', 'pdo', 'pdo_sqlite', 'phar', 'posix', 'random',
    'readline', 'reflection', 'session', 'simplexml', 'sodium', 'spl', 'sqlite3', 'standard',
    'tokenizer', 'xml', 'xmlreader', 'xmlwriter', 'opcache', 'zend-opcache', 'zlib',
];

/** Installed by the Dockerfile's base stage already. */
const PACK_EXTENSIONS = [
    'pdo_mysql', 'pdo_pgsql', 'mbstring', 'zip', 'exif', 'pcntl', 'bcmath', 'gd', 'intl', 'opcache',
];

/** Extensions only useful while developing: never installed in the image. */
const DEV_ONLY_EXTENSIONS = ['xdebug', 'pcov'];

/** Built from PHP's own source: docker-php-ext-install <name>, plus Alpine -dev packages. */
const DOCKER_EXTENSIONS = [
    'mysqli' => [],
    'pgsql' => [],
    'sockets' => ['linux-headers'],
    'calendar' => [],
    'shmop' => [],
    'sysvmsg' => [],
    'sysvsem' => [],
    'sysvshm' => [],
    'gettext' => ['gettext-dev'],
    'bz2' => ['bzip2-dev'],
    'soap' => ['libxml2-dev'],
    'xsl' => ['libxslt-dev'],
    'gmp' => ['gmp-dev'],
    'ldap' => ['openldap-dev'],
    'tidy' => ['tidyhtml-dev'],
    'ffi' => ['libffi-dev'],
];

/** Installed with pecl: 'build' packages are removed afterwards, 'runtime' packages stay. */
const PECL_EXTENSIONS = [
    'redis' => ['build' => [], 'runtime' => []],
    'apcu' => ['build' => [], 'runtime' => []],
    'mongodb' => ['build' => ['openssl-dev'], 'runtime' => []],
    'memcached' => ['build' => ['libmemcached-dev', 'zlib-dev'], 'runtime' => ['libmemcached-libs']],
    'imagick' => ['build' => ['imagemagick-dev'], 'runtime' => ['imagemagick', 'libgomp']],
    'uuid' => ['build' => ['util-linux-dev'], 'runtime' => ['libuuid']],
];

exit(main($argv));

// ---------------------------------------------------------------------------

function main(array $argv): int
{
    $opts = parseArgs($argv);

    if ($opts['help']) {
        echo "Usage: php init.php [app-folder] [--image=ghcr.io/user/name] [--dry-run] [--force] [--yes]\n\n"
            ."  app-folder   Laravel app root (default: current folder)\n"
            ."  --image      image name; default is derived from git remote + folder name\n"
            ."  --db         mysql | mariadb | pgsql | sqlite; default is read from .env.example\n"
            ."  --dry-run    only show what was detected and what would be written\n"
            ."  --force      overwrite existing files (a .bak copy is kept)\n"
            ."  --yes        do not ask for confirmation (a terminal asks by default)\n";

        return 0;
    }

    $pack = __DIR__;
    $target = realpath($opts['target'] ?? getcwd());

    if ($target === false || ! is_dir($target)) {
        fail('App folder not found.');
    }
    if ($target === $pack) {
        fail('Run this from your app folder, or pass the folder: php init.php /path/to/app');
    }
    if (! is_file($target.'/artisan') || ! is_file($target.'/composer.json')) {
        fail("$target does not look like a Laravel app (no artisan / composer.json).");
    }

    $report = new Report;
    $plan = new Plan($target, $opts['dry-run'], $opts['force']);

    $facts = detect($target, $report, $opts['db']);
    $image = resolveImage($target, $opts['image'], $report);

    say();
    say('Image name   : '.$image);
    say('Target       : '.$target.($opts['dry-run'] ? '   (DRY RUN: nothing is written)' : ''));
    say();

    // In a real terminal, show what would be written and ask first.
    if (! $opts['dry-run'] && ! $opts['yes'] && function_exists('stream_isatty') && stream_isatty(STDIN)) {
        $preview = new Plan($target, true, $opts['force']);
        install($pack, $target, $facts, $image, $preview, new Report);
        say('This will write into '.$target.':');
        foreach ($preview->results as [$file, $status, $why]) {
            say(sprintf('  %-24s %s', $file, $status));
        }
        echo PHP_EOL.'Continue? [y/N] ';
        $answer = strtolower(trim((string) fgets(STDIN)));
        if ($answer !== 'y' && $answer !== 'yes') {
            say('Aborted. Nothing was written.');

            return 0;
        }
        say();
    }

    install($pack, $target, $facts, $image, $plan, $report);

    $report->print();
    summary($plan, $facts, $image, $opts['dry-run']);

    return 0;
}

function parseArgs(array $argv): array
{
    $opts = ['target' => null, 'image' => null, 'db' => null, 'dry-run' => false, 'force' => false, 'yes' => false, 'help' => false];
    $args = array_slice($argv, 1);

    for ($i = 0; $i < count($args); $i++) {
        $a = $args[$i];
        if ($a === '--dry-run') {
            $opts['dry-run'] = true;
        } elseif ($a === '--force') {
            $opts['force'] = true;
        } elseif ($a === '--yes' || $a === '-y') {
            $opts['yes'] = true;
        } elseif ($a === '--help' || $a === '-h') {
            $opts['help'] = true;
        } elseif (str_starts_with($a, '--image=')) {
            $opts['image'] = substr($a, 8);
        } elseif (str_starts_with($a, '--db=')) {
            $db = strtolower(substr($a, 5));
            if (! in_array($db, ['sqlite', 'mysql', 'mariadb', 'pgsql'], true)) {
                fail('--db must be one of: mysql, mariadb, pgsql, sqlite');
            }
            $opts['db'] = $db;
        } elseif ($a === '--image' && isset($args[$i + 1])) {
            $opts['image'] = $args[++$i];
        } elseif (! str_starts_with($a, '-') && $opts['target'] === null) {
            $opts['target'] = $a;
        } else {
            fail("Unknown option: $a (try --help)");
        }
    }

    return $opts;
}

// ---------------------------------------------------------------------------
// Detection
// ---------------------------------------------------------------------------

/**
 * @return array{
 *   extensions: array<string, list<string>>, packages: list<string>,
 *   queue: bool, queueCommand: string, queueEvidence: list<string>, queueConnection: string,
 *   scheduler: bool, schedulerEvidence: list<string>, reverb: bool,
 *   hasFrontend: bool, hasLock: bool, hasBuildScript: bool, viteVars: list<string>,
 *   env: array<string, string>, envLines: list<string>, dbConnection: string
 * }
 */
function detect(string $target, Report $report, ?string $dbOverride = null): array
{
    say('Scanning '.$target.' ...');

    $composer = readJson($target.'/composer.json') ?? [];
    $lock = readJson($target.'/composer.lock');
    if ($lock === null) {
        $report->warn('composer.lock is missing: the Docker build needs it (run composer install / composer update, then commit it).');
    }

    $packages = [];
    $extensions = [];

    $addExt = function (string $dep, string $why) use (&$extensions): void {
        if (! str_starts_with($dep, 'ext-')) {
            return;
        }
        $name = strtolower(substr($dep, 4));
        $extensions[$name][] = $why;
    };

    foreach (array_keys($composer['require'] ?? []) as $dep) {
        $addExt((string) $dep, 'composer.json');
    }
    foreach ($lock['packages'] ?? [] as $pkg) {
        $packages[] = (string) ($pkg['name'] ?? '');
        foreach (array_keys($pkg['require'] ?? []) as $dep) {
            $addExt((string) $dep, (string) ($pkg['name'] ?? '?'));
        }
    }
    foreach (array_keys($lock['platform'] ?? []) as $dep) {
        $addExt((string) $dep, 'composer.lock');
    }

    // .env.example: drivers that imply an extension or a service
    $envText = readText($target.'/.env.example');
    $envLines = $envText === null ? [] : preg_split('/\R/', $envText);
    $env = parseEnv($envText);

    $dbConnection = $dbOverride ?? strtolower($env['DB_CONNECTION'] ?? 'sqlite');
    $dbSource = $dbOverride !== null ? 'from --db' : 'from .env.example';
    if ($dbConnection === 'sqlite' && $dbOverride === null) {
        $dbSource .= ', the Laravel default: this only picks the DB settings in .env.coolify. Using MySQL/PostgreSQL in production? Re-run with --db=mysql or --db=pgsql';
    }
    if ($dbConnection === 'sqlsrv') {
        $report->warn('DB_CONNECTION=sqlsrv: SQL Server drivers are not part of this image. Add them to the Dockerfile by hand.');
    }

    $redisDrivers = array_filter(
        [$env['QUEUE_CONNECTION'] ?? '', $env['CACHE_STORE'] ?? '', $env['SESSION_DRIVER'] ?? '', $env['BROADCAST_CONNECTION'] ?? ''],
        fn ($v) => strtolower($v) === 'redis'
    );
    $usesPredis = in_array('predis/predis', $packages, true) || strtolower($env['REDIS_CLIENT'] ?? '') === 'predis';
    if ((count($redisDrivers) > 0 || in_array('laravel/horizon', $packages, true)) && ! $usesPredis) {
        $extensions['redis'][] = 'a redis driver / laravel/horizon (phpredis client)';
    }
    if (strtolower($env['CACHE_STORE'] ?? '') === 'memcached') {
        $extensions['memcached'][] = '.env.example CACHE_STORE=memcached';
    }

    if (in_array('laravel/octane', $packages, true)) {
        $report->warn('laravel/octane is installed: this pack serves the app with nginx + php-fpm, not Octane. It will still run, without Octane.');
    }

    // Behind the Coolify proxy the container only sees plain HTTP: Laravel must trust the proxy
    // (documented: "Configuring Trusted Proxies") or it builds http:// redirects and URLs.
    $bootstrap = readText($target.'/bootstrap/app.php');
    $legacyMiddleware = is_file($target.'/app/Http/Middleware/TrustProxies.php');
    if ($bootstrap !== null && ! $legacyMiddleware && ! preg_match('/trustProxies\s*\(/', stripPhpComments($bootstrap))) {
        $report->warn('bootstrap/app.php does not call trustProxies(). Behind the Coolify proxy Laravel can build http:// redirects (the login page then looks stuck until you refresh) and see the proxy as the client IP. The nginx config of this pack forwards the scheme as a safety net; also add  $middleware->trustProxies(at: \'*\');  inside ->withMiddleware(...) in bootstrap/app.php.');
    }

    // Queue / scheduler / Reverb
    $phpFiles = array_merge(walk($target.'/app', ['.php']), walk($target.'/routes', ['.php']));
    $queueEvidence = scanPhp($phpFiles, $target, [
        'ShouldQueue' => '/\bShouldQueue\b/',
        'queued broadcast event' => '/\bShouldBroadcast\b(?!Now)/',
        'Queueable job' => '/\buse\s+Queueable\b/',
        'Bus::dispatch/chain/batch' => '/\bBus::(dispatch|chain|batch|dispatchSync)\b/',
        'Job::dispatch()' => '/\b[A-Z]\w*::dispatch(If|Unless|AfterResponse)?\s*\(/',
        '->onQueue()' => '/->onQueue\s*\(/',
        '->queue()' => '/->queue\s*\(/',
    ]);

    $queueConnection = strtolower($env['QUEUE_CONNECTION'] ?? 'database');
    $hasHorizon = in_array('laravel/horizon', $packages, true);
    $queueNeeded = $hasHorizon || (count($queueEvidence) > 0 && ! in_array($queueConnection, ['sync', 'null'], true));

    $scheduleFiles = array_filter([
        $target.'/routes/console.php',
        $target.'/app/Console/Kernel.php',
        $target.'/bootstrap/app.php',
    ], 'is_file');
    $schedulerEvidence = scanPhp($scheduleFiles, $target, [
        'Schedule::' => '/\bSchedule::\s*\w+\s*\(/',
        '$schedule->' => '/\$schedule\s*->\s*\w+\s*\(/',
    ]);

    $reverbNeeded = in_array('laravel/reverb', $packages, true);
    if (! $reverbNeeded && strtolower($env['BROADCAST_CONNECTION'] ?? '') === 'reverb') {
        $report->warn('BROADCAST_CONNECTION=reverb in .env.example but laravel/reverb is not installed.');
    }

    // Frontend
    $package = readJson($target.'/package.json');
    $hasFrontend = $package !== null;
    $hasLock = is_file($target.'/package-lock.json');
    $hasBuildScript = isset($package['scripts']['build']);

    if ($hasFrontend && ! $hasBuildScript) {
        $report->warn('package.json has no "build" script: the frontend build step is left out of the Dockerfile.');
    }
    if ($hasFrontend && ! $hasLock) {
        $report->warn('package-lock.json is missing: the image will use "npm install" (slower, not reproducible). Run "npm install" once and commit the lock file.');
        if (is_file($target.'/pnpm-lock.yaml') || is_file($target.'/yarn.lock')) {
            $report->warn('pnpm/yarn lock file found, but the pack builds with npm only.');
        }
    }

    $viteVars = [];
    if ($hasFrontend) {
        $frontendFiles = array_merge(
            walk($target.'/resources', ['.js', '.jsx', '.ts', '.tsx', '.vue', '.svelte', '.html', '.php']),
            glob($target.'/vite.config.*') ?: []
        );
        foreach ($frontendFiles as $file) {
            $text = (string) file_get_contents($file);
            if (preg_match_all('/(?:import\.meta\.env|process\.env)(?:\.|\[\s*["\'])(VITE_[A-Z0-9_]+)/', $text, $m)) {
                foreach ($m[1] as $name) {
                    $viteVars[$name] = true;
                }
            }
        }
        foreach (array_keys($env) as $name) {
            if (str_starts_with($name, 'VITE_')) {
                $viteVars[$name] = true;
            }
        }
        $viteVars = array_keys($viteVars);
        // laravel/reverb itself depends on pusher-php-server, so its presence says nothing:
        // with Reverb installed the Pusher client settings are not used.
        if ($reverbNeeded) {
            $viteVars = array_values(array_filter($viteVars, fn ($v) => ! str_starts_with($v, 'VITE_PUSHER_')));
        }
        sort($viteVars);
    }

    // Safety checks
    $tracked = trim((string) @shell_exec('git -C '.escapeshellarg($target).' ls-files .env 2>&1'));
    if ($tracked === '.env') {
        $report->warn('.env is tracked by git. Remove it from git (git rm --cached .env) and rotate any secrets it contained.');
    }

    $extensions = classifyExtensions($extensions, $report);

    // Print findings
    say('');
    say('Detected');
    say('  PHP extras   : '.($extensions['install'] === [] ? 'none' : implode(', ', array_keys($extensions['install']))));
    say('  Queue worker : '.yesNo($queueNeeded).evidenceNote($queueNeeded, $queueEvidence, $queueConnection, $hasHorizon));
    say('  Scheduler    : '.yesNo($schedulerEvidence !== []).($schedulerEvidence === [] ? ' (no scheduled tasks registered)' : ' ('.implode(', ', array_slice($schedulerEvidence, 0, 3)).')'));
    say('  Reverb       : '.yesNo($reverbNeeded).($reverbNeeded ? ' (laravel/reverb is installed)' : ''));
    say('  Frontend     : '.($hasFrontend ? 'yes ('.($hasLock ? 'npm ci' : 'npm install').($hasBuildScript ? ', npm run build' : ', no build script').')' : 'no package.json'));
    say('  VITE_ vars   : '.($viteVars === [] ? 'none' : implode(', ', $viteVars)));
    say('  Database     : '.$dbConnection.' ('.$dbSource.')');

    return [
        'extensions' => $extensions['install'],
        'packages' => $packages,
        'queue' => $queueNeeded,
        'queueCommand' => $hasHorizon ? 'horizon' : 'queue:work',
        'queueEvidence' => $queueEvidence,
        'queueConnection' => $queueConnection,
        'scheduler' => $schedulerEvidence !== [],
        'schedulerEvidence' => $schedulerEvidence,
        'reverb' => $reverbNeeded,
        'hasFrontend' => $hasFrontend,
        'hasLock' => $hasLock,
        'hasBuildScript' => $hasBuildScript,
        'viteVars' => $viteVars,
        'env' => $env,
        'envLines' => $envLines,
        'dbConnection' => $dbConnection,
    ];
}

/**
 * @param  array<string, list<string>>  $found  extension => reasons
 * @return array{install: array<string, list<string>>}
 */
function classifyExtensions(array $found, Report $report): array
{
    $install = [];
    ksort($found);

    $already = [];
    foreach ($found as $name => $reasons) {
        $why = implode(', ', array_slice(array_unique($reasons), 0, 2));

        if (in_array($name, PACK_EXTENSIONS, true)) {
            $already[] = $name;

            continue;
        }
        if (in_array($name, BUNDLED_EXTENSIONS, true)) {
            continue;
        }
        if (in_array($name, DEV_ONLY_EXTENSIONS, true)) {
            continue;
        }
        if (isset(DOCKER_EXTENSIONS[$name]) || isset(PECL_EXTENSIONS[$name])) {
            $install[$name] = $reasons;
            $report->note("ext-$name is required by $why: will be installed in the image.");

            continue;
        }
        $report->warn("ext-$name is required by $why, and the pack has no recipe for it. Add it to the Dockerfile by hand (above the init.php block).");
    }

    if ($already !== []) {
        $report->note('Required and already included in the image: '.implode(', ', $already).'.');
    }

    return ['install' => $install];
}

function evidenceNote(bool $needed, array $evidence, string $connection, bool $horizon): string
{
    if ($horizon) {
        return ' (laravel/horizon is installed)';
    }
    if ($evidence === []) {
        return ' (no queued jobs found)';
    }
    $files = implode(', ', array_slice($evidence, 0, 3));
    if (! $needed) {
        return " (jobs found in $files, but QUEUE_CONNECTION=$connection runs them inline)";
    }

    return " ($files)";
}

// ---------------------------------------------------------------------------
// Installing
// ---------------------------------------------------------------------------

function install(string $pack, string $target, array $facts, string $image, Plan $plan, Report $report): void
{
    // Dockerfile (managed blocks)
    $blocks = [
        'extra-extensions' => renderExtensions($facts['extensions']),
        'frontend-deps' => renderFrontendDeps($facts),
        'vite-build' => renderViteBuild($facts),
    ];
    $dockerfile = $target.'/Dockerfile';
    $template = readText($pack.'/Dockerfile') ?? fail('Pack Dockerfile is missing.');

    if (is_file($dockerfile) && ! $plan->force) {
        $existing = normalizeNl((string) file_get_contents($dockerfile));
        [$updated, $missing] = applyBlocks($existing, $blocks);
        if ($missing !== []) {
            $plan->skip('Dockerfile', 'exists without deploy-pack markers ('.implode(', ', $missing).'); use --force to replace it (a .bak is kept)');
            $report->warn('Dockerfile was left alone, so extensions/VITE args were NOT applied to it.');
        } else {
            $plan->write('Dockerfile', $updated, 'update');
        }
    } else {
        [$rendered] = applyBlocks(normalizeNl($template), $blocks);
        $plan->write('Dockerfile', $rendered);
    }

    // docker-compose.yaml
    $compose = renderCompose(
        readText($pack.'/docker-compose.yaml') ?? fail('Pack docker-compose.yaml is missing.'),
        $image,
        ['queue' => $facts['queue'], 'scheduler' => $facts['scheduler'], 'reverb' => $facts['reverb']],
        $facts['queueCommand']
    );
    $plan->write('docker-compose.yaml', $compose);

    // Static files
    $plan->write('.dockerignore', normalizeNl(readText($pack.'/.dockerignore') ?? ''));
    foreach (glob($pack.'/docker/*') ?: [] as $file) {
        if (is_file($file)) {
            $plan->write('docker/'.basename($file), normalizeNl((string) file_get_contents($file)));
        }
    }
    foreach (['build-push.sh', 'build-push.ps1'] as $script) {
        // The scripts read the image name from docker-compose.yaml themselves.
        $plan->write($script, normalizeNl(readText($pack.'/'.$script) ?? ''));
    }

    // .env.build (merged, never loses what you already typed)
    $plan->write('.env.build', renderEnvBuild($target, $facts), 'merge');

    // .env.coolify (generated checklist for this app)
    $plan->write('.env.coolify', renderCoolifyEnv($facts));

    // .dockerignore sanity when the app already had its own
    $ignore = readText($target.'/.dockerignore');
    if ($ignore !== null && ! preg_match('/^\.env(\.\*)?\s*$/m', $ignore)) {
        $report->warn('Your .dockerignore does not exclude ".env": the file (with secrets) would be copied into the image.');
    }
}

/**
 * @return array{0: string, 1: list<string>} updated text, markers that were missing
 */
function applyBlocks(string $text, array $blocks): array
{
    $missing = [];
    foreach ($blocks as $name => $content) {
        $pattern = '/(# >>> deploy-pack:'.preg_quote($name, '/').'\n)(.*?)(# <<< deploy-pack:'.preg_quote($name, '/').')/s';
        if (! preg_match($pattern, $text)) {
            $missing[] = $name;

            continue;
        }
        $body = $content === '' ? '' : rtrim($content, "\n")."\n";
        $text = (string) preg_replace_callback($pattern, fn ($m) => $m[1].$body.$m[3], $text);
    }

    return [$text, $missing];
}

function renderExtensions(array $extensions): string
{
    if ($extensions === []) {
        return "# (none needed: your composer packages only use extensions the image already has)\n";
    }

    $out = [];
    $docker = array_filter($extensions, fn ($_, $name) => isset(DOCKER_EXTENSIONS[$name]), ARRAY_FILTER_USE_BOTH);
    if ($docker !== []) {
        $apk = [];
        foreach (array_keys($docker) as $name) {
            $apk = array_merge($apk, DOCKER_EXTENSIONS[$name]);
        }
        $apk = array_values(array_unique($apk));
        $names = implode(' ', array_keys($docker));
        $line = 'RUN ';
        if ($apk !== []) {
            $line .= 'apk add --no-cache '.implode(' ', $apk)." \\\n    && ";
        }
        $out[] = $line.'docker-php-ext-install -j"$(nproc)" '.$names;
    }

    foreach (array_keys($extensions) as $name) {
        if (! isset(PECL_EXTENSIONS[$name])) {
            continue;
        }
        $spec = PECL_EXTENSIONS[$name];
        $cmd = 'RUN ';
        if ($spec['runtime'] !== []) {
            $cmd .= 'apk add --no-cache '.implode(' ', $spec['runtime'])." \\\n    && ";
        }
        $cmd .= 'apk add --no-cache --virtual .build-deps-'.$name.' $PHPIZE_DEPS '.implode(' ', $spec['build'])." \\\n"
            ."    && pecl install $name \\\n"
            ."    && docker-php-ext-enable $name \\\n"
            ."    && apk del .build-deps-$name";
        $out[] = $cmd;
    }

    return implode("\n", $out)."\n";
}

function renderFrontendDeps(array $facts): string
{
    if (! $facts['hasFrontend']) {
        return "# (no package.json: backend-only app)\n";
    }
    if ($facts['hasLock']) {
        return "COPY package.json package-lock.json ./\nRUN npm ci --no-audit --no-fund\n";
    }

    return "COPY package.json ./\nRUN npm install --no-audit --no-fund\n";
}

function renderViteBuild(array $facts): string
{
    if (! $facts['hasFrontend'] || ! $facts['hasBuildScript']) {
        return "# (no frontend build)\n";
    }

    $out = '';
    foreach ($facts['viteVars'] as $name) {
        $out .= "ARG $name=\n";
    }
    if ($facts['viteVars'] === []) {
        return $out."RUN npm run build\n";
    }

    return $out
        .'RUN for v in '.implode(' ', $facts['viteVars'])."; do \\\n"
        ."        eval \"[ -n \\\"\\\$\$v\\\" ] || unset \$v\"; \\\n"
        ."    done; \\\n"
        ."    npm run build\n";
}

function renderCompose(string $template, string $image, array $enable, string $queueCommand): string
{
    $lines = explode("\n", str_replace(PLACEHOLDER_IMAGE, $image, normalizeNl($template)));
    $out = [];
    $i = 0;
    $n = count($lines);

    while ($i < $n) {
        if (preg_match('/^  # (queue|scheduler|reverb):$/', $lines[$i], $m) && ($enable[$m[1]] ?? false)) {
            while ($i < $n && str_starts_with($lines[$i], '  #')) {
                $out[] = rtrim((string) preg_replace('/^(\s*)# ?/', '$1', $lines[$i], 1));
                $i++;
            }

            continue;
        }
        $out[] = $lines[$i];
        $i++;
    }

    $text = implode("\n", $out);

    if ($queueCommand === 'horizon') {
        $text = (string) preg_replace('/command: \["php", "artisan", "queue:work"[^\n]*\]/', 'command: ["php", "artisan", "horizon"]', $text);
    }

    $text = (string) preg_replace_callback('/^( *)# @reverb-env[ ]*\n/m', function (array $m) use ($enable): string {
        if (! ($enable['reverb'] ?? false)) {
            return '';
        }
        $i = $m[1];

        return $i."# publish broadcasts to Reverb over the internal network\n"
            .$i."REVERB_HOST: reverb\n"
            .$i."REVERB_PORT: \"8080\"\n"
            .$i."REVERB_SCHEME: http\n";
    }, $text);

    return $text;
}

function renderEnvBuild(string $target, array $facts): string
{
    $existingText = readText($target.'/.env.build');
    $existing = parseEnv($existingText);

    $header = <<<'TXT'
# Build-time values for the frontend (read by build-push.sh / build-push.ps1).
# Vite compiles VITE_* variables into the JavaScript, so they must exist when the
# image is BUILT: setting them in Coolify later does nothing. They end up in the
# public JS bundle: never put secrets here. Empty value = the frontend default.
# .dockerignore excludes .env.* so this file never enters the image.

TXT;

    $body = $existingText !== null ? rtrim(normalizeNl($existingText), "\n")."\n" : $header;
    $added = [];

    // .env.example holds LOCAL values (localhost, log driver); production needs different ones.
    $production = $facts['reverb'] ? [
        'VITE_BROADCAST_CONNECTION' => ['reverb', ''],
        'VITE_REVERB_HOST' => ['', 'CHANGE: public domain of the reverb service, e.g. ws.example.com'],
        'VITE_REVERB_PORT' => ['443', ''],
        'VITE_REVERB_SCHEME' => ['https', ''],
        'VITE_REVERB_APP_KEY' => ['', 'CHANGE: must equal REVERB_APP_KEY set in Coolify'],
    ] : [];

    foreach ($facts['viteVars'] as $name) {
        if (array_key_exists($name, $existing)) {
            continue;
        }
        if (isset($production[$name])) {
            [$value, $comment] = $production[$name];
        } else {
            $value = $facts['env'][$name] ?? '';
            $comment = $value === '' ? 'fill in if the frontend needs it (empty = frontend default)' : '';
        }
        $added[] = ($comment !== '' ? "# $comment\n" : '').$name.'='.($value === '' ? '' : quoteEnv($value));
    }

    if ($added !== []) {
        $body .= ($existingText !== null ? "\n" : '').implode("\n", $added)."\n";
    }

    return $body;
}

function renderCoolifyEnv(array $facts): string
{
    $env = $facts['env'];
    $sqlite = $facts['dbConnection'] === 'sqlite';
    $out = [
        '# Environment for Coolify -> your app -> Environment Variables (Developer view: paste all).',
        '# Generated by init.php from .env.example for THIS app. Fill every CHANGE_ME / empty secret.',
        '# Generate APP_KEY with:  php artisan key:generate --show',
        '# Never commit real secrets. VITE_* values are not here: they live in .env.build.',
        '# Values are written without quotes on purpose: they reach the container exactly as typed.',
        '',
    ];

    $handled = [];
    foreach ($facts['envLines'] as $line) {
        if (! preg_match('/^\s*(?:export\s+)?([A-Za-z_][A-Za-z0-9_.]*)\s*=/', $line, $m)) {
            // keep comments, but drop commented DB host lines when SQLite is used
            if ($sqlite && preg_match('/^\s*#\s*DB_(HOST|PORT|DATABASE|USERNAME|PASSWORD)\b/', $line)) {
                continue;
            }
            if (preg_match('/^\s*#\s*DB_(HOST|PORT|DATABASE|USERNAME|PASSWORD)=/', $line) && ! $sqlite) {
                continue;
            }
            $out[] = rtrim($line);

            continue;
        }

        $key = $m[1];
        $value = $env[$key] ?? '';
        $handled[$key] = true;

        if (str_starts_with($key, 'VITE_')) {
            continue;
        }
        if ($facts['reverb'] && in_array($key, ['REVERB_HOST', 'REVERB_PORT', 'REVERB_SCHEME'], true)) {
            continue; // compose points the containers at the internal reverb service
        }

        switch ($key) {
            case 'APP_ENV':
                $value = 'production';
                break;
            case 'DB_CONNECTION':
                $value = $facts['dbConnection'];
                break;
            case 'APP_DEBUG':
                $value = 'false';
                break;
            case 'APP_URL':
                $value = 'https://CHANGE_ME';
                break;
            case 'APP_KEY':
                $out[] = '# required: php artisan key:generate --show   (keep it forever once in use)';
                break;
            case 'LOG_LEVEL':
                $value = 'info';
                break;
            case 'DB_DATABASE':
                if ($sqlite) {
                    $out[] = '# SQLite lives in the storage volume so it survives redeploys';
                    $value = '/app/storage/database/database.sqlite';
                } else {
                    $value = 'CHANGE_ME';
                }
                break;
            case 'DB_HOST':
            case 'DB_USERNAME':
            case 'DB_PASSWORD':
                if (! $sqlite) {
                    $value = 'CHANGE_ME';
                }
                break;
            case 'QUEUE_CONNECTION':
                if (! $facts['queue'] && ! in_array(strtolower($value), ['sync', 'null'], true)) {
                    $out[] = '# no queued jobs detected: "'.$value.'" would need a worker service, so run jobs inline';
                    $value = 'sync';
                }
                break;
            case 'BROADCAST_CONNECTION':
                if ($facts['reverb']) {
                    $value = 'reverb';
                }
                break;
        }

        // No quotes: Coolify hands the text after "=" to the container as it is.
        $out[] = $key.'='.$value;
    }

    if ($sqlite && ! isset($handled['DB_DATABASE'])) {
        $out[] = '';
        $out[] = '# SQLite lives in the storage volume so it survives redeploys';
        $out[] = 'DB_DATABASE=/app/storage/database/database.sqlite';
    }

    if (! $sqlite) {
        $out[] = '';
        $out[] = '# Database: use the hostname Coolify shows for your database resource';
        foreach (['DB_HOST' => 'CHANGE_ME', 'DB_PORT' => $facts['dbConnection'] === 'pgsql' ? '5432' : '3306', 'DB_USERNAME' => 'CHANGE_ME', 'DB_PASSWORD' => 'CHANGE_ME'] as $k => $v) {
            if (! isset($handled[$k])) {
                $out[] = "$k=$v";
            }
        }
        if (! isset($handled['DB_DATABASE'])) {
            $out[] = 'DB_DATABASE=CHANGE_ME';
        }
    }

    $out[] = '';
    $out[] = '# ---- Added by the deploy pack ----';
    $out[] = '# true = run "php artisan migrate --force" on every start (a failed migration stops the container)';
    $out[] = 'AUTO_MIGRATE=true';
    $out[] = '# Pin / roll back to a build: APP_IMAGE_TAG=sha-<commit> (remove to follow :latest)';
    $out[] = '# APP_IMAGE_TAG=latest';

    if ($facts['reverb']) {
        foreach (['REVERB_APP_ID', 'REVERB_APP_KEY', 'REVERB_APP_SECRET'] as $k) {
            if (! isset($handled[$k])) {
                $out[] = $k.'=';
            }
        }
    }

    return preg_replace("/\n{3,}/", "\n\n", implode("\n", $out))."\n";
}

// ---------------------------------------------------------------------------
// Image name
// ---------------------------------------------------------------------------

function resolveImage(string $target, ?string $given, Report $report): string
{
    if ($given !== null && $given !== '') {
        $image = $given;
    } else {
        $remote = trim((string) @shell_exec('git -C '.escapeshellarg($target).' remote get-url origin 2>&1'));
        $owner = preg_match('#github\.com[:/]+([^/]+)/#i', $remote, $m) ? $m[1] : null;
        $name = basename($target);
        if ($owner === null) {
            $report->warn('Could not read the GitHub owner from git remote: image name left as a placeholder. Re-run with --image=ghcr.io/<user>/<name>.');

            return PLACEHOLDER_IMAGE;
        }
        $image = 'ghcr.io/'.$owner.'/'.$name;
    }

    $lower = strtolower((string) preg_replace('/[^A-Za-z0-9._\/:-]+/', '-', $image));
    if (! str_starts_with($lower, 'ghcr.io/') && ! str_contains(explode('/', $lower)[0], '.')) {
        $lower = 'ghcr.io/'.$lower;
    }
    if ($given !== null && $given !== '' && $lower !== $image) {
        $report->note("Image name normalised to lowercase: $lower");
    }

    return $lower;
}

// ---------------------------------------------------------------------------
// File plan / report
// ---------------------------------------------------------------------------

final class Plan
{
    /** @var list<array{string, string, string}> */
    public array $results = [];

    public function __construct(public string $target, public bool $dryRun, public bool $force) {}

    public function write(string $relative, string $content, string $mode = 'create'): void
    {
        $path = $this->target.'/'.$relative;
        $exists = is_file($path);

        if ($exists) {
            $current = normalizeNl((string) file_get_contents($path));
            if ($current === $content) {
                $this->results[] = [$relative, 'unchanged', ''];

                return;
            }
            if ($mode === 'create' && ! $this->force) {
                $this->results[] = [$relative, 'skipped', 'already exists (use --force to replace; a .bak is kept)'];

                return;
            }
            if ($this->force && $mode === 'create' && ! $this->dryRun) {
                copy($path, $path.'.bak');
            }
        }

        $verb = ! $exists ? 'created' : ($mode === 'create' ? 'replaced' : ($mode === 'merge' ? 'merged' : 'updated'));
        if (! $this->dryRun) {
            $dir = dirname($path);
            if (! is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            file_put_contents($path, $content);
            if (str_ends_with($relative, '.sh')) {
                @chmod($path, 0755);
            }
        }
        $this->results[] = [$relative, $this->dryRun ? 'would be '.$verb : $verb, ''];
    }

    public function skip(string $relative, string $why): void
    {
        $this->results[] = [$relative, 'skipped', $why];
    }
}

final class Report
{
    /** @var list<string> */
    private array $notes = [];

    /** @var list<string> */
    private array $warnings = [];

    public function note(string $m): void
    {
        $this->notes[] = $m;
    }

    public function warn(string $m): void
    {
        $this->warnings[] = $m;
    }

    public function print(): void
    {
        if ($this->notes !== []) {
            say('Notes');
            foreach ($this->notes as $n) {
                say('  - '.$n);
            }
            say();
        }
        if ($this->warnings !== []) {
            say('WARNINGS (read these)');
            foreach ($this->warnings as $w) {
                say('  [!] '.$w);
            }
            say();
        }
    }
}

function summary(Plan $plan, array $facts, string $image, bool $dryRun): void
{
    say('Files');
    foreach ($plan->results as [$file, $status, $why]) {
        say(sprintf('  %-24s %s%s', $file, $status, $why !== '' ? " - $why" : ''));
    }
    say();

    if ($dryRun) {
        say('Dry run: nothing was written. Run again without --dry-run to apply.');

        return;
    }

    say('Next steps');
    say('  1. Open .env.build and fill the empty VITE_ values if the frontend needs them.');
    say('  2. Open .env.coolify, fill the CHANGE_ME / empty values, and paste it into Coolify.');
    say('  3. Test:  .\\build-push.ps1 -NoPush     (or: PUSH=0 ./build-push.sh)');
    say('  4. docker login ghcr.io, then push:  .\\build-push.ps1');
    say('  5. In Coolify: new Docker Compose resource, paste docker-compose.yaml, set the domain on service "app" (port 8000)'.($facts['reverb'] ? ' and on "reverb" (port 8080)' : '').'.');
    if (str_contains($image, 'your-github-user')) {
        say('  !  The image name is still a placeholder: re-run with --image=ghcr.io/<user>/<name>.');
    }
}

// ---------------------------------------------------------------------------
// Small helpers
// ---------------------------------------------------------------------------

function say(string $line = ''): void
{
    echo $line, PHP_EOL;
}

function fail(string $message): never
{
    fwrite(STDERR, "Error: $message".PHP_EOL);
    exit(1);
}

function yesNo(bool $v): string
{
    return $v ? 'yes' : 'no';
}

function normalizeNl(string $s): string
{
    return str_replace("\r\n", "\n", $s);
}

function readText(string $path): ?string
{
    return is_file($path) ? ltrim((string) file_get_contents($path), "\xEF\xBB\xBF") : null;
}

function readJson(string $path): ?array
{
    $text = readText($path);
    if ($text === null) {
        return null;
    }
    $data = json_decode($text, true);

    return is_array($data) ? $data : null;
}

/**
 * @param  list<string>  $extensions
 * @return list<string>
 */
function walk(string $dir, array $extensions): array
{
    if (! is_dir($dir)) {
        return [];
    }
    $skip = ['vendor', 'node_modules', '.git', 'storage', 'build', 'dist'];
    $iterator = new RecursiveIteratorIterator(new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        fn (SplFileInfo $f) => ! ($f->isDir() && in_array($f->getFilename(), $skip, true))
    ));

    $files = [];
    foreach ($iterator as $file) {
        if (! $file->isFile()) {
            continue;
        }
        foreach ($extensions as $ext) {
            if (str_ends_with($file->getFilename(), $ext)) {
                $files[] = $file->getPathname();
                break;
            }
        }
    }

    return $files;
}

/**
 * Search PHP files (comments removed) for patterns.
 *
 * @param  list<string>  $files
 * @param  array<string, string>  $patterns  label => regex
 * @return list<string> "relative/path (label)"
 */
function scanPhp(array $files, string $root, array $patterns): array
{
    $hits = [];
    foreach ($files as $file) {
        $code = stripPhpComments((string) file_get_contents($file));
        foreach ($patterns as $label => $regex) {
            if (preg_match($regex, $code)) {
                $path = str_replace('\\', '/', $file);
                $base = rtrim(str_replace('\\', '/', $root), '/').'/';
                $relative = str_starts_with($path, $base) ? substr($path, strlen($base)) : $path;
                $hits[] = $relative.' ['.$label.']';
                break;
            }
        }
    }

    return $hits;
}

function stripPhpComments(string $code): string
{
    $out = '';
    foreach (token_get_all($code) as $token) {
        if (is_array($token)) {
            if ($token[0] === T_COMMENT || $token[0] === T_DOC_COMMENT) {
                continue;
            }
            $out .= $token[1];
        } else {
            $out .= $token;
        }
    }

    return $out;
}

/**
 * Parse a dotenv file into key => value, expanding ${OTHER} references.
 *
 * @return array<string, string>
 */
function parseEnv(?string $text): array
{
    $vars = [];
    if ($text === null) {
        return $vars;
    }

    foreach (preg_split('/\R/', $text) ?: [] as $line) {
        if (! preg_match('/^\s*(?:export\s+)?([A-Za-z_][A-Za-z0-9_.]*)\s*=\s*(.*)$/', $line, $m)) {
            continue;
        }
        $value = trim($m[2]);
        if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
            $quote = $value[0];
            $end = strpos($value, $quote, 1);
            $value = $end === false ? substr($value, 1) : substr($value, 1, $end - 1);
        } else {
            $value = trim((string) preg_replace('/\s+#.*$/', '', $value));
        }
        $vars[$m[1]] = $value;
    }

    foreach ($vars as $key => $value) {
        $vars[$key] = (string) preg_replace_callback(
            '/\$\{([A-Za-z_][A-Za-z0-9_]*)\}/',
            fn ($r) => $vars[$r[1]] ?? '',
            $value
        );
    }

    return $vars;
}

function quoteEnv(string $value): string
{
    return preg_match('/[\s#"\'$]/', $value) ? '"'.str_replace('"', '\\"', $value).'"' : $value;
}
