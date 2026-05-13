<?php

namespace Asciisd\Zoho\Console;

use Illuminate\Console\Command;

class ZohoRefreshTokenCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'zoho:token:refresh
                            {--clear-cache : Clear cached tokens}';

    /**
     * The console command description.
     */
    protected $description = 'Refresh Zoho CRM access token';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('clear-cache')) {
            $this->clearCache();
        }

        $this->info('🔄 Refreshing access token...');
        $this->newLine();

        try {
            $oauth = app('zoho.oauth');
            $tokens = $oauth->refreshAccessToken();

            $this->info('✓ Token refreshed successfully!');
            $this->newLine();

            $this->table(
                ['Property', 'Value'],
                [
                    ['Access Token', substr($tokens['access_token'], 0, 30).'...'],
                    ['Token Type', $tokens['token_type'] ?? 'Bearer'],
                    ['Expires In', ($tokens['expires_in'] ?? 3600).' seconds'],
                ]
            );

            $this->newLine();
            $this->info('💡 Token has been stored and cached');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Token refresh failed: '.$e->getMessage());
            $this->newLine();
            $this->line('Make sure you have a valid refresh token configured.');
            $this->line('Run "php artisan zoho:setup" if you need to authenticate again.');

            return self::FAILURE;
        }
    }

    /**
     * Clear cached tokens.
     */
    protected function clearCache(): void
    {
        $this->line('🗑️  Clearing cached tokens...');

        $storage = app('zoho.storage');
        $storage->deleteTokens();

        $this->info('✓ Cache cleared');
        $this->newLine();
    }
}
