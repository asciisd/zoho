<?php

namespace Asciisd\Zoho\Console\Commands;

use Asciisd\Zoho\Facades\Zoho;
use Illuminate\Console\Command;

class ZohoSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zoho:grant {token: generate grant token from https://accounts.zoho.com/developerconsole}';

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
     * @return mixed
     */
    public function handle()
    {
        $grantToken = $this->argument('token');

        if (!$grantToken) {
            $this->error('The Grant Token is required.');
            return;
        }

        Zoho::generateAccessToken($grantToken);

        $this->info('Zoho CRM has been set up successfully.');
    }
}
