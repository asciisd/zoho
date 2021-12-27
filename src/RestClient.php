<?php

namespace Asciisd\Zoho;

use App\Support\Zoho\TokenStore;
use com\zoho\api\authenticator\OAuthToken;
use com\zoho\api\authenticator\TokenType;
use com\zoho\api\logger\Levels;
use com\zoho\api\logger\Logger;
use com\zoho\crm\api\dc\EUDataCenter;
use com\zoho\crm\api\exception\SDKException;
use com\zoho\crm\api\Initializer;
use com\zoho\crm\api\modules\ModulesOperations;
use com\zoho\crm\api\SDKConfigBuilder;
use com\zoho\crm\api\UserSignature;
use Illuminate\Support\Facades\Storage;
use JetBrains\PhpStorm\Pure;

/**
 * Class RestClient
 *
 *
 *
 * @package Asciisd\Zoho
 */
class RestClient
{
    protected ?Initializer $initializer;

    public function __construct(?Initializer $initializer)
    {
        $this->initializer = $initializer;
    }

    public function generateAccessToken($grantToken)
    {
        try {
            $zohoOptions = collect(Zoho::zohoOptions());
            $logger = Logger::getInstance(Levels::ALL, $zohoOptions->get('applicationLogFilePath') . '/zoho-api.log');
            $user = new UserSignature($zohoOptions->get('currentUserEmail'));
            $token = new OAuthToken(
                $zohoOptions->get('client_id'),
                $zohoOptions->get('client_secret'),
                $grantToken,
                TokenType::GRANT,
                $zohoOptions->get('redirect_uri')
            );
            $tokenStore = new TokenStore('zoho/oauth/tokens');
            $sdkConfig = (new SDKConfigBuilder())->setAutoRefreshFields(true)->setPickListValidation(false)->setSSLVerification(true)->connectionTimeout(2)->timeout(2)->build();
            $environment = EUDataCenter::PRODUCTION();
            Initializer::initialize($user, $environment, $token, $tokenStore, $sdkConfig, Storage::disk('local')->path('/zoho/resources/'), $logger);
            $token->generateAccessToken($user, $tokenStore);
        } catch (SDKException $exception) {
            exit($exception->getMessage());
        }
    }

    public function getAllModules()
    {
        $modules = new ModulesOperations();
        return $modules->getModules();
    }

    public function useModule($module_api_name = 'leads')
    {
        return new ZohoModule($this->initializer, $module_api_name);
    }

    #[Pure]
    public function currentOrg(): ZohoOrganization
    {
        return new ZohoOrganization($this->zohoOptions);
    }
}
