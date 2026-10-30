<?php

namespace App\Console\Commands;

use Database\Seeders\SeedMedia;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RefreshSeedMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:refresh-seed {--dry-run : List what would change without writing any file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Replace the seeded images on the public disk with the current files from database/seeders/images, without touching the database';

    /**
     * Overwrite each already-stored seed image whose source photo has the same file type.
     *
     * Existing CMS records keep pointing at the same paths, so no CMS text changes. A stored file whose
     * source now has another type (e.g. a PNG photo converted to JPG) is listed as skipped, because its
     * path would have to change in the database.
     */
    public function handle(): int
    {
        $disk = Storage::disk('public');
        $dryRun = (bool) $this->option('dry-run');
        $replaced = 0;
        $savedBytes = 0;
        $skipped = [];

        foreach ($disk->allFiles('seed') as $path) {
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                continue;
            }

            $name = substr($path, strlen('seed/'), -strlen($extension) - 1);
            $source = SeedMedia::sourceFor($name);

            if ($source === null) {
                continue;
            }

            if (! $this->sameType(strtolower(pathinfo($source, PATHINFO_EXTENSION)), $extension)) {
                $skipped[] = $path;

                continue;
            }

            $contents = (string) file_get_contents($source);

            if ($contents === $disk->get($path)) {
                continue;
            }

            $savedBytes += $disk->size($path) - strlen($contents);
            $replaced++;
            $this->line(($dryRun ? 'would replace ' : 'replaced ').$path);

            if (! $dryRun) {
                $disk->put($path, $contents);
            }
        }

        $this->info(sprintf('%s %d file(s), %.0f KB smaller in total.', $dryRun ? 'Would replace' : 'Replaced', $replaced, $savedBytes / 1024));

        foreach ($skipped as $path) {
            $this->warn("skipped {$path}: its source is now a different file type, so it must be re-uploaded or re-pointed in the CMS");
        }

        return self::SUCCESS;
    }

    private function sameType(string $a, string $b): bool
    {
        return str_replace('jpeg', 'jpg', $a) === str_replace('jpeg', 'jpg', $b);
    }
}
