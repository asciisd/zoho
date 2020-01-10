<?php

namespace Asciisd\Zoho\Console\Commands;

use Asciisd\Zoho\RestClient;
use Illuminate\Console\Command;
use zcrmsdk\oauth\exception\ZohoOAuthException;

class ZohoSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zoho:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup zoho credentials';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param RestClient $client
     * @return mixed
     * @throws ZohoOAuthException
     */
    public function handle(RestClient $client)
    {
        $grantToken = $this->ask('Please enter your Grant Token');
        if (!$grantToken) {
            $this->comment('The Grant Token is required.');
            return;
        }

        $client->generateAccessToken($grantToken);

        $this->info('Zoho CRM has been set up successfully.');
    }
}
