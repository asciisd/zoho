<?php

namespace Asciisd\Zoho\Tests\Unit\Auth;

use Asciisd\Zoho\Auth\OAuthManager;
use Asciisd\Zoho\Exceptions\ZohoAuthException;
use Asciisd\Zoho\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class OAuthManagerTest extends TestCase
{
    protected OAuthManager $oauth;

    protected function setUp(): void
    {
        parent::setUp();
        $this->oauth = app('zoho.oauth');
    }

    public function test_it_throws_exception_when_client_id_is_missing(): void
    {
        config(['zoho.client_id' => '']);

        $this->expectException(ZohoAuthException::class);
        $this->expectExceptionMessage('client_id');

        new OAuthManager;
    }

    public function test_it_throws_exception_when_client_secret_is_missing(): void
    {
        config(['zoho.client_secret' => '']);

        $this->expectException(ZohoAuthException::class);
        $this->expectExceptionMessage('client_secret');

        new OAuthManager;
    }

    public function test_it_throws_exception_when_redirect_uri_is_missing(): void
    {
        config(['zoho.redirect_uri' => '']);

        $this->expectException(ZohoAuthException::class);
        $this->expectExceptionMessage('redirect_uri');

        new OAuthManager;
    }

    public function test_get_authorization_url_returns_correct_url(): void
    {
        $url = $this->oauth->getAuthorizationUrl();

        $this->assertStringContainsString('https://accounts.zoho.com/oauth/v2/auth', $url);
        $this->assertStringContainsString('client_id=test-client-id', $url);
        $this->assertStringContainsString('response_type=code', $url);
        $this->assertStringContainsString('access_type=offline', $url);
        $this->assertStringContainsString(urlencode('https://example.com/zoho/callback'), $url);
    }

    public function test_get_authorization_url_with_custom_scope(): void
    {
        $url = $this->oauth->getAuthorizationUrl('ZohoCRM.modules.READ');

        $this->assertStringContainsString(urlencode('ZohoCRM.modules.READ'), $url);
    }

    public function test_get_authorization_url_respects_data_center(): void
    {
        config(['zoho.data_center' => 'EU']);
        $oauth = new OAuthManager;

        $url = $oauth->getAuthorizationUrl();

        $this->assertStringContainsString('https://accounts.zoho.eu', $url);
    }

    public function test_generate_access_token_stores_tokens_on_success(): void
    {
        Http::fake([
            'accounts.zoho.com/oauth/v2/token' => Http::response([
                'access_token' => 'test-access-token',
                'refresh_token' => 'test-refresh-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
        ]);

        $tokens = $this->oauth->generateAccessToken('test-grant-token');

        $this->assertArrayHasKey('access_token', $tokens);
        $this->assertEquals('test-access-token', $tokens['access_token']);
        $this->assertEquals('test-refresh-token', $tokens['refresh_token']);
    }

    public function test_generate_access_token_throws_on_failure(): void
    {
        Http::fake([
            'accounts.zoho.com/oauth/v2/token' => Http::response([
                'error' => 'invalid_code',
            ], 400),
        ]);

        $this->expectException(ZohoAuthException::class);

        $this->oauth->generateAccessToken('invalid-grant-token');
    }

    public function test_refresh_access_token_uses_stored_refresh_token(): void
    {
        $storage = app('zoho.storage');
        $storage->storeTokens([
            'access_token' => 'old-access-token',
            'refresh_token' => 'stored-refresh-token',
            'expires_in' => 3600,
        ]);

        Http::fake([
            'accounts.zoho.com/oauth/v2/token' => Http::response([
                'access_token' => 'new-access-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
        ]);

        $tokens = $this->oauth->refreshAccessToken();

        $this->assertEquals('new-access-token', $tokens['access_token']);
    }

    public function test_refresh_access_token_falls_back_to_config(): void
    {
        config(['zoho.refresh_token' => 'config-refresh-token']);

        Http::fake([
            'accounts.zoho.com/oauth/v2/token' => Http::response([
                'access_token' => 'new-access-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
        ]);

        $tokens = $this->oauth->refreshAccessToken();

        $this->assertEquals('new-access-token', $tokens['access_token']);
        Http::assertSent(function ($request) {
            return $request['refresh_token'] === 'config-refresh-token';
        });
    }

    public function test_refresh_access_token_throws_when_no_refresh_token(): void
    {
        config(['zoho.refresh_token' => null]);

        $this->expectException(ZohoAuthException::class);
        $this->expectExceptionMessage('No refresh token available');

        $this->oauth->refreshAccessToken();
    }

    public function test_refresh_access_token_throws_on_api_failure(): void
    {
        Http::fake([
            'accounts.zoho.com/oauth/v2/token' => Http::response([
                'error' => 'invalid_refresh_token',
            ], 401),
        ]);

        $this->expectException(ZohoAuthException::class);

        $this->oauth->refreshAccessToken('bad-refresh-token');
    }

    public function test_get_valid_access_token_returns_token_when_not_expired(): void
    {
        $storage = app('zoho.storage');
        $storage->storeTokens([
            'access_token' => 'valid-token',
            'refresh_token' => 'refresh-token',
            'expires_in' => 3600,
        ]);

        $token = $this->oauth->getValidAccessToken();

        $this->assertEquals('valid-token', $token);
    }

    public function test_get_valid_access_token_refreshes_expired_token(): void
    {
        $storage = app('zoho.storage');
        $storage->storeTokens([
            'access_token' => 'expired-token',
            'refresh_token' => 'refresh-token',
            'expires_in' => -3600,
        ]);

        Http::fake([
            'accounts.zoho.com/oauth/v2/token' => Http::response([
                'access_token' => 'refreshed-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
        ]);

        $token = $this->oauth->getValidAccessToken();

        $this->assertEquals('refreshed-token', $token);
    }

    public function test_get_valid_access_token_handles_corrupted_expires_at(): void
    {
        $storage = app('zoho.storage');
        $storage->storeTokens([
            'access_token' => 'corrupted-token',
            'refresh_token' => 'refresh-token',
            'expires_in' => 3600,
        ]);

        $cacheKey = 'zoho_tokens:default:US:production';
        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        $cached['expires_at'] = new \__PHP_Incomplete_Class;
        \Illuminate\Support\Facades\Cache::put($cacheKey, $cached, 3600);

        Http::fake([
            'accounts.zoho.com/oauth/v2/token' => Http::response([
                'access_token' => 'recovered-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
        ]);

        $token = $this->oauth->getValidAccessToken();

        $this->assertEquals('recovered-token', $token);
    }

    public function test_revoke_token_sends_revoke_request(): void
    {
        Http::fake([
            'accounts.zoho.com/oauth/v2/token/revoke' => Http::response([], 200),
        ]);

        $result = $this->oauth->revokeToken('test-token');

        $this->assertTrue($result);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'revoke')
                && $request['token'] === 'test-token';
        });
    }

    public function test_revoke_token_returns_false_when_no_token(): void
    {
        $result = $this->oauth->revokeToken();

        $this->assertFalse($result);
    }

    public function test_is_authenticated_returns_true_when_tokens_exist(): void
    {
        $storage = app('zoho.storage');
        $storage->storeTokens([
            'access_token' => 'valid-token',
            'expires_in' => 3600,
        ]);

        $this->assertTrue($this->oauth->isAuthenticated());
    }

    public function test_is_authenticated_returns_false_when_no_tokens(): void
    {
        $this->assertFalse($this->oauth->isAuthenticated());
    }

    public function test_get_api_url_returns_correct_url_for_us(): void
    {
        $this->assertEquals('https://www.zohoapis.com', $this->oauth->getApiUrl());
    }

    public function test_get_api_url_returns_correct_url_for_eu(): void
    {
        config(['zoho.data_center' => 'EU']);
        $oauth = new OAuthManager;

        $this->assertEquals('https://www.zohoapis.eu', $oauth->getApiUrl());
    }

    public function test_get_api_url_returns_correct_url_for_all_data_centers(): void
    {
        $expected = [
            'US' => 'https://www.zohoapis.com',
            'EU' => 'https://www.zohoapis.eu',
            'IN' => 'https://www.zohoapis.in',
            'CN' => 'https://www.zohoapis.com.cn',
            'JP' => 'https://www.zohoapis.jp',
            'AU' => 'https://www.zohoapis.com.au',
            'CA' => 'https://www.zohoapis.ca',
        ];

        foreach ($expected as $dc => $url) {
            config(['zoho.data_center' => $dc]);
            $oauth = new OAuthManager;
            $this->assertEquals($url, $oauth->getApiUrl(), "Failed for data center: {$dc}");
        }
    }
}
