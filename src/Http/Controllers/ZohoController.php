<?php

namespace Asciisd\Zoho\Http\Controllers;

use Asciisd\Zoho\Http\Requests\ZohoRedirectRequest;
use Illuminate\Routing\Controller;
use zcrmsdk\oauth\ZohoOAuth;

class ZohoController extends Controller
{
    public function oauth2callback(ZohoRedirectRequest $request)
    {
        $oAuthClient = ZohoOAuth::getClientInstance();
        $$oAuthClient->generateAccessToken($request->code);

        return 'Zoho CRM has been set up successfully.';
    }
}
