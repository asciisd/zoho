<?php

namespace Asciisd\Zoho\Tests\Unit;

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
use Asciisd\Zoho\Tests\TestCase;
use Asciisd\Zoho\ZohoClient;

class ZohoClientTest extends TestCase
{
    protected ZohoClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new ZohoClient;
    }

    public function test_contacts_returns_zoho_contact(): void
    {
        $this->assertInstanceOf(ZohoContact::class, $this->client->contacts());
    }

    public function test_accounts_returns_zoho_account(): void
    {
        $this->assertInstanceOf(ZohoAccount::class, $this->client->accounts());
    }

    public function test_leads_returns_zoho_lead(): void
    {
        $this->assertInstanceOf(ZohoLead::class, $this->client->leads());
    }

    public function test_deals_returns_zoho_deal(): void
    {
        $this->assertInstanceOf(ZohoDeal::class, $this->client->deals());
    }

    public function test_tasks_returns_zoho_task(): void
    {
        $this->assertInstanceOf(ZohoTask::class, $this->client->tasks());
    }

    public function test_events_returns_zoho_event(): void
    {
        $this->assertInstanceOf(ZohoEvent::class, $this->client->events());
    }

    public function test_calls_returns_zoho_call(): void
    {
        $this->assertInstanceOf(ZohoCall::class, $this->client->calls());
    }

    public function test_notes_returns_zoho_note(): void
    {
        $this->assertInstanceOf(ZohoNote::class, $this->client->notes());
    }

    public function test_products_returns_zoho_product(): void
    {
        $this->assertInstanceOf(ZohoProduct::class, $this->client->products());
    }

    public function test_invoices_returns_zoho_invoice(): void
    {
        $this->assertInstanceOf(ZohoInvoice::class, $this->client->invoices());
    }
}
