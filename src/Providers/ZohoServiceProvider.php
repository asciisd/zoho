<?php

namespace Asciisd\Zoho\Providers;

use App\Support\Zoho\TokenStore;
use Asciisd\Zoho\Console\Commands\ZohoAuthentication;
use Asciisd\Zoho\Console\Commands\ZohoInstallCommand;
use Asciisd\Zoho\Console\Commands\ZohoSetupCommand;
use Asciisd\Zoho\RestClient;
use Asciisd\Zoho\Zoho;
use com\zoho\api\authenticator\OAuthToken;
use com\zoho\api\authenticator\TokenType;
use com\zoho\api\logger\Levels;
use com\zoho\api\logger\Logger;
use com\zoho\crm\api\dc\EUDataCenter;
use com\zoho\crm\api\Initializer;
use com\zoho\crm\api\SDKConfigBuilder;
use com\zoho\crm\api\UserSignature;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
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
        $this->registerRoutes();
//        $this->registerResources();
        $this->registerMigrations();
        $this->registerPublishing();
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        if (Zoho::$registersRoutes) {
            Route::group([
                'prefix' => config('zoho.path'),
                'namespace' => 'Asciisd\Zoho\Http\Controllers',
                'as' => 'zoho.',
            ], function () {
                $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
            });
        }
    }

    /**
     * Register the package migrations.
     *
     * @return void
     */
    protected function registerMigrations()
    {
        if (Zoho::$runsMigrations && $this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        }
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/zoho.php' => $this->app->configPath('zoho.php'),
            ], 'zoho-config');

            $this->publishes([
                __DIR__ . '/../../database/migrations' => $this->app->databasePath('migrations'),
            ], 'zoho-migrations');

//            $this->publishes([
//                __DIR__ . '/../../resources/views' => $this->app->resourcePath('views/vendor/zoho'),
//            ], 'zoho-views');
//
//            $this->publishes([
//                __DIR__ . '/../../public' => public_path('vendor/zoho'),
//            ], 'zoho-assets');

            $this->publishes([
                __DIR__ . '/../Storage/oauth' => storage_path('zoho/oauth'),
            ], 'zoho-oauth');

            // use if you want to use application service provider
//            $this->publishes([
//                __DIR__ . '/../../stubs/ZohoServiceProvider.stub' => app_path('Providers/ZohoServiceProvider.php'),
//            ], 'zoho-provider');
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->configure();
        $this->registerCommands();
        $this->registerSingleton();

        if (!class_exists('Zoho')) {
            class_alias('Asciisd\Zoho\Zoho', 'Zoho');
        }
    }

    /**
     * Setup the configuration for Zoho.
     *
     * @return void
     */
    protected function configure()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/zoho.php', 'zoho'
        );
    }

    private function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ZohoInstallCommand::class,
                ZohoSetupCommand::class,
                ZohoAuthentication::class
            ]);
        }
    }

    private function registerSingleton()
    {
        $this->app->bind('zoho_manager', function ($app) {
            $zohoOptions = collect(Zoho::zohoOptions());
            $logger = Logger::getInstance(Levels::ALL, $zohoOptions->get('applicationLogFilePath') . '/zoho-api.log');
            $user = new UserSignature($zohoOptions->get('currentUserEmail'));
            $tokenStore = new TokenStore('zoho/oauth/tokens');
            $token = new OAuthToken(
                $zohoOptions->get('client_id'),
                $zohoOptions->get('client_secret'),
                '',
                TokenType::REFRESH,
                $zohoOptions->get('redirect_uri')
            );
            $token = $tokenStore->getToken($user, $token);
            if ($token) {
                $sdkConfig = (new SDKConfigBuilder())->setAutoRefreshFields(true)->setPickListValidation(false)->setSSLVerification(true)->connectionTimeout(2)->timeout(2)->build();
                $environment = EUDataCenter::PRODUCTION();
                Initializer::initialize($user, $environment, $token, $tokenStore, $sdkConfig, Storage::disk('local')->path('zoho/resources/'), $logger);
            }
            return new RestClient(Initializer::getInitializer());
        });
    }

    /**
     * Register the package resources.
     *
     * @return void
     */
    protected function registerResources()
    {
        $this->loadJsonTranslationsFrom(__DIR__ . '/../../resources/lang');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'zoho');
    }
}
