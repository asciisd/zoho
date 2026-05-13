<?php

namespace Asciisd\Zoho\Auth;

use Asciisd\Zoho\Exceptions\ZohoAuthException;
use Asciisd\Zoho\Storage\TokenStorage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OAuthManager
{
    protected TokenStorage $storage;

    protected string $clientId;

    protected string $clientSecret;

    protected string $redirectUri;

    protected string $dataCenter;

    protected array $dataCenterUrls = [
        'US' => 'https://accounts.zoho.com',
        'EU' => 'https://accounts.zoho.eu',
        'IN' => 'https://accounts.zoho.in',
        'CN' => 'https://accounts.zoho.com.cn',
        'JP' => 'https://accounts.zoho.jp',
        'AU' => 'https://accounts.zoho.com.au',
        'CA' => 'https://accounts.zohocloud.ca',
    ];

    public function __construct()
    {
        $this->storage = app('zoho.storage');
        $this->clientId = config('zoho.client_id');
        $this->clientSecret = config('zoho.client_secret');
        $this->redirectUri = config('zoho.redirect_uri');
        $this->dataCenter = config('zoho.data_center', 'US');

        $this->validateConfiguration();
    }

    /**
     * Validate OAuth configuration.
     */
    protected function validateConfiguration(): void
    {
        if (empty($this->clientId)) {
            throw ZohoAuthException::missingConfiguration('client_id');
        }

        if (empty($this->clientSecret)) {
            throw ZohoAuthException::missingConfiguration('client_secret');
        }

        if (empty($this->redirectUri)) {
            throw ZohoAuthException::missingConfiguration('redirect_uri');
        }
    }

    /**
     * Get the authorization URL for OAuth flow.
     */
    public function getAuthorizationUrl(string $scope = 'ZohoCRM.modules.ALL,ZohoCRM.settings.ALL'): string
    {
        $baseUrl = $this->getAccountsUrl();

        $params = http_build_query([
            'scope' => $scope,
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'access_type' => 'offline',
            'redirect_uri' => $this->redirectUri,
        ]);

        return "{$baseUrl}/oauth/v2/auth?{$params}";
    }

    /**
     * Generate access token from grant token/code.
     */
    public function generateAccessToken(string $grantToken): array
    {
        try {
            $baseUrl = $this->getAccountsUrl();

            $response = Http::asForm()->post("{$baseUrl}/oauth/v2/token", [
                'grant_type' => 'authorization_code',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri' => $this->redirectUri,
                'code' => $grantToken,
            ]);

            if (! $response->successful()) {
                $error = $response->json('error', 'Unknown error');
                throw ZohoAuthException::tokenGenerationFailed($error);
            }

            $tokens = $response->json();

            // Store tokens
            $this->storage->storeTokens([
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'] ?? null,
                'expires_in' => $tokens['expires_in'] ?? 3600,
                'token_type' => $tokens['token_type'] ?? 'Bearer',
                'grant_token' => $grantToken,
            ]);

            return $tokens;
        } catch (ZohoAuthException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Zoho token generation failed: '.$e->getMessage());
            throw ZohoAuthException::tokenGenerationFailed($e->getMessage());
        }
    }

    /**
     * Refresh access token using refresh token.
     */
    public function refreshAccessToken(?string $refreshToken = null): array
    {
        try {
            if (! $refreshToken) {
                $refreshToken = $this->storage->getRefreshToken();
            }

            if (! $refreshToken) {
                $refreshToken = config('zoho.refresh_token');
            }

            if (! $refreshToken) {
                throw ZohoAuthException::tokenRefreshFailed('No refresh token available');
            }

            $baseUrl = $this->getAccountsUrl();

            $response = Http::asForm()->post("{$baseUrl}/oauth/v2/token", [
                'grant_type' => 'refresh_token',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $refreshToken,
            ]);

            if (! $response->successful()) {
                $error = $response->json('error', 'Unknown error');
                throw ZohoAuthException::tokenRefreshFailed($error);
            }

            $tokens = $response->json();

            // Store new access token
            $this->storage->storeTokens([
                'access_token' => $tokens['access_token'],
                'refresh_token' => $refreshToken, // Keep the same refresh token
                'expires_in' => $tokens['expires_in'] ?? 3600,
                'token_type' => $tokens['token_type'] ?? 'Bearer',
            ]);

            return $tokens;
        } catch (ZohoAuthException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Zoho token refresh failed: '.$e->getMessage());
            throw ZohoAuthException::tokenRefreshFailed($e->getMessage());
        }
    }

    /**
     * Get a valid access token (refresh if expired).
     */
    public function getValidAccessToken(string $userIdentifier = 'default'): string
    {
        $tokens = $this->storage->getTokens($userIdentifier);

        if (! $tokens || empty($tokens['access_token'])) {
            // Try to refresh from config
            $this->refreshAccessToken();
            $tokens = $this->storage->getTokens($userIdentifier);
        }

        // Check if token is expired
        if (isset($tokens['expires_at'])) {
            try {
                $expiresAt = \Carbon\Carbon::parse($tokens['expires_at']);
            } catch (\Throwable) {
                $this->refreshAccessToken($tokens['refresh_token'] ?? null);
                $tokens = $this->storage->getTokens($userIdentifier);

                return $tokens['access_token'] ?? throw ZohoAuthException::tokenExpired();
            }

            if ($expiresAt->isPast()) {
                $this->refreshAccessToken($tokens['refresh_token'] ?? null);
                $tokens = $this->storage->getTokens($userIdentifier);
            }
        }

        return $tokens['access_token'] ?? throw ZohoAuthException::tokenExpired();
    }

    /**
     * Revoke access token.
     */
    public function revokeToken(?string $token = null): bool
    {
        try {
            if (! $token) {
                $token = $this->storage->getAccessToken();
            }

            if (! $token) {
                return false;
            }

            $baseUrl = $this->getAccountsUrl();

            $response = Http::asForm()->post("{$baseUrl}/oauth/v2/token/revoke", [
                'token' => $token,
            ]);

            // Delete stored tokens
            $this->storage->deleteTokens();

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Zoho token revocation failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Check if authenticated.
     */
    public function isAuthenticated(): bool
    {
        return $this->storage->hasTokens();
    }

    /**
     * Get the accounts URL based on data center.
     */
    protected function getAccountsUrl(): string
    {
        return $this->dataCenterUrls[$this->dataCenter] ?? $this->dataCenterUrls['US'];
    }

    /**
     * Get API URL based on data center.
     */
    public function getApiUrl(): string
    {
        $urls = [
            'US' => 'https://www.zohoapis.com',
            'EU' => 'https://www.zohoapis.eu',
            'IN' => 'https://www.zohoapis.in',
            'CN' => 'https://www.zohoapis.com.cn',
            'JP' => 'https://www.zohoapis.jp',
            'AU' => 'https://www.zohoapis.com.au',
            'CA' => 'https://www.zohoapis.ca',
        ];

        return $urls[$this->dataCenter] ?? $urls['US'];
    }
}
