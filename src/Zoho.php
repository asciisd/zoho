<?php


namespace Asciisd\Zoho;


class Zoho
{
    /**
     * get all modules from your crm
     *
     * @param RestClient $client
     * @return array
     */
    public static function getAllModules(RestClient $client)
    {
        return $client->getAllModules();
    }
}
