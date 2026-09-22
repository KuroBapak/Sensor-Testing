<?php

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        $this->configureDefaults();
        $this->configureGates();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    protected function configureGates(): void
    {
        Gate::define('export-reports', fn (User $user) => $user->hasPermission('reports.export'));
        Gate::define('export-main-tank-transactions', fn (User $user) => $user->hasPermission('reports.export') && $user->hasPermission('main_tank.view'));
        Gate::define('export-main-tank-daily-reconciliation', fn (User $user) => $user->hasPermission('reports.export') && $user->hasPermission('main_tank.view'));
        Gate::define('export-browser-tank-refuels', fn (User $user) => $user->hasPermission('reports.export') && $user->hasPermission('mobile_tanks.view'));
        Gate::define('export-browser-tank-daily-consumption', fn (User $user) => $user->hasPermission('reports.export') && $user->hasPermission('mobile_tanks.view'));
        Gate::define('export-fuel-tanker-unloads', fn (User $user) => $user->hasPermission('reports.export') && $user->hasPermission('mobile_tanks.view'));
        Gate::define('export-alarm-log', fn (User $user) => $user->hasPermission('reports.export') && $user->hasPermission('alarms.view'));
        // Raw readings: main_tank.view atau mobile_tanks.view
        Gate::define('export-tank-level-readings', fn (User $user) => $user->hasPermission('reports.export') && ($user->hasPermission('main_tank.view') || $user->hasPermission('mobile_tanks.view')));
    }
}
