<?php

namespace App\Providers\Filament;

use App\Models\GlobalSetting;
use App\Support\MaterialIcons;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName(fn (): string => GlobalSetting::query()->value('company_name') ?: config('app.name'))
            ->favicon(fn (): ?string => GlobalSetting::query()->first()?->favicon_url)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->navigationGroups([
                'Landing Page',
                'About Page',
                'Works Page',
                'License & Certification',
                'Portfolio Page',
                'Global Data Settings',
                'Inquiries',
            ])
            ->renderHook(PanelsRenderHook::HEAD_END, fn (): HtmlString => new HtmlString(
                '<link rel="stylesheet" href="'.e(MaterialIcons::fontUrl()).'">'
                .'<style>.material-symbols-outlined{font-family:"Material Symbols Outlined";font-weight:400;font-style:normal;font-size:1.25rem;line-height:1;display:inline-block;white-space:nowrap;-webkit-font-feature-settings:"liga";font-feature-settings:"liga";-webkit-font-smoothing:antialiased}.material-symbols-outlined::before{content:attr(data-icon)}</style>'
            ))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
