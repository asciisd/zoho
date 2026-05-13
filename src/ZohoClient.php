<?php

namespace Asciisd\Zoho;

use Asciisd\Zoho\Models\ZohoAccount;
use Asciisd\Zoho\Models\ZohoCall;
use Asciisd\Zoho\Models\ZohoContact;
use Asciisd\Zoho\Models\ZohoDeal;
use Asciisd\Zoho\Models\ZohoEvent;
use Asciisd\Zoho\Models\ZohoInvoice;
use Asciisd\Zoho\Models\ZohoLead;
use Asciisd\Zoho\Models\ZohoNote;
use Asciisd\Zoho\Models\ZohoProduct;
use Asciisd\Zoho\Models\ZohoTask;

class ZohoClient
{
    /**
     * Get Contacts module instance.
     */
    public function contacts(): ZohoContact
    {
        return new ZohoContact;
    }

    /**
     * Get Accounts module instance.
     */
    public function accounts(): ZohoAccount
    {
        return new ZohoAccount;
    }

    /**
     * Get Leads module instance.
     */
    public function leads(): ZohoLead
    {
        return new ZohoLead;
    }

    /**
     * Get Deals module instance.
     */
    public function deals(): ZohoDeal
    {
        return new ZohoDeal;
    }

    /**
     * Get Tasks module instance.
     */
    public function tasks(): ZohoTask
    {
        return new ZohoTask;
    }

    /**
     * Get Events module instance.
     */
    public function events(): ZohoEvent
    {
        return new ZohoEvent;
    }

    /**
     * Get Calls module instance.
     */
    public function calls(): ZohoCall
    {
        return new ZohoCall;
    }

    /**
     * Get Notes module instance.
     */
    public function notes(): ZohoNote
    {
        return new ZohoNote;
    }

    /**
     * Get Products module instance.
     */
    public function products(): ZohoProduct
    {
        return new ZohoProduct;
    }

    /**
     * Get Invoices module instance.
     */
    public function invoices(): ZohoInvoice
    {
        return new ZohoInvoice;
    }
}
