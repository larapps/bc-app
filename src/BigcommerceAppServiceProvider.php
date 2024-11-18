<?php

namespace Larapps\BigcommerceApp;

use Illuminate\Support\ServiceProvider;

class BigcommerceAppServiceProvider extends ServiceProvider
{
    public function boot()
    {

        // $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadMigrationsFrom(__DIR__ .'/database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('bigcommerce-app.php'),
            ], 'config');

        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'bigcommerce-app');

        // Register the main class to use with the facade
        $this->app->singleton('bigcommerce-app', function () {
            return new BigcommerceApp;
        });
    }
}
