<?php

namespace Asciisd\Zoho\Tests\Integration;

use Asciisd\Zoho\RestClient;
use Asciisd\Zoho\Tests\TestCase;
use zcrmsdk\crm\setup\restclient\ZCRMRestClient;

abstract class IntegrationTestCase extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $configuration = [
            'client_id' => getenv('ZOHO_CLIENT_ID'),
            'client_secret' => getenv('ZOHO_CLIENT_SECRET'),
            'redirect_uri' => getenv('ZOHO_REDIRECT_URI'),
            'currentUserEmail' => getenv('ZOHO_CURRENT_USER_EMAIL'),
            'applicationLogFilePath' => './tests/Fixture/Storage/oauth/logs',
            'token_persistence_path' => './tests/Fixture/Storage/oauth/tokens',
            'accounts_url' => 'https://accounts.zoho.com',
            'sandbox' => true,
            'apiBaseUrl' => 'www.zohoapis.com',
            'apiVersion' => 'v2',
            'access_type' => 'offline',
            'persistence_handler_class' => 'ZohoOAuthPersistenceHandler',
        ];

        ZCRMRestClient::initialize($configuration);
    }

    protected function getClient(): RestClient
    {
        return new RestClient(ZCRMRestClient::getInstance());
    }
}
