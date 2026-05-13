<?php

namespace Asciisd\Zoho\Tests\Unit\Models;

use Asciisd\Zoho\Models\ZohoCall;
use Asciisd\Zoho\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class ZohoCallTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        app('zoho.storage')->storeTokens([
            'access_token' => 'valid-access-token',
            'refresh_token' => 'valid-refresh-token',
            'expires_in' => 3600,
        ]);
    }

    protected function fakeCallsCreate(): void
    {
        Http::fake([
            '*/crm/v8/Calls' => Http::response([
                'data' => [
                    [
                        'code' => 'SUCCESS',
                        'details' => ['id' => '999'],
                        'status' => 'success',
                    ],
                ],
            ]),
        ]);
    }

    public function test_create_converts_numeric_minutes_to_hms(): void
    {
        $this->fakeCallsCreate();

        ZohoCall::create([
            'Subject' => 'Discovery call',
            'Call_Type' => 'Outbound',
            'Call_Duration' => 30,
            'Who_Id' => 'lead-id-123',
            '$se_module' => 'Leads',
        ]);

        Http::assertSent(fn ($request) => $request->data()['data'][0]['Call_Duration'] === '00:30');
    }

    public function test_create_converts_numeric_string_minutes_to_hm(): void
    {
        $this->fakeCallsCreate();

        ZohoCall::create(['Call_Duration' => '30']);

        Http::assertSent(fn ($request) => $request->data()['data'][0]['Call_Duration'] === '00:30');
    }

    public function test_create_handles_minutes_over_one_hour(): void
    {
        $this->fakeCallsCreate();

        ZohoCall::create(['Call_Duration' => 125]);

        Http::assertSent(fn ($request) => $request->data()['data'][0]['Call_Duration'] === '02:05');
    }

    public function test_create_strips_seconds_from_hms_input(): void
    {
        $this->fakeCallsCreate();

        ZohoCall::create(['Call_Duration' => '01:02:05']);

        Http::assertSent(fn ($request) => $request->data()['data'][0]['Call_Duration'] === '01:02');
    }

    public function test_create_promotes_single_segment_to_hh_mm(): void
    {
        $this->fakeCallsCreate();

        ZohoCall::create(['Call_Duration' => '5:30']);

        Http::assertSent(fn ($request) => $request->data()['data'][0]['Call_Duration'] === '00:05');
    }

    public function test_create_leaves_field_alone_when_not_provided(): void
    {
        $this->fakeCallsCreate();

        ZohoCall::create(['Subject' => 'Quick chat']);

        Http::assertSent(fn ($request) => ! array_key_exists('Call_Duration', $request->data()['data'][0]));
    }

    public function test_update_normalizes_duration(): void
    {
        Http::fake([
            '*/crm/v8/Calls' => Http::response([
                'data' => [
                    ['code' => 'SUCCESS', 'details' => ['id' => '999'], 'status' => 'success'],
                ],
            ]),
        ]);

        ZohoCall::update('999', ['Call_Duration' => 90]);

        Http::assertSent(function ($request) {
            $row = $request->data()['data'][0];

            return $request->method() === 'PUT'
                && $row['id'] === '999'
                && $row['Call_Duration'] === '01:30';
        });
    }

    public function test_upsert_normalizes_duration(): void
    {
        Http::fake([
            '*/crm/v8/Calls/upsert' => Http::response([
                'data' => [['code' => 'SUCCESS', 'details' => ['id' => '999'], 'status' => 'success']],
            ]),
        ]);

        ZohoCall::upsert(['Call_Duration' => 45], ['Subject']);

        Http::assertSent(fn ($request) => $request->data()['data'][0]['Call_Duration'] === '00:45');
    }

    public function test_update_multiple_normalizes_each_record(): void
    {
        Http::fake([
            '*/crm/v8/Calls' => Http::response([
                'data' => [
                    ['code' => 'SUCCESS', 'details' => ['id' => '1']],
                    ['code' => 'SUCCESS', 'details' => ['id' => '2']],
                ],
            ]),
        ]);

        ZohoCall::updateMultiple([
            ['id' => '1', 'Call_Duration' => 30],
            ['id' => '2', 'Call_Duration' => '02:15'],
        ]);

        Http::assertSent(function ($request) {
            $rows = $request->data()['data'];

            return $rows[0]['Call_Duration'] === '00:30'
                && $rows[1]['Call_Duration'] === '00:02';
        });
    }
}
