<?php

namespace Asciisd\Zoho\Console;

use Illuminate\Console\Command;

class ZohoAuthCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'zoho:auth
                            {action=status : Action to perform (status, url, refresh, revoke)}';

    /**
     * The console command description.
     */
    protected $description = 'Manage Zoho CRM authentication';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'status' => $this->showStatus(),
            'url' => $this->showAuthUrl(),
            'refresh' => $this->refreshToken(),
            'revoke' => $this->revokeToken(),
            default => (function () use ($action) {
                $this->error("Unknown action: {$action}");

                return self::FAILURE;
            })(),
        };
    }

    /**
     * Show authentication status.
     */
    protected function showStatus(): int
    {
        $oauth = app('zoho.oauth');
        $storage = app('zoho.storage');

        $this->info('🔐 Zoho CRM Authentication Status');
        $this->newLine();

        if (! $oauth->isAuthenticated()) {
            $this->warn('❌ Not authenticated');
            $this->line('Run "php artisan zoho:setup" to authenticate.');

            return self::FAILURE;
        }

        $this->info('✓ Authenticated');
        $this->newLine();

        $tokens = $storage->getTokens();

        if ($tokens) {
            $this->table(
                ['Property', 'Value'],
                [
                    ['Access Token', substr($tokens['access_token'] ?? '', 0, 30).'...'],
                    ['Refresh Token', isset($tokens['refresh_token']) ? substr($tokens['refresh_token'], 0, 30).'...' : 'N/A'],
                    ['Token Type', $tokens['token_type'] ?? 'Bearer'],
                    ['Data Center', $tokens['data_center'] ?? config('zoho.data_center')],
                    ['Environment', $tokens['environment'] ?? config('zoho.environment')],
                    ['Expires At', isset($tokens['expires_at']) ? $tokens['expires_at'] : 'N/A'],
                ]
            );
        }

        return self::SUCCESS;
    }

    /**
     * Show authorization URL.
     */
    protected function showAuthUrl(): int
    {
        $oauth = app('zoho.oauth');

        $scope = $this->ask(
            'Enter OAuth scope (press Enter for default)',
            'ZohoCRM.modules.ALL,ZohoCRM.settings.ALL,ZohoCRM.users.ALL'
        );

        $authUrl = $oauth->getAuthorizationUrl($scope);

        $this->newLine();
        $this->info('📋 Authorization URL:');
        $this->line($authUrl);
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Refresh access token.
     */
    protected function refreshToken(): int
    {
        $this->info('🔄 Refreshing access token...');

        try {
            $oauth = app('zoho.oauth');
            $tokens = $oauth->refreshAccessToken();

            $this->newLine();
            $this->info('✓ Token refreshed successfully!');
            $this->newLine();

            $this->table(
                ['Property', 'Value'],
                [
                    ['Access Token', substr($tokens['access_token'], 0, 30).'...'],
                    ['Expires In', ($tokens['expires_in'] ?? 3600).' seconds'],
                ]
            );

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Token refresh failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Revoke access token.
     */
    protected function revokeToken(): int
    {
        if (! $this->confirm('Are you sure you want to revoke the access token?')) {
            return self::SUCCESS;
        }

        $this->info('🗑️  Revoking access token...');

        try {
            $oauth = app('zoho.oauth');
            $success = $oauth->revokeToken();

            if ($success) {
                $this->newLine();
                $this->info('✓ Token revoked successfully!');
                $this->line('Run "php artisan zoho:setup" to authenticate again.');
            } else {
                $this->warn('⚠️  Token revocation may have failed');
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Token revocation failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
