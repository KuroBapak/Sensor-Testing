<?php

namespace App\Providers;

use App\Models\Service;
use App\Support\StructuredData;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // The services are listed in the footer, the contact form and the structured data of every page.
        $this->app->scoped('site.services', fn (): Collection => Service::query()->orderBy('sort_order')->get());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The contact form lists the services on every page; pages that already loaded them are not queried again.
        View::composer('layouts.contact-widget', function (ViewContract $view): void {
            $view->with('contactServices', $view->getData()['services'] ?? app('site.services'));
        });

        View::composer('layouts.footer', function (ViewContract $view): void {
            $view->with('footerServices', $view->getData()['services'] ?? app('site.services'));
        });

        View::composer('layouts.seo-head', function (ViewContract $view): void {
            $view->with('structuredData', StructuredData::json(
                $view->getData()['globalSettings'] ?? null,
                request()->route()?->getName(),
                $view->getData()['service'] ?? null,
                $view->getData()['seoTitle'] ?? null,
                $view->getData()['seoDescription'] ?? null,
                $view->getData()['services'] ?? app('site.services'),
            ));
        });
    }
}
