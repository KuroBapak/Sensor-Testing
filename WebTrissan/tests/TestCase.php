<?php

namespace Tests;

use App\Jobs\GenerateImageVariants;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Bus;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The compiled assets are not committed, so pages must render without a Vite build.
        $this->withoutVite();

        // Page views would otherwise write WebP copies of the seeded images into the real public disk.
        Bus::fake([GenerateImageVariants::class]);
    }

    /**
     * Refuse to run against a non-test database.
     *
     * A stale `config:cache` makes the application ignore phpunit.xml and RefreshDatabase would then
     * wipe the development database, so the connection is checked before any trait touches it.
     */
    protected function refreshApplication(): void
    {
        parent::refreshApplication();

        $connection = config('database.default');
        $database = (string) config("database.connections.{$connection}.database");

        if ($database !== ':memory:' && ! str_ends_with($database, '_test')) {
            throw new RuntimeException(
                "Tests must use a database ending in '_test' (got '{$database}'). Run `php artisan config:clear` and try again."
            );
        }
    }
}
