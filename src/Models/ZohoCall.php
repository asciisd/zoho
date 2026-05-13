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

        // Zoho CRM expects Call_Duration in "mm:ss" format (minutes:seconds).
        // "HH:mm:ss" input → convert hours to total minutes, keep seconds.
        if (is_string($value) && preg_match('/^(\d+):(\d{1,2}):(\d{1,2})$/', $value, $m)) {
            $totalMinutes = (int) $m[1] * 60 + (int) $m[2];

            return sprintf('%02d:%02d', $totalMinutes, (int) $m[3]);
        }

        // "mm:ss" input → normalize padding.
        if (is_string($value) && preg_match('/^(\d+):(\d{1,2})$/', $value, $m)) {
            return sprintf('%02d:%02d', (int) $m[1], (int) $m[2]);
        }

        // Bare number (int or numeric string) → interpret as minutes, zero seconds.
        if (is_numeric($value)) {
            $minutes = max(0, (int) $value);

            return sprintf('%02d:00', $minutes);
        }

        return $value;
    }
}
