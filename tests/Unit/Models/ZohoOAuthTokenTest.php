<?php

namespace Asciisd\Zoho\Tests\Unit\Models;

use Asciisd\Zoho\Models\ZohoOAuthToken;
use Asciisd\Zoho\Tests\TestCase;

class ZohoOAuthTokenTest extends TestCase
{
    public function test_it_has_correct_fillable_attributes(): void
    {
        $token = new ZohoOAuthToken;

        $this->assertEquals([
            'user_identifier',
            'access_token',
            'refresh_token',
            'expires_at',
            'token_type',
            'grant_token',
            'data_center',
            'environment',
        ], $token->getFillable());
    }

    public function test_expires_at_is_cast_to_datetime(): void
    {
        $token = new ZohoOAuthToken;

        $this->assertArrayHasKey('expires_at', $token->getCasts());
    }

    public function test_is_expired_returns_true_when_no_expiry(): void
    {
        $token = new ZohoOAuthToken;
        $token->expires_at = null;

        $this->assertTrue($token->isExpired());
    }

    public function test_is_expired_returns_true_when_past(): void
    {
        $token = new ZohoOAuthToken;
        $token->expires_at = now()->subHour();

        $this->assertTrue($token->isExpired());
    }

    public function test_is_expired_returns_false_when_future(): void
    {
        $token = new ZohoOAuthToken;
        $token->expires_at = now()->addHour();

        $this->assertFalse($token->isExpired());
    }

    public function test_is_valid_returns_true_when_not_expired_with_token(): void
    {
        $token = new ZohoOAuthToken;
        $token->access_token = 'valid-token';
        $token->expires_at = now()->addHour();

        $this->assertTrue($token->isValid());
    }

    public function test_is_valid_returns_false_when_expired(): void
    {
        $token = new ZohoOAuthToken;
        $token->access_token = 'expired-token';
        $token->expires_at = now()->subHour();

        $this->assertFalse($token->isValid());
    }

    public function test_is_valid_returns_false_when_no_access_token(): void
    {
        $token = new ZohoOAuthToken;
        $token->access_token = '';
        $token->expires_at = now()->addHour();

        $this->assertFalse($token->isValid());
    }

    public function test_create_and_retrieve_from_database(): void
    {
        ZohoOAuthToken::create([
            'user_identifier' => 'test-user',
            'access_token' => 'access-123',
            'refresh_token' => 'refresh-456',
            'expires_at' => now()->addHour(),
            'token_type' => 'Bearer',
            'data_center' => 'US',
            'environment' => 'production',
        ]);

        $token = ZohoOAuthToken::where('user_identifier', 'test-user')->first();

        $this->assertNotNull($token);
        $this->assertEquals('access-123', $token->access_token);
        $this->assertEquals('refresh-456', $token->refresh_token);
    }
}
