<?php

namespace Asciisd\Zoho\Tests\Unit\Models;

use Asciisd\Zoho\Models\ZohoSync;
use Asciisd\Zoho\Tests\Mocks\TestCustomer;
use Asciisd\Zoho\Tests\TestCase;

class ZohoSyncTest extends TestCase
{
    public function test_it_has_correct_fillable_attributes(): void
    {
        $sync = new ZohoSync;

        $this->assertEquals([
            'zohoable_type',
            'zohoable_id',
            'zoho_module',
            'zoho_record_id',
            'last_synced_at',
        ], $sync->getFillable());
    }

    public function test_last_synced_at_is_cast_to_datetime(): void
    {
        $sync = new ZohoSync;
        $casts = $sync->getCasts();

        $this->assertArrayHasKey('last_synced_at', $casts);
        $this->assertEquals('datetime', $casts['last_synced_at']);
    }

    public function test_scope_for_module(): void
    {
        ZohoSync::create([
            'zohoable_type' => TestCustomer::class,
            'zohoable_id' => 1,
            'zoho_module' => 'Contacts',
            'zoho_record_id' => 'z1',
        ]);

        ZohoSync::create([
            'zohoable_type' => TestCustomer::class,
            'zohoable_id' => 2,
            'zoho_module' => 'Leads',
            'zoho_record_id' => 'z2',
        ]);

        $contacts = ZohoSync::forModule('Contacts')->get();
        $leads = ZohoSync::forModule('Leads')->get();

        $this->assertCount(1, $contacts);
        $this->assertCount(1, $leads);
    }

    public function test_scope_with_zoho_record_id(): void
    {
        ZohoSync::create([
            'zohoable_type' => TestCustomer::class,
            'zohoable_id' => 1,
            'zoho_module' => 'Contacts',
            'zoho_record_id' => 'target-id',
        ]);

        $result = ZohoSync::withZohoRecordId('target-id')->first();

        $this->assertNotNull($result);
        $this->assertEquals('target-id', $result->zoho_record_id);
    }

    public function test_scope_synced(): void
    {
        ZohoSync::create([
            'zohoable_type' => TestCustomer::class,
            'zohoable_id' => 1,
            'zoho_module' => 'Contacts',
            'zoho_record_id' => 'synced-id',
        ]);

        ZohoSync::create([
            'zohoable_type' => TestCustomer::class,
            'zohoable_id' => 2,
            'zoho_module' => 'Contacts',
            'zoho_record_id' => null,
        ]);

        $this->assertCount(1, ZohoSync::synced()->get());
    }

    public function test_scope_not_synced(): void
    {
        ZohoSync::create([
            'zohoable_type' => TestCustomer::class,
            'zohoable_id' => 1,
            'zoho_module' => 'Contacts',
            'zoho_record_id' => 'synced-id',
        ]);

        ZohoSync::create([
            'zohoable_type' => TestCustomer::class,
            'zohoable_id' => 2,
            'zoho_module' => 'Contacts',
            'zoho_record_id' => null,
        ]);

        $this->assertCount(1, ZohoSync::notSynced()->get());
    }

    public function test_zohoable_relationship(): void
    {
        $customer = TestCustomer::withoutZohoSync(fn () => TestCustomer::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]));

        $sync = ZohoSync::create([
            'zohoable_type' => TestCustomer::class,
            'zohoable_id' => $customer->id,
            'zoho_module' => 'Contacts',
            'zoho_record_id' => 'z123',
        ]);

        $this->assertInstanceOf(TestCustomer::class, $sync->zohoable);
        $this->assertEquals($customer->id, $sync->zohoable->id);
    }
}
