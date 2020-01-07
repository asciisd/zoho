<?php

namespace Asciisd\Zoho;

use zcrmsdk\crm\setup\restclient\ZCRMRestClient;
use zcrmsdk\oauth\ZohoOAuth;

class RestClient
{
    protected $configuration;

    public function __construct()
    {
        $this->configuration = [
            'client_id' => config('zoho.client_id'),
            'client_secret' => config('zoho.client_secret'),
            'redirect_uri' => config('zoho.redirect_uri'),
            'currentUserEmail' => config('zoho.current_user_email'),
            'applicationLogFilePath' => config('zoho.application_log_file_path'),
            'token_persistence_path' => config('zoho.token_persistence_path'),
            'accounts_url' => config('zoho.accounts_url'),
            'sandbox' => config('zoho.sandbox'),
            'apiBaseUrl' => config('zoho.api_base_url'),
            'apiVersion' => config('zoho.api_version'),
            'access_type' => config('zoho.access_type'),
            'persistence_handler_class' => config('zoho.persistence_handler_class'),
        ];

        ZCRMRestClient::initialize($this->configuration);
    }

    public function generateAccessToken($grantToken)
    {
        $oAuthClient = ZohoOAuth::getClientInstance();
        return $oAuthClient->generateAccessToken($grantToken);
    }

    public static function getOrganizationDetails()
    {
        $rest = ZCRMRestClient::getInstance();
        //to get the organization in form of ZCRMOrganization instance
        return $rest->getOrganizationDetails()->getData();

    }

    public static function getAllModules()
    {
        $configuration = [
            'client_id' => config('zoho.client_id'),
            'client_secret' => config('zoho.client_secret'),
            'redirect_uri' => config('zoho.redirect_uri'),
            'currentUserEmail' => config('zoho.current_user_email'),
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
        $rest = ZCRMRestClient::getInstance();
        //to get the the modules in form of ZCRMModule instances array
        return $rest->getAllModules()->getData();
    }
}
