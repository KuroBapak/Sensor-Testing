<?php

namespace App\Jobs;

use App\Support\ImageVariants;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Creates the WebP copies of one image; dispatched after the response so a page view never waits for it.
 */
class GenerateImageVariants
{
    use Dispatchable;

    public function __construct(public readonly string $path) {}

    public function handle(): void
    {
        ImageVariants::generate($this->path);
    }
}
