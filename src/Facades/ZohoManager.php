<?php

namespace Asciisd\Zoho\Facades;

use Asciisd\Zoho\ZohoModule;
use Asciisd\Zoho\ZohoOrganization;
use Illuminate\Support\Facades\Facade;

/**
 * Class Zoho
 *
 * @method static ZohoModule useModule($module_api_name = 'leads')
 * @method static ZohoOrganization currentOrg()
 * @method static generateAccessToken($grantToken)
 *
 * @package App\Facades
 */
class ZohoManager extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'zoho_manager';
    }
}
