# Build the image on THIS machine, push it to GHCR, optionally tell Coolify to redeploy.
# Windows PowerShell version of build-push.sh (same behaviour).
#
# Usage (from the app root, next to the Dockerfile):
#   .\build-push.ps1
#
# Settings are parameters (or the same environment variables as build-push.sh):
#   -Image       image name, lowercase            default: read from docker-compose.yaml
#   -Context     build context folder             default: .
#   -Dockerfile  path to the Dockerfile           default: <Context>\Dockerfile
#   -NoPush      build only, do not push/deploy
#   -NoCache     add --no-cache (only to debug stale layers; much slower)
#   $env:COOLIFY_WEBHOOK + $env:COOLIFY_TOKEN  if both are set, redeploy after the push
#
# Example, second image (AI server) from a subfolder:
#   .\build-push.ps1 -Context AIServer -Image ghcr.io/you/your-app-ai
#
# One-time before the first push:
#   $env:GH_TOKEN | docker login ghcr.io -u <github-user> --password-stdin
#   (classic token with write:packages)

param(
    [string]$Image = $env:IMAGE,
    [string]$Context = $(if ($env:CONTEXT) { $env:CONTEXT } else { '.' }),
    [string]$Dockerfile = '',
    [string]$BuildEnvFile = '',
    [switch]$NoPush,
    [switch]$NoCache
)

# 'Continue' on purpose: Windows PowerShell 5.1 treats any stderr line from a
# native command (docker prints build progress there, git prints "fatal: ...")
# as a terminating error under 'Stop'. Failures are caught with $LASTEXITCODE.
$ErrorActionPreference = 'Continue'

if (-not $Dockerfile) { $Dockerfile = Join-Path $Context 'Dockerfile' }
if (-not $BuildEnvFile) { $BuildEnvFile = Join-Path $Context '.env.build' }

# --- image name: ONE place only, the first active `image:` line of docker-compose.yaml
# (init.php fills it in). -Image / $env:IMAGE override it, e.g. for a second image.
if (-not $Image) {
    foreach ($name in 'docker-compose.yaml', 'docker-compose.yml') {
        $compose = Join-Path $Context $name
        if (Test-Path $compose) {
            $line = Select-String -Path $compose -Pattern '^\s*image:\s*(\S+)' | Select-Object -First 1
            if ($line) {
                $Image = $line.Matches[0].Groups[1].Value
                $Image = $Image -replace '["'']', ''
                $Image = $Image -replace ':\$\{.*$', ''
                $Image = $Image -replace '(/[^/:]+):[^/]+$', '$1'
            }
            break
        }
    }
}
if (-not $Image -or $Image -like '*your-github-user*') {
    throw "Image name not set. Edit the image: line in $(Join-Path $Context 'docker-compose.yaml') (or run init.php), or pass -Image ghcr.io/<user>/<name>."
}

function Invoke-Docker {
    & docker @args
    if ($LASTEXITCODE -ne 0) { throw "docker $($args[0]) failed (exit $LASTEXITCODE)" }
}

# --- immutable tag: sha-<commit>, plus -dirty when files are uncommitted ----
$sha = (& git -C $Context rev-parse --short HEAD 2>$null)
if ($LASTEXITCODE -eq 0 -and $sha) {
    $tag = "sha-$sha"
    $dirty = (& git -C $Context status --porcelain -- . 2>$null)
    if ($dirty) { $tag = "$tag-dirty" }
} else {
    $tag = 'local-' + (Get-Date -Format 'yyyyMMddHHmmss')
}

# --- VITE_* build args (compiled into the JS bundle) ------------------------
$buildArgs = @()
if (Test-Path $BuildEnvFile) {
    foreach ($line in Get-Content $BuildEnvFile) {
        $line = $line.TrimEnd("`r")
        if ($line -eq '' -or $line.StartsWith('#')) { continue }
        if ($line -match '^(VITE_[A-Za-z0-9_]*)=(.*)$') {
            $value = $Matches[2]
            if ($value.Length -ge 2 -and $value.StartsWith('"') -and $value.EndsWith('"')) {
                $value = $value.Substring(1, $value.Length - 2)
            }
            $buildArgs += @('--build-arg', "$($Matches[1])=$value")
        } else {
            Write-Host "Ignoring non-VITE line in ${BuildEnvFile}: $($line.Split('=')[0])"
        }
    }
}

$cacheArgs = @()
if ($NoCache) { $cacheArgs = @('--no-cache') }

Write-Host "==> Building ${Image}:$tag (and :latest)"
Invoke-Docker build --platform linux/amd64 @cacheArgs @buildArgs `
    -t "${Image}:$tag" -t "${Image}:latest" `
    -f $Dockerfile $Context

if ($NoPush) {
    Write-Host '==> -NoPush: built only. Nothing pushed.'
    return
}

# sha tag first, so :latest never points at an image that was not pushed
Write-Host '==> Pushing'
Invoke-Docker push "${Image}:$tag"
Invoke-Docker push "${Image}:latest"

if ($env:COOLIFY_WEBHOOK -and $env:COOLIFY_TOKEN) {
    Write-Host '==> Triggering Coolify deploy'
    Invoke-RestMethod -Method Post -Uri $env:COOLIFY_WEBHOOK -ErrorAction Stop `
        -Headers @{ Authorization = "Bearer $($env:COOLIFY_TOKEN)" } | Out-Null
} else {
    Write-Host '==> Pushed. Now click Deploy / Redeploy in Coolify.'
}

Write-Host "==> Done: ${Image}:$tag"
Write-Host "    Roll back later with APP_IMAGE_TAG=$tag in the Coolify environment."
