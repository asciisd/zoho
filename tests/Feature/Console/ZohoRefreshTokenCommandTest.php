<?php

namespace Asciisd\Zoho\Tests\Feature\Console;

use Asciisd\Zoho\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class ZohoRefreshTokenCommandTest extends TestCase
{
    public function test_refresh_command_succeeds(): void
    {
        app('zoho.storage')->storeTokens([
            'access_token' => 'old-access',
            'refresh_token' => 'stored-refresh',
            'expires_in' => 3600,
        ]);

        Http::fake([
            'accounts.zoho.com/oauth/v2/token' => Http::response([
                'access_token' => 'new-access-via-command',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
        ]);

        $this->artisan('zoho:token:refresh')
            ->expectsOutputToContain('Token refreshed successfully')
            ->assertExitCode(0);
    }

    public function test_refresh_command_with_clear_cache(): void
    {
        app('zoho.storage')->storeTokens([
            'access_token' => 'cached-token',
            'refresh_token' => 'refresh-for-clear',
            'expires_in' => 3600,
        ]);

        config(['zoho.refresh_token' => 'config-refresh-for-clear']);

        Http::fake([
            'accounts.zoho.com/oauth/v2/token' => Http::response([
                'access_token' => 'fresh-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
        ]);

        $this->artisan('zoho:token:refresh', ['--clear-cache' => true])
            ->expectsOutputToContain('Cache cleared')
            ->expectsOutputToContain('Token refreshed successfully')
            ->assertExitCode(0);
    }

    public function test_refresh_command_fails_without_token(): void
    {
        config(['zoho.refresh_token' => null]);

        $this->artisan('zoho:token:refresh')
            ->expectsOutputToContain('Token refresh failed')
            ->assertExitCode(1);
    }
}
