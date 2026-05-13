<?php

namespace Asciisd\Zoho\Tests\Unit\Events;

use Asciisd\Zoho\Events\ZohoRecordCreated;
use Asciisd\Zoho\Events\ZohoRecordDeleted;
use Asciisd\Zoho\Events\ZohoRecordUpdated;
use Asciisd\Zoho\Events\ZohoWebhookReceived;
use Asciisd\Zoho\Tests\TestCase;

class EventsTest extends TestCase
{
    // ZohoWebhookReceived
    public function test_webhook_received_stores_payload(): void
    {
        $payload = ['event' => 'create', 'module' => 'Contacts', 'data' => ['id' => '123']];
        $event = new ZohoWebhookReceived($payload);

        $this->assertEquals($payload, $event->payload);
        $this->assertEquals('Contacts', $event->module);
        $this->assertEquals('create', $event->event);
    }

    public function test_webhook_received_defaults_unknown_module(): void
    {
        $event = new ZohoWebhookReceived([]);

        $this->assertEquals('Unknown', $event->module);
        $this->assertEquals('Unknown', $event->event);
    }

    public function test_webhook_received_get_data(): void
    {
        $payload = ['event' => 'create', 'module' => 'Contacts'];
        $event = new ZohoWebhookReceived($payload);

        $this->assertEquals($payload, $event->getData());
    }

    public function test_webhook_received_get_record(): void
    {
        $event = new ZohoWebhookReceived([
            'data' => ['id' => '456', 'Last_Name' => 'Doe'],
        ]);

        $this->assertEquals(['id' => '456', 'Last_Name' => 'Doe'], $event->getRecord());
    }

    public function test_webhook_received_get_record_returns_null(): void
    {
        $event = new ZohoWebhookReceived([]);

        $this->assertNull($event->getRecord());
    }

    public function test_webhook_received_get_record_id(): void
    {
        $event = new ZohoWebhookReceived([
            'data' => ['id' => '789'],
        ]);

        $this->assertEquals('789', $event->getRecordId());
    }

    public function test_webhook_received_get_record_id_null(): void
    {
        $event = new ZohoWebhookReceived([]);

        $this->assertNull($event->getRecordId());
    }

    // ZohoRecordCreated
    public function test_record_created_stores_data(): void
    {
        $record = ['id' => '123', 'Last_Name' => 'Doe'];
        $event = new ZohoRecordCreated($record, 'Contacts');

        $this->assertEquals($record, $event->record);
        $this->assertEquals('Contacts', $event->module);
    }

    public function test_record_created_get_data(): void
    {
        $record = ['id' => '123'];
        $event = new ZohoRecordCreated($record, 'Contacts');

        $this->assertEquals($record, $event->getData());
    }

    public function test_record_created_get_record_id(): void
    {
        $event = new ZohoRecordCreated(['id' => '456'], 'Contacts');

        $this->assertEquals('456', $event->getRecordId());
    }

    public function test_record_created_get_record_id_null(): void
    {
        $event = new ZohoRecordCreated([], 'Contacts');

        $this->assertNull($event->getRecordId());
    }

    // ZohoRecordUpdated
    public function test_record_updated_stores_data(): void
    {
        $record = ['id' => '123', 'Phone' => '+1234567890'];
        $event = new ZohoRecordUpdated($record, 'Contacts');

        $this->assertEquals($record, $event->record);
        $this->assertEquals('Contacts', $event->module);
    }

    // ZohoRecordDeleted
    public function test_record_deleted_stores_data(): void
    {
        $record = ['id' => '123'];
        $event = new ZohoRecordDeleted($record, 'Contacts');

        $this->assertEquals($record, $event->record);
        $this->assertEquals('Contacts', $event->module);
    }
}
