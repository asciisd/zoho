<?php

namespace Asciisd\Zoho\Tests\Feature;

use Asciisd\Zoho\Auth\OAuthManager;
use Asciisd\Zoho\Storage\TokenStorage;
use Asciisd\Zoho\Tests\TestCase;
use Asciisd\Zoho\ZohoClient;

class ZohoServiceProviderTest extends TestCase
{
    public function test_zoho_client_is_bound_as_singleton(): void
    {
        $instance1 = app('zoho');
        $instance2 = app('zoho');

        $this->assertInstanceOf(ZohoClient::class, $instance1);
        $this->assertSame($instance1, $instance2);
    }

    public function test_oauth_manager_is_bound_as_singleton(): void
    {
        $instance1 = app('zoho.oauth');
        $instance2 = app('zoho.oauth');

        $this->assertInstanceOf(OAuthManager::class, $instance1);
        $this->assertSame($instance1, $instance2);
    }

    public function test_token_storage_is_bound_as_singleton(): void
    {
        $instance1 = app('zoho.storage');
        $instance2 = app('zoho.storage');

        $this->assertInstanceOf(TokenStorage::class, $instance1);
        $this->assertSame($instance1, $instance2);
    }

    public function test_config_is_merged(): void
    {
        $this->assertNotNull(config('zoho.client_id'));
        $this->assertNotNull(config('zoho.api_version'));
        $this->assertEquals('v8', config('zoho.api_version'));
    }

    public function test_routes_are_registered(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes());

        $routeNames = $routes->pluck('action.as')->filter()->toArray();

        $this->assertContains('zoho.callback', $routeNames);
        $this->assertContains('zoho.webhook', $routeNames);
        $this->assertContains('zoho.webhook.verify', $routeNames);
    }

    public function test_commands_are_registered(): void
    {
        $this->artisan('list')
            ->assertExitCode(0);

        $commands = array_keys(\Artisan::all());

        $this->assertContains('zoho:setup', $commands);
        $this->assertContains('zoho:auth', $commands);
        $this->assertContains('zoho:token:refresh', $commands);
    }

    public function test_facade_resolves_to_zoho_client(): void
    {
        $client = \Asciisd\Zoho\Facades\Zoho::getFacadeRoot();

        $this->assertInstanceOf(ZohoClient::class, $client);
    }

    public function test_provides_returns_correct_services(): void
    {
        $provider = app()->getProvider(\Asciisd\Zoho\ZohoServiceProvider::class);

        $provides = $provider->provides();

        $this->assertContains('zoho', $provides);
        $this->assertContains('zoho.oauth', $provides);
        $this->assertContains('zoho.storage', $provides);
    }
}
