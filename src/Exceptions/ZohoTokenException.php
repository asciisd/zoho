<?php

namespace Asciisd\Zoho\Exceptions;

class ZohoTokenException extends ZohoException
{
    /**
     * Create a new token exception for missing token.
     */
    public static function missingToken(string $type = 'access'): self
    {
        return new self("Missing {$type} token. Please authenticate first.", 401);
    }

    /**
     * Create a new token exception for invalid token.
     */
    public static function invalidToken(string $type = 'access'): self
    {
        return new self("Invalid {$type} token provided.", 401);
    }

    /**
     * Create a new token exception for token storage failure.
     */
    public static function storageFailed(string $reason = ''): self
    {
        $message = 'Failed to store token';

        if ($reason) {
            $message .= ": {$reason}";
        }

        return new self($message, 500);
    }

    /**
     * Create a new token exception for token retrieval failure.
     */
    public static function retrievalFailed(string $reason = ''): self
    {
        $message = 'Failed to retrieve token';

        if ($reason) {
            $message .= ": {$reason}";
        }

        return new self($message, 500);
    }

    /**
     * Create a new token exception for expired refresh token.
     */
    public static function refreshTokenExpired(): self
    {
        return new self('Refresh token has expired. Please re-authenticate.', 401);
    }
}
