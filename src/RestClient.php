<?php

namespace Asciisd\Zoho;

use zcrmsdk\crm\setup\restclient\ZCRMRestClient;
use zcrmsdk\oauth\exception\ZohoOAuthException;
use zcrmsdk\oauth\ZohoOAuth;

/**
 * Class RestClient
 *
 *
 *
 * @package Asciisd\Zoho
 */
class RestClient
{
    protected $rest;

    /**
     * RestClient constructor.
     *
     * @param ZCRMRestClient $rest
     */
    public function __construct($rest)
    {
        $this->rest = $rest;
    }

    public function getAllModules()
    {
        return $this->rest->getAllModules()->getData();
    }

    public function useModule($module_api_name = 'leads')
    {
        return new ZohoModule($this->rest, $module_api_name);
    }

    public function currentOrg()
    {
        return new ZohoOrganization($this->rest);
    }

    /**
     * @param $grantToken
     * @return mixed
     * @throws ZohoOAuthException
     */
    public static function generateAccessToken($grantToken)
    {
        $oAuthClient = ZohoOAuth::getClientInstance();
        return $oAuthClient->generateAccessToken($grantToken);
    }
}
