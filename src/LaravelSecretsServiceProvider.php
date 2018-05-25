<?php

namespace Alavida\LaravelSecrets;

use Alavida\LaravelSecrets\Console\Commands\UpdateSecrets;
use Aws\Laravel\AwsFacade;
use Aws\Laravel\AwsServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
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

            // Register command
            $this->commands([
                UpdateSecrets::class
            ]);

            // Schedule command to run everyFiveMinutes
            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);
                $schedule->command('secrets:update')->everyFiveMinutes();
            });

            // Re-write .env from cache pulled from AWS Secrets Manager
            $this->overwriteEnvFile();

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

        // Register Server Provider of Subpackage: "aws/aws-sdk-php-laravel": "~3.0"
        $this->app->register(AwsServiceProvider::class);

        $this->app->alias('AWS', AwsFacade::class);

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

    private function overwriteEnvFile(): void
    {
        $env_variables = cache('secrets');
        if (isset($env_variables)) {
            file_put_contents(base_path('.env'), $env_variables);
        }
    }
}