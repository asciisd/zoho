<?php

namespace Asciisd\Zoho\Models;

class ZohoCall extends ZohoModel
{
    protected const MODULE_API_NAME = 'Calls';

    public static function create(array $data): array
    {
        return parent::create(static::normalizeCallData($data));
    }

    public static function update(string $id, array $data): array
    {
        return parent::update($id, static::normalizeCallData($data));
    }

    public static function upsert(array $data, array $duplicateCheckFields = []): array
    {
        return parent::upsert(static::normalizeCallData($data), $duplicateCheckFields);
    }

    public static function updateMultiple(array $records): array
    {
        return parent::updateMultiple(array_map(
            fn (array $record) => static::normalizeCallData($record),
            $records
        ));
    }

    protected static function normalizeCallData(array $data): array
    {
        if (array_key_exists('Call_Duration', $data)) {
            $data['Call_Duration'] = static::formatCallDuration($data['Call_Duration']);
        }

        return $data;
    }

    protected static function formatCallDuration(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        // Zoho CRM API v8 expects Call_Duration in "HH:mm" format (no seconds).
        if (is_string($value) && preg_match('/^(\d{1,2}):(\d{1,2})(?::(\d{1,2}))?$/', $value, $m)) {
            if (isset($m[3])) {
                return sprintf('%02d:%02d', (int) $m[1], (int) $m[2]);
            }

            return sprintf('00:%02d', (int) $m[1]);
        }

        if (is_numeric($value)) {
            $minutes = max(0, (int) $value);

            return sprintf(
                '%02d:%02d',
                intdiv($minutes, 60),
                $minutes % 60
            );
        }

        return $value;
    }
}
