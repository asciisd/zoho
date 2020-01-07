<?php


namespace Asciisd\Zoho;


class Zoho
{
    /**
     * get all modules from your crm
     *
     * @return array
     */
    public static function getAllModules()
    {
        return RestClient::getAllModules();
    }
}
