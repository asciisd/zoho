<?php


namespace Asciisd\Zoho\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ZohoInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zoho:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Zoho resources';

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
        $this->comment('Generate Zoho OAuth files ...');
        Storage::disk('local')->put('zoho/oauth/logs/ZCRMClientLibrary.log', '');
        Storage::disk('local')->put('zoho/oauth/tokens/zcrm_oauthtokens.txt', '');

//        $this->comment('Publishing Zoho Configuration ...');
//        $this->callSilent('vendor:publish', ['--tag' => 'zoho-config']);

        $this->info('Zoho scaffolding installed successfully.');
    }
}
