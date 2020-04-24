<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Client ID
    |--------------------------------------------------------------------------
    |
    | Zoho's Client id for OAuth process
    |
    */
    'client_id' => env('ZOHO_CLIENT_ID', null),

    /*
    |--------------------------------------------------------------------------
    | Client Secret
    |--------------------------------------------------------------------------
    |
    | Zoho's Client secret for OAuth process
    |
    */
    'client_secret' => env('ZOHO_CLIENT_SECRET', null),

    /*
    |--------------------------------------------------------------------------
    | REDIRECT URI
    |--------------------------------------------------------------------------
    |
    | this is were we should handle the OAuth tokens after registering your
    | Zoho client
    |
    */
    'redirect_uri' => env('ZOHO_REDIRECT_URI', null),

    /*
    |--------------------------------------------------------------------------
    | CURRENT USER EMAIL
    |--------------------------------------------------------------------------
    |
    | Zoho's email address that will be used to interact with API
    |
    */
    'current_user_email' => env('ZOHO_CURRENT_USER_EMAIL', null),

    /*
    |--------------------------------------------------------------------------
    | LOG FILE PATH
    |--------------------------------------------------------------------------
    |
    | The SDK stores the log information in a file. you can change the path but
    | just make sure to create an empty file with name `ZCRMClientLibrary.log`
    | then point to the folder contains it in config file here
    |
    | note: In case the path is not specified, the log file will be created
    | inside the project.
    |
    */
    'application_log_file_path' => storage_path('app/zoho/oauth/logs'),

    /*
    |--------------------------------------------------------------------------
    | Token Persistence Path
    |--------------------------------------------------------------------------
    |
    | path of your tokens text file, this path is predefined and used by default,
    | and you are free to change this path, but just make sure that you generate
    | file with name `zcrm_oauthtokens.txt` then point to the folder that containing
    | the file here
    |
    */
    'token_persistence_path' => storage_path('app/zoho/oauth/tokens'),

    /*
    |--------------------------------------------------------------------------
    | ACCOUNTS URL
    |--------------------------------------------------------------------------
    |
    | Default value is set as US domain. This value can be changed based on your
    | domain (EU, CN).
    |
    | Available url's is:-
    | [`accounts.zoho.com`, `accounts.zoho.eu`, `accounts.zoho.com.cn`]
    |
    */
    'accounts_url' => env('ZOHO_ACCOUNTS_URL', 'https://accounts.zoho.com'),

    /*
    |--------------------------------------------------------------------------
    | ZOHO SANDBOX
    |--------------------------------------------------------------------------
    |
    | To make API calls to sandbox account, change the value of this key to true.
    | By default, the value is false
    |
    */
    'sandbox' => env('ZOHO_SANDBOX', false),

    /*
    |--------------------------------------------------------------------------
    | API BASE URL
    |--------------------------------------------------------------------------
    |
    | URL to be used when calling an API. It denotes the domain of the user.
    | This URL may be:
    | - www.zohoapis.com (default)
    | - www.zohoapis.eu
    | - www.zohoapis.com.cn
    |
    */
    'api_base_url' => env('ZOHO_API_BASE_URL', 'www.zohoapis.com'),

    /*
    |--------------------------------------------------------------------------
    | API VERSION
    |--------------------------------------------------------------------------
    |
    | Zoho API version
    |
    */
    'api_version' => env('ZOHO_API_VERSION', 'v2'),

    /*
    |--------------------------------------------------------------------------
    | ACCESS TYPE
    |--------------------------------------------------------------------------
    |
    | must be set only to "offline" as online OAuth client is not supported by the
    | PHP SDK as of now.
    |
    */
    'access_type' => env('ZOHO_ACCESS_TYPE', 'offline'),

    /*
    |--------------------------------------------------------------------------
    | PERSISTENCE HANDLER CLASS
    |--------------------------------------------------------------------------
    |
    | Is the implementation of the ZohoOAuthPersistenceInterface. Refer to this
    | page for more details.
    | https://www.zoho.com/crm/developer/docs/php-sdk/token-persistence.html
    |
    */
    'persistence_handler_class' => env('ZOHO_PERSISTENCE_HANDLER_CLASS', 'ZohoOAuthPersistenceHandler'),

    /*
    |--------------------------------------------------------------------------
    | Zoho Path
    |--------------------------------------------------------------------------
    |
    | This is the base URI path where Zoho's views, such as the callback
    | verification screen, will be available from. You're free to tweak
    | this path according to your preferences and application design.
    |
    */
    'path' => env('ZOHO_PATH', 'zoho'),

    /*
    |--------------------------------------------------------------------------
    | Zoho Path
    |--------------------------------------------------------------------------
    |
    | This is the base URI path where Zoho's views, such as the callback
    | verification screen, will be available from. You're free to tweak
    | this path according to your preferences and application design.
    |
    */
    'oauth_scope' => env('ZOHO_OAUTH_SCOPE', 'aaaserver.profile.READ,ZohoCRM.modules.ALL,ZohoCRM.settings.ALL'),
];
