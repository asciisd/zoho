<?php

namespace Asciisd\Zoho;

class Zoho
{
    /**
     * The Zoho library version.
     *
     * @var string
     */
    const VERSION = '1.2.7';

    /**
     * Indicates if Zoho migrations will be run.
     *
     * @var bool
     */
    public static $runsMigrations = true;

    /**
     * Indicates if Zoho routes will be registered.
     *
     * @var bool
     */
    public static $registersRoutes = true;

    /**
     * Get the default Zoho API options.
     *
     * @param array $options
     * @return array
     */
    public static function zohoOptions(array $options = [])
    {
        return array_merge([
            'client_id' => config('zoho.client_id'),
            'client_secret' => config('zoho.client_secret'),
            'redirect_uri' => config('zoho.redirect_uri'),
            'currentUserEmail' => config('zoho.current_user_email'),
            'applicationLogFilePath' => config('zoho.application_log_file_path'),
            'sandbox' => config('zoho.sandbox'),
            'apiBaseUrl' => config('zoho.api_base_url'),
            'apiVersion' => config('zoho.api_version'),
            'access_type' => config('zoho.access_type'),
            'accounts_url' => config('zoho.accounts_url'),
//            'persistence_handler_class' => config('zoho.persistence_handler_class'),
//            'persistence_handler_class_name' => config('zoho.persistence_handler_class_name'),
            'token_persistence_path' => config('zoho.token_persistence_path'),
//            'fileUploadUrl' => config('zoho.file_upload_url'),
        ], $options);
    }

    /**
     * Configure Zoho to not register its migrations.
     *
     * @return static
     */
    public static function ignoreMigrations()
    {
        static::$runsMigrations = false;

        return new static;
    }

    /**
     * Configure Zoho to not register its routes.
     *
     * @return static
     */
    public static function ignoreRoutes()
    {
        static::$registersRoutes = false;

        return new static;
    }
}
