<?php

namespace Asciisd\Zoho\Tests\Unit\Exceptions;

use Asciisd\Zoho\Exceptions\ZohoApiException;
use Asciisd\Zoho\Exceptions\ZohoAuthException;
use Asciisd\Zoho\Exceptions\ZohoException;
use Asciisd\Zoho\Exceptions\ZohoTokenException;
use Asciisd\Zoho\Tests\TestCase;

class ExceptionsTest extends TestCase
{
    // ZohoException
    public function test_zoho_exception_renders_json(): void
    {
        $exception = new ZohoException('Test error', 500);

        $response = $exception->render();

        $this->assertEquals(500, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['error']);
        $this->assertEquals('Test error', $data['message']);
        $this->assertEquals(500, $data['code']);
    }

    // ZohoApiException factory methods
    public function test_record_not_found_exception(): void
    {
        $e = ZohoApiException::recordNotFound('Contacts', '123');

        $this->assertInstanceOf(ZohoApiException::class, $e);
        $this->assertEquals(404, $e->getCode());
        $this->assertStringContainsString('Contacts', $e->getMessage());
        $this->assertStringContainsString('123', $e->getMessage());
    }

    public function test_invalid_module_exception(): void
    {
        $e = ZohoApiException::invalidModule('BadModule');

        $this->assertEquals(400, $e->getCode());
        $this->assertStringContainsString('BadModule', $e->getMessage());
    }

    public function test_request_failed_exception(): void
    {
        $e = ZohoApiException::requestFailed('Server error', 502);

        $this->assertEquals(502, $e->getCode());
        $this->assertStringContainsString('Server error', $e->getMessage());
    }

    public function test_invalid_data_exception(): void
    {
        $e = ZohoApiException::invalidData('Missing field');

        $this->assertEquals(400, $e->getCode());
        $this->assertStringContainsString('Missing field', $e->getMessage());
    }

    public function test_rate_limit_exceeded_exception(): void
    {
        $e = ZohoApiException::rateLimitExceeded();

        $this->assertEquals(429, $e->getCode());
        $this->assertStringContainsString('rate limit', $e->getMessage());
    }

    public function test_insufficient_permissions_exception(): void
    {
        $e = ZohoApiException::insufficientPermissions('delete records');

        $this->assertEquals(403, $e->getCode());
        $this->assertStringContainsString('delete records', $e->getMessage());
    }

    public function test_insufficient_permissions_without_action(): void
    {
        $e = ZohoApiException::insufficientPermissions();

        $this->assertStringContainsString('Insufficient permissions', $e->getMessage());
    }

    // ZohoAuthException factory methods
    public function test_invalid_credentials_exception(): void
    {
        $e = ZohoAuthException::invalidCredentials();

        $this->assertEquals(401, $e->getCode());
    }

    public function test_missing_configuration_exception(): void
    {
        $e = ZohoAuthException::missingConfiguration('client_id');

        $this->assertEquals(500, $e->getCode());
        $this->assertStringContainsString('client_id', $e->getMessage());
    }

    public function test_token_generation_failed_exception(): void
    {
        $e = ZohoAuthException::tokenGenerationFailed('invalid_code');

        $this->assertEquals(500, $e->getCode());
        $this->assertStringContainsString('invalid_code', $e->getMessage());
    }

    public function test_token_generation_failed_without_reason(): void
    {
        $e = ZohoAuthException::tokenGenerationFailed();

        $this->assertStringContainsString('Failed to generate', $e->getMessage());
    }

    public function test_token_refresh_failed_exception(): void
    {
        $e = ZohoAuthException::tokenRefreshFailed('expired');

        $this->assertStringContainsString('expired', $e->getMessage());
    }

    public function test_token_expired_exception(): void
    {
        $e = ZohoAuthException::tokenExpired();

        $this->assertEquals(401, $e->getCode());
    }

    // ZohoTokenException factory methods
    public function test_missing_token_exception(): void
    {
        $e = ZohoTokenException::missingToken('refresh');

        $this->assertEquals(401, $e->getCode());
        $this->assertStringContainsString('refresh', $e->getMessage());
    }

    public function test_invalid_token_exception(): void
    {
        $e = ZohoTokenException::invalidToken();

        $this->assertEquals(401, $e->getCode());
    }

    public function test_storage_failed_exception(): void
    {
        $e = ZohoTokenException::storageFailed('disk full');

        $this->assertEquals(500, $e->getCode());
        $this->assertStringContainsString('disk full', $e->getMessage());
    }

    public function test_storage_failed_without_reason(): void
    {
        $e = ZohoTokenException::storageFailed();

        $this->assertStringContainsString('Failed to store', $e->getMessage());
    }

    public function test_retrieval_failed_exception(): void
    {
        $e = ZohoTokenException::retrievalFailed('connection lost');

        $this->assertStringContainsString('connection lost', $e->getMessage());
    }

    public function test_refresh_token_expired_exception(): void
    {
        $e = ZohoTokenException::refreshTokenExpired();

        $this->assertEquals(401, $e->getCode());
        $this->assertStringContainsString('re-authenticate', $e->getMessage());
    }

    // Inheritance checks
    public function test_api_exception_extends_zoho_exception(): void
    {
        $this->assertInstanceOf(ZohoException::class, new ZohoApiException('test'));
    }

    public function test_auth_exception_extends_zoho_exception(): void
    {
        $this->assertInstanceOf(ZohoException::class, new ZohoAuthException('test'));
    }

    public function test_token_exception_extends_zoho_exception(): void
    {
        $this->assertInstanceOf(ZohoException::class, new ZohoTokenException('test'));
    }
}
