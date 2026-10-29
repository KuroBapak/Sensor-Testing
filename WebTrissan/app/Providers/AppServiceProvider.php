<?php

namespace App\Providers;

use App\Support\StructuredData;
use Illuminate\Contracts\View\View as ViewContract;
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
        View::composer('layouts.seo-head', function (ViewContract $view): void {
            $view->with('structuredData', StructuredData::json(
                $view->getData()['globalSettings'] ?? null,
                request()->route()?->getName(),
            ));
        });
    }
}
