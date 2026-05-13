<?php

namespace Asciisd\Zoho\Tests\Unit\Storage;

use Asciisd\Zoho\Storage\TokenStorage;
use Asciisd\Zoho\Tests\TestCase;
use Illuminate\Support\Facades\Cache;

class TokenStorageTest extends TestCase
{
    protected TokenStorage $storage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->storage = app('zoho.storage');
    }

    public function test_store_and_retrieve_tokens_via_cache(): void
    {
        config(['zoho.token_storage' => 'cache']);
        $storage = new TokenStorage;

        $storage->storeTokens([
            'access_token' => 'test-access',
            'refresh_token' => 'test-refresh',
            'expires_in' => 3600,
            'token_type' => 'Bearer',
        ]);

        $tokens = $storage->getTokens();

        $this->assertEquals('test-access', $tokens['access_token']);
        $this->assertEquals('test-refresh', $tokens['refresh_token']);
        $this->assertEquals('Bearer', $tokens['token_type']);
    }

    public function test_store_and_retrieve_tokens_via_database(): void
    {
        config(['zoho.token_storage' => 'database']);
        $storage = new TokenStorage;

        $storage->storeTokens([
            'access_token' => 'db-access',
            'refresh_token' => 'db-refresh',
            'expires_in' => 3600,
            'token_type' => 'Bearer',
        ]);

        $tokens = $storage->getTokens();

        $this->assertEquals('db-access', $tokens['access_token']);
        $this->assertEquals('db-refresh', $tokens['refresh_token']);
    }

    public function test_store_and_retrieve_tokens_via_both(): void
    {
        config(['zoho.token_storage' => 'both']);
        $storage = new TokenStorage;

        $storage->storeTokens([
            'access_token' => 'both-access',
            'refresh_token' => 'both-refresh',
            'expires_in' => 3600,
        ]);

        $tokens = $storage->getTokens();

        $this->assertEquals('both-access', $tokens['access_token']);
    }

    public function test_get_access_token(): void
    {
        $this->storage->storeTokens([
            'access_token' => 'my-access-token',
            'expires_in' => 3600,
        ]);

        $this->assertEquals('my-access-token', $this->storage->getAccessToken());
    }

    public function test_get_refresh_token(): void
    {
        $this->storage->storeTokens([
            'access_token' => 'access',
            'refresh_token' => 'my-refresh-token',
            'expires_in' => 3600,
        ]);

        $this->assertEquals('my-refresh-token', $this->storage->getRefreshToken());
    }

    public function test_delete_tokens(): void
    {
        $this->storage->storeTokens([
            'access_token' => 'to-delete',
            'expires_in' => 3600,
        ]);

        $this->assertTrue($this->storage->hasTokens());

        $this->storage->deleteTokens();

        $this->assertFalse($this->storage->hasTokens());
    }

    public function test_has_tokens_returns_false_when_empty(): void
    {
        $this->assertFalse($this->storage->hasTokens());
    }

    public function test_has_tokens_returns_true_when_present(): void
    {
        $this->storage->storeTokens([
            'access_token' => 'present-token',
            'expires_in' => 3600,
        ]);

        $this->assertTrue($this->storage->hasTokens());
    }

    public function test_get_tokens_returns_null_when_empty(): void
    {
        $this->assertNull($this->storage->getTokens());
    }

    public function test_get_access_token_returns_null_when_empty(): void
    {
        $this->assertNull($this->storage->getAccessToken());
    }

    public function test_get_refresh_token_returns_null_when_empty(): void
    {
        $this->assertNull($this->storage->getRefreshToken());
    }

    public function test_tokens_stored_with_correct_metadata(): void
    {
        $this->storage->storeTokens([
            'access_token' => 'meta-test',
            'refresh_token' => 'meta-refresh',
            'expires_in' => 7200,
            'token_type' => 'Bearer',
            'grant_token' => 'meta-grant',
        ]);

        $tokens = $this->storage->getTokens();

        $this->assertEquals('US', $tokens['data_center']);
        $this->assertEquals('production', $tokens['environment']);
        $this->assertEquals('default', $tokens['user_identifier']);
    }

    public function test_tokens_stored_per_user_identifier(): void
    {
        $this->storage->storeTokens(
            ['access_token' => 'user-a-token', 'expires_in' => 3600],
            'user-a'
        );

        $this->storage->storeTokens(
            ['access_token' => 'user-b-token', 'expires_in' => 3600],
            'user-b'
        );

        $this->assertEquals('user-a-token', $this->storage->getAccessToken('user-a'));
        $this->assertEquals('user-b-token', $this->storage->getAccessToken('user-b'));
    }

    public function test_expires_at_is_stored_as_string(): void
    {
        $this->storage->storeTokens([
            'access_token' => 'string-test',
            'expires_in' => 3600,
        ]);

        $tokens = $this->storage->getTokens();

        $this->assertIsString($tokens['expires_at']);
    }

    public function test_database_fallback_when_cache_empty_in_both_mode(): void
    {
        config(['zoho.token_storage' => 'both']);
        $storage = new TokenStorage;

        $storage->storeTokens([
            'access_token' => 'fallback-test',
            'expires_in' => 3600,
        ]);

        Cache::driver('array')->forget('zoho_tokens:default:US:production');

        $tokens = $storage->getTokens();

        $this->assertEquals('fallback-test', $tokens['access_token']);
    }
}
