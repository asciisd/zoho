<?php

namespace Asciisd\Zoho\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ZohoWebhookReceived
{
    use Dispatchable, SerializesModels;

    public array $payload;

    public string $module;

    public string $event;

    /**
     * Create a new event instance.
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
        $this->module = $payload['module'] ?? 'Unknown';
        $this->event = $payload['event'] ?? 'Unknown';
    }

    /**
     * Get the payload data.
     */
    public function getData(): array
    {
        return $this->payload;
    }

    /**
     * Get the record data.
     */
    public function getRecord(): ?array
    {
        return $this->payload['data'] ?? null;
    }

    /**
     * Get the record ID.
     */
    public function getRecordId(): ?string
    {
        $record = $this->getRecord();

        return $record['id'] ?? null;
    }
}
