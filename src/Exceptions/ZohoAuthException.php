<?php

namespace Asciisd\Zoho\Exceptions;

class ZohoAuthException extends ZohoException
{
    /**
     * Create a new authentication exception.
     */
    public static function invalidCredentials(): self
    {
        return new self('Invalid Zoho CRM credentials provided.', 401);
    }

    /**
     * Create a new authentication exception for missing configuration.
     */
    public static function missingConfiguration(string $key): self
    {
        return new self("Missing Zoho CRM configuration: {$key}", 500);
    }

    /**
     * Create a new authentication exception for token generation failure.
     */
    public static function tokenGenerationFailed(string $reason = ''): self
    {
        $message = 'Failed to generate access token';

        if ($reason) {
            $message .= ": {$reason}";
        }

        return new self($message, 500);
    }

    /**
     * Create a new authentication exception for token refresh failure.
     */
    public static function tokenRefreshFailed(string $reason = ''): self
    {
        $message = 'Failed to refresh access token';

        if ($reason) {
            $message .= ": {$reason}";
        }

        return new self($message, 500);
    }

    /**
     * Create a new authentication exception for expired token.
     */
    public static function tokenExpired(): self
    {
        return new self('Access token has expired.', 401);
    }
}
