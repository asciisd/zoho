<?php

namespace Asciisd\Zoho\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Asciisd\Zoho\Models\ZohoContact contacts()
 * @method static \Asciisd\Zoho\Models\ZohoAccount accounts()
 * @method static \Asciisd\Zoho\Models\ZohoLead leads()
 * @method static \Asciisd\Zoho\Models\ZohoDeal deals()
 * @method static \Asciisd\Zoho\Models\ZohoTask tasks()
 * @method static \Asciisd\Zoho\Models\ZohoEvent events()
 * @method static \Asciisd\Zoho\Models\ZohoCall calls()
 * @method static \Asciisd\Zoho\Models\ZohoNote notes()
 * @method static \Asciisd\Zoho\Models\ZohoProduct products()
 * @method static \Asciisd\Zoho\Models\ZohoInvoice invoices()
 *
 * @see \Asciisd\Zoho\ZohoClient
 */
class Zoho extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'zoho';
    }
}
