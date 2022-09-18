<?php


namespace Asciisd\Zoho\Console\Commands;


use Illuminate\Console\Command;

class ZohoAuthentication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zoho:authentication';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate OAuth url to complete the Authentication process.';

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
        $client_id     = config('zoho.client_id');
        $client_domain = config('app.url').'/zoho/oauth2callback';
        $scope         = config('zoho.oauth_scope');
        $prompt        = 'consent';
        $response_type = 'code';
        $access_type   = config('zoho.access_type');

        $redirect_url = "https://accounts.zoho.com/oauth/v2/auth?scope={$scope}&prompt={$prompt}&client_id={$client_id}&response_type={$response_type}&access_type={$access_type}&redirect_uri={$client_domain}";
//{{accounts_url}}/oauth/v2/token?grant_type=authorization_code&client_id={{client_id}}&client_secret={{client_secret}}&redirect_uri={{redirect_uri}}&code=1000.2ba87e1464afc3ba9042acb90f4c196d.971662a877b2aa7470642ce244c41055
        $this->info('Copy the following url, past on browser and hit return.');
        $this->line($redirect_url);
    }
}
