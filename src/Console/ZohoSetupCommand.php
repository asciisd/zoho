<?php

namespace Asciisd\Zoho\Console;

use Asciisd\Zoho\Auth\OAuthManager;
use Illuminate\Console\Command;

class ZohoSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'zoho:setup';

    /**
     * The console command description.
     */
    protected $description = 'Setup Zoho CRM OAuth authentication';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Zoho CRM Setup Wizard');
        $this->newLine();

        // Check if already configured
        if (empty(config('zoho.client_id')) || empty(config('zoho.client_secret'))) {
            $this->error('❌ Missing Zoho CRM configuration!');
            $this->newLine();
            $this->line('Please add the following to your .env file:');
            $this->line('ZOHO_CLIENT_ID=your_client_id');
            $this->line('ZOHO_CLIENT_SECRET=your_client_secret');
            $this->line('ZOHO_REDIRECT_URI=your_redirect_uri');
            $this->newLine();

            return self::FAILURE;
        }

        $this->info('✓ Configuration found');
        $this->newLine();

        $oauth = app('zoho.oauth');

        // Check if already authenticated
        if ($oauth->isAuthenticated()) {
            $this->warn('⚠️  Already authenticated!');

            if (! $this->confirm('Do you want to re-authenticate?', false)) {
                return self::SUCCESS;
            }
        }

        // Generate authorization URL
        $this->info('📋 Step 1: Authorization');
        $this->newLine();

        $scope = $this->ask(
            'Enter OAuth scope (press Enter for default)',
            'ZohoCRM.modules.ALL,ZohoCRM.settings.ALL,ZohoCRM.users.ALL'
        );

        $authUrl = $oauth->getAuthorizationUrl($scope);

        $this->line('Open this URL in your browser:');
        $this->line($authUrl);
        $this->newLine();

        $usesAppCallback = str_contains(config('zoho.redirect_uri', ''), '/zoho/callback');

        if ($usesAppCallback) {
            return $this->waitForCallback($oauth);
        }

        return $this->manualGrantToken($oauth);
    }

    /**
     * Wait for the OAuth callback route to handle the token exchange.
     */
    protected function waitForCallback(OAuthManager $oauth): int
    {
        $this->info('🔐 Step 2: Waiting for authorization...');
        $this->line('After you authorize in the browser, the callback will save your tokens automatically.');
        $this->newLine();

        $maxAttempts = 60;
        $interval = 2;

        for ($i = 0; $i < $maxAttempts; $i++) {
            if ($oauth->isAuthenticated()) {
                return $this->verifyConnection($oauth);
            }

            sleep($interval);
        }

        $this->error('❌ Timed out waiting for authorization callback.');
        $this->line('Make sure your app is running and the redirect URI is correctly configured.');

        return self::FAILURE;
    }

    /**
     * Prompt for a grant token and exchange it manually (for non-callback redirect URIs).
     */
    protected function manualGrantToken(OAuthManager $oauth): int
    {
        $grantToken = $this->ask('Enter the grant token/code from the redirect URL');

        if (empty($grantToken)) {
            $this->error('❌ Grant token is required!');

            return self::FAILURE;
        }

        $this->info('🔐 Step 2: Generating tokens...');

        try {
            $oauth->generateAccessToken($grantToken);

            return $this->verifyConnection($oauth);
        } catch (\Exception $e) {
            $this->error('❌ Token generation failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Verify the connection works and display token info.
     */
    protected function verifyConnection(OAuthManager $oauth): int
    {
        $storage = app('zoho.storage');
        $tokens = $storage->getTokens();

        $this->newLine();
        $this->info('✓ Tokens generated successfully!');
        $this->newLine();

        $this->table(
            ['Token Type', 'Value'],
            [
                ['Access Token', substr($tokens['access_token'] ?? '', 0, 20).'...'],
                ['Refresh Token', isset($tokens['refresh_token']) ? substr($tokens['refresh_token'], 0, 20).'...' : 'N/A'],
                ['Expires In', ($tokens['expires_in'] ?? 3600).' seconds'],
            ]
        );

        $this->newLine();
        $this->info('💡 Add this to your .env file for future use:');
        $this->line('ZOHO_REFRESH_TOKEN='.($tokens['refresh_token'] ?? 'N/A'));
        $this->newLine();

        $this->info('🧪 Step 3: Testing connection...');

        try {
            $oauth->getValidAccessToken();
            $this->info('✓ Connection test successful!');
            $this->newLine();

            $this->info('🎉 Setup completed successfully!');
            $this->line('You can now use Zoho CRM in your application.');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Connection test failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
