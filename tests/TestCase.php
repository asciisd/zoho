<?php

namespace Asciisd\Zoho\Tests;

use Asciisd\Zoho\ZohoServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ZohoServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Zoho' => \Asciisd\Zoho\Facades\Zoho::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('zoho.client_id', 'test-client-id');
        $app['config']->set('zoho.client_secret', 'test-client-secret');
        $app['config']->set('zoho.redirect_uri', 'https://example.com/zoho/callback');
        $app['config']->set('zoho.data_center', 'US');
        $app['config']->set('zoho.environment', 'production');
        $app['config']->set('zoho.token_storage', 'cache');
        $app['config']->set('zoho.cache_driver', 'array');
        $app['config']->set('zoho.cache_ttl', 3600);
        $app['config']->set('zoho.webhook_secret', 'test-webhook-secret');
        $app['config']->set('zoho.sync.enabled', true);
        $app['config']->set('zoho.sync.queue', 'default');
        $app['config']->set('zoho.api_version', 'v8');
        $app['config']->set('zoho.max_fields_per_request', 50);
        $app['config']->set('zoho.pagination.per_page', 200);
        $app['config']->set('cache.default', 'array');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }
}
