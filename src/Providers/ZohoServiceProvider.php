<?php

namespace Asciisd\Zoho\Providers;

use Asciisd\Zoho\Console\Commands\ZohoSetupCommand;
use Asciisd\Zoho\RestClient;
use Illuminate\Support\ServiceProvider;
use zcrmsdk\crm\setup\restclient\ZCRMRestClient;

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
            __DIR__ . '/../Storage/oauth' => storage_path('app/zoho/oauth'),
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
                ZohoSetupCommand::class,
            ]);
        }
    }

    private function registerSingleton()
    {
        $this->app->singleton('zoho', function ($app) {
            $configuration = [
                'client_id' => config('zoho.client_id'),
                'client_secret' => config('zoho.client_secret'),
                'redirect_uri' => config('zoho.redirect_uri'),
                'currentUserEmail' => 'it@caveo-kw.com',
                'applicationLogFilePath' => config('zoho.application_log_file_path'),
                'token_persistence_path' => config('zoho.token_persistence_path'),
                'accounts_url' => config('zoho.accounts_url'),
                'sandbox' => config('zoho.sandbox'),
                'apiBaseUrl' => config('zoho.api_base_url'),
                'apiVersion' => config('zoho.api_version'),
                'access_type' => config('zoho.access_type'),
                'persistence_handler_class' => config('zoho.persistence_handler_class'),
            ];

            ZCRMRestClient::initialize($configuration);
            return new RestClient(ZCRMRestClient::getInstance());
        });
    }
}
