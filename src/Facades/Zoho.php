<?php

namespace Asciisd\Zoho\Facades;

use Asciisd\Zoho\Traits\ZohoModules;
use Asciisd\Zoho\Traits\ZohoOrganization;
use Illuminate\Support\Facades\Facade;

/**
 * Class Zoho
 *
 * @method static ZohoModules useModule($module_api_name = 'leads')
 * @method static ZohoOrganization currentOrg()
 * @method static generateAccessToken($grantToken)
 *
 * @package App\Facades
 */
class Zoho extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'zoho';
    }
}
