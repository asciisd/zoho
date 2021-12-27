<?php

namespace Asciisd\Zoho\Http\Controllers;

use Asciisd\Zoho\Facades\ZohoManager;
use Asciisd\Zoho\Http\Requests\ZohoRedirectRequest;
use Illuminate\Routing\Controller;
use zcrmsdk\crm\setup\restclient\ZCRMRestClient;
use zcrmsdk\oauth\ZohoOAuth;
use Zoho;

class ZohoController extends Controller
{
    public function oauth2callback(ZohoRedirectRequest $request)
    {
        $oAuthClient = ZohoManager::generateAccessToken($request->code);

        return 'Zoho CRM has been set up successfully.';
    }
}
