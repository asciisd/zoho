<?php

namespace Asciisd\Zoho\Storage;

use Asciisd\Zoho\Exceptions\ZohoTokenException;
use Asciisd\Zoho\Models\ZohoOAuthToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TokenStorage
{
    protected string $storageMethod;

    protected string $cacheDriver;

    protected int $cacheTtl;

    public function __construct()
    {
        $this->storageMethod = config('zoho.token_storage', 'both');
        $this->cacheDriver = config('zoho.cache_driver', 'file');
        $this->cacheTtl = config('zoho.cache_ttl', 3600);
    }

    /**
     * Store OAuth tokens.
     */
    public function storeTokens(array $tokens, string $userIdentifier = 'default'): bool
    {
        try {
            $data = [
                'user_identifier' => $userIdentifier,
                'access_token' => $tokens['access_token'] ?? null,
                'refresh_token' => $tokens['refresh_token'] ?? null,
                'expires_at' => isset($tokens['expires_in'])
                    ? now()->addSeconds($tokens['expires_in'])->toIso8601String()
                    : null,
                'token_type' => $tokens['token_type'] ?? 'Bearer',
                'grant_token' => $tokens['grant_token'] ?? null,
                'data_center' => config('zoho.data_center', 'US'),
                'environment' => config('zoho.environment', 'production'),
            ];

            if ($this->storageMethod === 'cache' || $this->storageMethod === 'both') {
                $this->storeInCache($data, $userIdentifier);
            }

            if ($this->storageMethod === 'database' || $this->storageMethod === 'both') {
                $this->storeInDatabase($data, $userIdentifier);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to store Zoho tokens: '.$e->getMessage());
            throw ZohoTokenException::storageFailed($e->getMessage());
        }
    }

    /**
     * Retrieve OAuth tokens.
     */
    public function getTokens(string $userIdentifier = 'default'): ?array
    {
        try {
            // Try cache first if enabled
            if ($this->storageMethod === 'cache' || $this->storageMethod === 'both') {
                $cachedTokens = $this->getFromCache($userIdentifier);
                if ($cachedTokens) {
                    return $cachedTokens;
                }
            }

            // Fallback to database
            if ($this->storageMethod === 'database' || $this->storageMethod === 'both') {
                $dbTokens = $this->getFromDatabase($userIdentifier);

                // Refresh cache if we got tokens from database
                if ($dbTokens && ($this->storageMethod === 'both')) {
                    $this->storeInCache($dbTokens, $userIdentifier);
                }

                return $dbTokens;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to retrieve Zoho tokens: '.$e->getMessage());
            throw ZohoTokenException::retrievalFailed($e->getMessage());
        }
    }

    /**
     * Get access token only.
     */
    public function getAccessToken(string $userIdentifier = 'default'): ?string
    {
        $tokens = $this->getTokens($userIdentifier);

        return $tokens['access_token'] ?? null;
    }

    /**
     * Get refresh token only.
     */
    public function getRefreshToken(string $userIdentifier = 'default'): ?string
    {
        $tokens = $this->getTokens($userIdentifier);

        return $tokens['refresh_token'] ?? null;
    }

    /**
     * Delete tokens.
     */
    public function deleteTokens(string $userIdentifier = 'default'): bool
    {
        try {
            if ($this->storageMethod === 'cache' || $this->storageMethod === 'both') {
                $this->deleteFromCache($userIdentifier);
            }

            if ($this->storageMethod === 'database' || $this->storageMethod === 'both') {
                $this->deleteFromDatabase($userIdentifier);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete Zoho tokens: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Check if tokens exist.
     */
    public function hasTokens(string $userIdentifier = 'default'): bool
    {
        $tokens = $this->getTokens($userIdentifier);

        return ! empty($tokens['access_token']);
    }

    /**
     * Store tokens in cache.
     */
    protected function storeInCache(array $data, string $userIdentifier): void
    {
        $cacheKey = $this->getCacheKey($userIdentifier);
        Cache::driver($this->cacheDriver)->put($cacheKey, $data, $this->cacheTtl);
    }

    /**
     * Get tokens from cache.
     */
    protected function getFromCache(string $userIdentifier): ?array
    {
        $cacheKey = $this->getCacheKey($userIdentifier);

        return Cache::driver($this->cacheDriver)->get($cacheKey);
    }

    /**
     * Delete tokens from cache.
     */
    protected function deleteFromCache(string $userIdentifier): void
    {
        $cacheKey = $this->getCacheKey($userIdentifier);
        Cache::driver($this->cacheDriver)->forget($cacheKey);
    }

    /**
     * Store tokens in database.
     */
    protected function storeInDatabase(array $data, string $userIdentifier): void
    {
        ZohoOAuthToken::updateOrCreate(
            [
                'user_identifier' => $userIdentifier,
                'data_center' => $data['data_center'],
                'environment' => $data['environment'],
            ],
            $data
        );
    }

    /**
     * Get tokens from database.
     */
    protected function getFromDatabase(string $userIdentifier): ?array
    {
        $token = ZohoOAuthToken::where('user_identifier', $userIdentifier)
            ->where('data_center', config('zoho.data_center', 'US'))
            ->where('environment', config('zoho.environment', 'production'))
            ->first();

        if (! $token) {
            return null;
        }

        return [
            'access_token' => $token->access_token,
            'refresh_token' => $token->refresh_token,
            'expires_at' => $token->expires_at?->toIso8601String(),
            'token_type' => $token->token_type,
            'grant_token' => $token->grant_token,
            'data_center' => $token->data_center,
            'environment' => $token->environment,
        ];
    }

    /**
     * Delete tokens from database.
     */
    protected function deleteFromDatabase(string $userIdentifier): void
    {
        ZohoOAuthToken::where('user_identifier', $userIdentifier)
            ->where('data_center', config('zoho.data_center', 'US'))
            ->where('environment', config('zoho.environment', 'production'))
            ->delete();
    }

    /**
     * Get cache key for tokens.
     */
    protected function getCacheKey(string $userIdentifier): string
    {
        $dataCenter = config('zoho.data_center', 'US');
        $environment = config('zoho.environment', 'production');

        return "zoho_tokens:{$userIdentifier}:{$dataCenter}:{$environment}";
    }
}
