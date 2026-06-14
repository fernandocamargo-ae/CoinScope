<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Contracts\SimulationRepositoryInterface::class,
            \App\Repositories\Eloquent\SimulationRepository::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            fn($e) => app(\App\Services\AuditService::class)->log('LOGIN', 'Inició sesión', $e->user->id)
        );

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Registered::class,
            fn($e) => app(\App\Services\AuditService::class)->log('REGISTER', 'Se registró en la plataforma', $e->user->id)
        );

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Logout::class,
            fn($e) => $e->user
                ? app(\App\Services\AuditService::class)->log('LOGOUT', 'Cerró sesión', $e->user->id)
                : null
        );
    }
}
