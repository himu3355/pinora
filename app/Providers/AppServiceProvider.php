<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\CartService::class);
        $this->app->singleton(\App\Services\PricingService::class);
        $this->app->singleton(\App\Services\GstService::class);
        $this->app->singleton(\App\Services\OrderService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Grant super_admin all permissions without explicit assignment.
        // This must be registered BEFORE any policy checks.
        Gate::before(function (\App\Models\User $user, string $ability) {
            if ($user->hasRole('super_admin')) {
                return true;
            }
        });

        // Share category and subcategory menu data to header navbar
        \Illuminate\Support\Facades\View::composer(
            'layouts.partials.navbar',
            \App\Http\View\Composers\CategoryMenuComposer::class
        );
    }
}
