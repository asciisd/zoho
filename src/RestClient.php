<?php

namespace Asciisd\Zoho;

use zcrmsdk\crm\setup\restclient\ZCRMRestClient;
use zcrmsdk\oauth\ZohoOAuth;

class RestClient
{

    public function __construct()
    {
        $configuration = [
            'client_id' => config('zoho.client_id'),
            'client_secret' => config('zoho.client_secret'),
            'redirect_uri' => config('zoho.redirect_uri'),
            'currentUserEmail' => 'it@caveo.com.kw',
            'applicationLogFilePath' => config('zoho.application_log_file_path'),
            'token_persistence_path' => config('zoho.token_persistence_path'),
            'accounts_url' => config('zoho.accounts_url'),
            'sandbox' => config('zoho.sandbox'),
            'apiBaseUrl' => config('zoho.api_base_url'),
            'apiVersion' => config('zoho.api_version'),
            'access_type' => config('zoho.access_type'),
            'persistence_handler_class' => config('zoho.persistence_handler_class'),
        ];

        ZCRMRestClient::initialize($configuration);
    }

    public function generateAccessToken($grantToken)
    {
        $oAuthClient = ZohoOAuth::getClientInstance();
        return $oAuthClient->generateAccessToken($grantToken);
    }

    public function getOrganizationDetails()
    {
        $rest = ZCRMRestClient::getInstance();
        //to get the organization in form of ZCRMOrganization instance
        return $rest->getOrganizationDetails()->getData();

    }

    public function getAllModules()
    {
        $rest = ZCRMRestClient::getInstance();
        //to get the the modules in form of ZCRMModule instances array
        return $rest->getAllModules()->getData();
    }
}
