<?php

namespace Asciisd\Zoho\Http\Controllers;

use Zoho;
use zcrmsdk\oauth\ZohoOAuth;
use Illuminate\Routing\Controller;
use zcrmsdk\crm\setup\restclient\ZCRMRestClient;
use Asciisd\Zoho\Http\Requests\ZohoRedirectRequest;

class ZohoController extends Controller
{
    public function oauth2callback(ZohoRedirectRequest $request)
    {
        ZCRMRestClient::initialize(Zoho::zohoOptions());
        $oAuthClient = ZohoOAuth::getClientInstance();
        $oAuthClient->generateAccessToken($request->code);

        return 'Zoho CRM has been set up successfully.';
    }
}
