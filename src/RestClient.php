<?php


namespace Asciisd\Zoho;


use Asciisd\Zoho\Traits\ZohoModules;
use Asciisd\Zoho\Traits\ZohoOrganization;
use zcrmsdk\crm\setup\restclient\ZCRMRestClient;
use zcrmsdk\oauth\exception\ZohoOAuthException;
use zcrmsdk\oauth\ZohoOAuth;

class RestClient
{
    use ZohoModules, ZohoOrganization;

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
