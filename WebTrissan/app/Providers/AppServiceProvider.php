<?php

namespace App\Providers;

use App\Models\Service;
use App\Models\ServiceCluster;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.navbar', function ($view): void {
            $view->with([
                'navServiceCount' => Service::count(),
                'navClusterCount' => ServiceCluster::count(),
            ]);
        });
    }
}
