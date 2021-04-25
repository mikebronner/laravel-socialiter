<?php

namespace GeneaLabs\LaravelSocialiter\Providers;

use GeneaLabs\LaravelSocialiter\Socialiter;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    protected $defer = false;

    public function boot()
    {
        if (Socialiter::$runsMigrations) {
            $this->loadMigrationsFrom(__DIR__ . "/../../database/migrations");
        }

        $this->publishes(
            [
                __DIR__ . '/../../database/migrations/' => database_path('migrations')
            ],
            'migrations'
        );
    }

    public function register()
    {
        // $this->registerConfiguration();
        $this->registerFacade();
    }

    protected function registerFacade()
    {
        $this->app->bind(
            'socialiter',
            function () {
                return new Socialiter;
            }
        );
    }

    // protected function registerConfiguration()
    // {
    //     $this->mergeConfigFrom(
    //         __DIR__ . '/../../config/services.php',
    //         'services'
    //     );
    // }
}
