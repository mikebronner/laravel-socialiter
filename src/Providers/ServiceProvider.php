<?php

declare(strict_types=1);

namespace GeneaLabs\LaravelSocialiter\Providers;

use GeneaLabs\LaravelSocialiter\Socialiter;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    public function boot(): void
    {
        if (Socialiter::$runsMigrations) {
            $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        }

        $this->publishes([
            __DIR__ . '/../../database/migrations/' => database_path('migrations'),
        ], 'socialiter-migrations');
    }

    public function register(): void
    {
        $this->registerFacade();
    }

    protected function registerFacade(): void
    {
        $this->app
            ->bind('socialiter', function (): Socialiter {
                return new Socialiter;
            });
    }
}
