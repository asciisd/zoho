<?php

namespace Asciisd\Zoho\Tests\Feature\Console;

use Asciisd\Zoho\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class ZohoAuthCommandTest extends TestCase
{
    public function test_status_shows_not_authenticated(): void
    {
        $this->artisan('zoho:auth', ['action' => 'status'])
            ->expectsOutputToContain('Not authenticated')
            ->assertExitCode(1);
    }

    public function test_status_shows_authenticated(): void
    {
        app('zoho.storage')->storeTokens([
            'access_token' => 'test-access-token-for-status',
            'refresh_token' => 'test-refresh-token-for-status',
            'expires_in' => 3600,
            'token_type' => 'Bearer',
        ]);

        $this->artisan('zoho:auth', ['action' => 'status'])
            ->expectsOutputToContain('Authenticated')
            ->assertExitCode(0);
    }

    public function test_refresh_succeeds_with_valid_token(): void
    {
        app('zoho.storage')->storeTokens([
            'access_token' => 'old-token',
            'refresh_token' => 'valid-refresh',
            'expires_in' => 3600,
        ]);

        Http::fake([
            'accounts.zoho.com/oauth/v2/token' => Http::response([
                'access_token' => 'refreshed-token-from-command',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
        ]);

        $this->artisan('zoho:auth', ['action' => 'refresh'])
            ->expectsOutputToContain('Token refreshed successfully')
            ->assertExitCode(0);
    }

    public function test_refresh_fails_with_no_refresh_token(): void
    {
        config(['zoho.refresh_token' => null]);

        $this->artisan('zoho:auth', ['action' => 'refresh'])
            ->expectsOutputToContain('Token refresh failed')
            ->assertExitCode(1);
    }

    public function test_revoke_succeeds(): void
    {
        app('zoho.storage')->storeTokens([
            'access_token' => 'token-to-revoke',
            'expires_in' => 3600,
        ]);

        Http::fake([
            'accounts.zoho.com/oauth/v2/token/revoke' => Http::response([], 200),
        ]);

        $this->artisan('zoho:auth', ['action' => 'revoke'])
            ->expectsConfirmation('Are you sure you want to revoke the access token?', 'yes')
            ->expectsOutputToContain('Token revoked successfully')
            ->assertExitCode(0);
    }

    public function test_revoke_cancelled_by_user(): void
    {
        $this->artisan('zoho:auth', ['action' => 'revoke'])
            ->expectsConfirmation('Are you sure you want to revoke the access token?', 'no')
            ->assertExitCode(0);
    }

    public function test_unknown_action_returns_failure(): void
    {
        $this->artisan('zoho:auth', ['action' => 'unknown'])
            ->expectsOutputToContain('Unknown action')
            ->assertExitCode(1);
    }

    public function test_default_action_is_status(): void
    {
        $this->artisan('zoho:auth')
            ->assertExitCode(1);
    }
}
