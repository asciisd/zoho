<?php

namespace Asciisd\Zoho;

use zcrmsdk\crm\setup\restclient\ZCRMRestClient;
use zcrmsdk\oauth\ZohoOAuth;

class RestClient
{
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
        $rest = ZCRMRestClient::getInstance();
        //to get the the modules in form of ZCRMModule instances array
        return $rest->getAllModules()->getData();
    }
}
