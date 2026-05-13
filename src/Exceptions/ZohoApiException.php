<?php

namespace Asciisd\Zoho\Exceptions;

class ZohoApiException extends ZohoException
{
    /**
     * Create a new API exception for record not found.
     */
    public static function recordNotFound(string $module, string $id): self
    {
        return new self("Record not found in {$module} with ID: {$id}", 404);
    }

    /**
     * Create a new API exception for invalid module.
     */
    public static function invalidModule(string $module): self
    {
        return new self("Invalid Zoho CRM module: {$module}", 400);
    }

    /**
     * Create a new API exception for API request failure.
     */
    public static function requestFailed(string $message, int $code = 500): self
    {
        return new self("API request failed: {$message}", $code);
    }

    /**
     * Create a new API exception for invalid data.
     */
    public static function invalidData(string $message): self
    {
        return new self("Invalid data: {$message}", 400);
    }

    /**
     * Create a new API exception for rate limit exceeded.
     */
    public static function rateLimitExceeded(): self
    {
        return new self('API rate limit exceeded. Please try again later.', 429);
    }

    /**
     * Create a new API exception for insufficient permissions.
     */
    public static function insufficientPermissions(string $action = ''): self
    {
        $message = 'Insufficient permissions';

        if ($action) {
            $message .= " to {$action}";
        }

        return new self($message, 403);
    }
}
