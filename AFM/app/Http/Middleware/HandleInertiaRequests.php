<?php

namespace App\Http\Middleware;

use App\Services\SettingsService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    public function __construct(private readonly SettingsService $settings) {}

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            // What the UI may show; the server enforces the same rules with policies.
            'can' => [
                'manageAdmins' => (bool) $request->user()?->isSuperAdmin(),
            ],
            'site' => fn () => $request->user() ? $this->site() : null,
            'branding' => fn () => $this->branding(),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * The name and logo shown in the sidebar and on the login page: the site name
     * and uploaded logo from global settings, or the app name and no logo.
     *
     * @return array{name: string, logoUrl: string|null}
     */
    private function branding(): array
    {
        $logo = (string) $this->settings->get('logo_path');

        return [
            'name' => $this->settings->get('site_name') ?: (string) config('app.name'),
            'logoUrl' => $logo === '' ? null : route('site-logo', ['v' => basename($logo)], false),
        ];
    }

    /**
     * Settings that every screen formats with: time zone, site name and the reset period.
     *
     * @return array<string, mixed>
     */
    private function site(): array
    {
        $period = $this->settings->resetPeriod();

        return [
            'name' => $this->settings->get('site_name') ?: null,
            'timezone' => $this->settings->timezone(),
            'resetPeriod' => $period->value,
            'resetUnit' => $period->unit(),
        ];
    }
}
