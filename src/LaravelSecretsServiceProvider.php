<?php

namespace Alavida\LaravelSecrets;

use Illuminate\Support\ServiceProvider;

class LaravelSecretsServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'alavida');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'alavida');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            // Publishing the configuration file.
            $this->publishes([
                __DIR__.'/../config/laravelsecrets.php' => config_path('laravelsecrets.php'),
            ], 'laravelsecrets.config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => base_path('resources/views/vendor/alavida'),
            ], 'laravelsecrets.views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/alavida'),
            ], 'laravelsecrets.views');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/alavida'),
            ], 'laravelsecrets.views');*/

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravelsecrets.php', 'laravelsecrets');

        // Register the service the package provides.
        $this->app->singleton('laravelsecrets', function ($app) {
            return new LaravelSecrets;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['laravelsecrets'];
    }
}