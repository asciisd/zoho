<?php

namespace Asciisd\Zoho\Providers;

use Asciisd\Zoho\Console\Commands\InstallCommand;
use Asciisd\Zoho\Console\Commands\SetupCommand;
use Illuminate\Support\ServiceProvider;

class ZohoServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishConfig();
        $this->publishOauth();
        $this->publishCommands();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/zoho.php', 'zoho'
        );
        $this->registerSingleton();
    }

    private function publishConfig()
    {
        $this->publishes([
            __DIR__ . '/../../config/zoho.php' => config_path('zoho.php'),
        ], 'zoho-config');
    }

    private function publishOauth()
    {
        $this->publishes([
            __DIR__ . '/Storage/oauth' => storage_path('app/zoho/oauth'),
        ], 'zoho-oauth');
    }

    private function publishAssets()
    {
        // $this->publishes([
        //     __DIR__.'/../../public' => public_path('vendor/zoho'),
        // ], 'public');
    }

    private function publishRoutes()
    {
        // $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
    }

    private function publishTranslations()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'zoho');
        // $this->loadJsonTranslationsFrom(__DIR__.'/../../resources/lang', 'zoho');
        // $this->publishes([
        //     __DIR__.'/../../resources/lang' => resource_path('lang/vendor/zoho'),
        // ], 'translations');
    }

    private function publishViews()
    {
        // $this->loadViewsFrom(__DIR__.'/../../resources/views', 'zoho');
        // $this->publishes([
        //     __DIR__.'/../../resources/views' => resource_path('views/vendor/zoho'),
        // ], 'views');
    }

    private function publishMigrations()
    {
        // $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        // $this->publishes([
        //     __DIR__.'/../../database/migrations/' => database_path('migrations')
        // ], 'migrations');
    }

    private function publishCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                SetupCommand::class,
            ]);
        }
    }

    private function registerSingleton()
    {
        //
    }
}
