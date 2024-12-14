<?php

namespace App\Providers;

use App\Policies\PlanPolicy;
use App\Policies\RolePolicy;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravelcm\Subscriptions\Models\Plan;
use Spatie\Permission\Models\Role;

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
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Plan::class, PlanPolicy::class);

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['ar', 'en', 'fr']); // also accepts a closure
        });
    }
}
