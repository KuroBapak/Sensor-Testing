<?php

namespace App\Console\Commands;

use App\Support\ImageVariants;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateImageVariantsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:variants {--force : Rewrite copies that already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the smaller WebP copies of every image on the public disk, so pages never wait for them';

    /**
     * Create the missing copies for every supported image; safe to run again at any time.
     */
    public function handle(): int
    {
        $images = collect(Storage::disk('public')->allFiles())
            ->filter(fn (string $path): bool => ImageVariants::supports($path))
            ->values();

        $created = 0;

        foreach ($images as $path) {
            $made = ImageVariants::generate($path, (bool) $this->option('force'));
            $created += $made;

            if ($made > 0) {
                $this->line("{$path}: {$made} copy(ies)");
            }
        }

        $this->info("Checked {$images->count()} image(s), wrote {$created} copy(ies).");

        return self::SUCCESS;
    }
}
