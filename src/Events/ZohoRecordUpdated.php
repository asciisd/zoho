<?php

namespace Asciisd\Zoho\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ZohoRecordUpdated
{
    use Dispatchable, SerializesModels;

    public array $record;

    public string $module;

    /**
     * Create a new event instance.
     */
    public function __construct(array $record, string $module)
    {
        $this->record = $record;
        $this->module = $module;
    }

    /**
     * Get the record data.
     */
    public function getData(): array
    {
        return $this->record;
    }

    /**
     * Get the record ID.
     */
    public function getRecordId(): ?string
    {
        return $this->record['id'] ?? null;
    }
}
