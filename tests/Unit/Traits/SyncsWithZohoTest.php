<?php

namespace Asciisd\Zoho\Tests\Unit\Traits;

use Asciisd\Zoho\Jobs\SyncModelToZoho;
use Asciisd\Zoho\Models\ZohoSync;
use Asciisd\Zoho\Tests\Mocks\TestCustomer;
use Asciisd\Zoho\Tests\Mocks\TestCustomerNoMapping;
use Asciisd\Zoho\Tests\Mocks\TestCustomModuleCustomer;
use Asciisd\Zoho\Tests\Mocks\ZohoPropertyListing;
use Asciisd\Zoho\Tests\TestCase;
use Illuminate\Support\Facades\Queue;

class SyncsWithZohoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    public function test_creating_model_dispatches_create_sync_job(): void
    {
        TestCustomer::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        Queue::assertPushed(SyncModelToZoho::class, function ($job) {
            return $job->operation === 'create';
        });
    }

    public function test_updating_model_dispatches_update_sync_job(): void
    {
        $customer = TestCustomer::withoutZohoSync(fn () => TestCustomer::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]));

        $customer->update(['name' => 'Jane Smith']);

        Queue::assertPushed(SyncModelToZoho::class, function ($job) {
            return $job->operation === 'update';
        });
    }

    public function test_deleting_model_dispatches_delete_sync_job(): void
    {
        $customer = TestCustomer::withoutZohoSync(fn () => TestCustomer::create([
            'name' => 'Delete Me',
            'email' => 'delete@example.com',
        ]));

        $customer->delete();

        Queue::assertPushed(SyncModelToZoho::class, function ($job) {
            return $job->operation === 'delete';
        });
    }

    public function test_without_zoho_sync_prevents_dispatching(): void
    {
        TestCustomer::withoutZohoSync(function () {
            TestCustomer::create([
                'name' => 'Silent Create',
                'email' => 'silent@example.com',
            ]);
        });

        Queue::assertNotPushed(SyncModelToZoho::class);
    }

    public function test_without_zoho_sync_restores_state_after_callback(): void
    {
        $this->assertFalse(TestCustomer::isZohoSyncDisabled());

        TestCustomer::withoutZohoSync(function () {
            $this->assertTrue(TestCustomer::isZohoSyncDisabled());
        });

        $this->assertFalse(TestCustomer::isZohoSyncDisabled());
    }

    public function test_without_zoho_sync_restores_state_even_on_exception(): void
    {
        try {
            TestCustomer::withoutZohoSync(function () {
                throw new \RuntimeException('test');
            });
        } catch (\RuntimeException) {
        }

        $this->assertFalse(TestCustomer::isZohoSyncDisabled());
    }

    public function test_sync_disabled_when_config_disabled(): void
    {
        config(['zoho.sync.enabled' => false]);

        TestCustomer::create([
            'name' => 'Disabled Sync',
            'email' => 'disabled@example.com',
        ]);

        Queue::assertNotPushed(SyncModelToZoho::class);
    }

    public function test_get_zoho_module_returns_correct_module(): void
    {
        $customer = new TestCustomer;

        $this->assertEquals('Contacts', $customer->getZohoModule());
    }

    public function test_get_zoho_field_mapping_returns_mapping(): void
    {
        $customer = new TestCustomer;

        $mapping = $customer->getZohoFieldMapping();

        $this->assertEquals([
            'name' => 'Last_Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'company' => 'Company',
        ], $mapping);
    }

    public function test_get_zoho_field_mapping_returns_empty_when_not_defined(): void
    {
        $customer = new TestCustomerNoMapping;

        $this->assertEmpty($customer->getZohoFieldMapping());
    }

    public function test_transform_to_zoho_data_with_mapping(): void
    {
        $customer = TestCustomer::withoutZohoSync(fn () => TestCustomer::create([
            'name' => 'Mapped User',
            'email' => 'mapped@example.com',
            'phone' => '+1234567890',
        ]));

        $data = $customer->transformToZohoData();

        $this->assertEquals('Mapped User', $data['Last_Name']);
        $this->assertEquals('mapped@example.com', $data['Email']);
        $this->assertEquals('+1234567890', $data['Phone']);
    }

    public function test_transform_to_zoho_data_without_mapping(): void
    {
        $customer = TestCustomerNoMapping::withoutZohoSync(fn () => TestCustomerNoMapping::create([
            'name' => 'Unmapped User',
            'email' => 'unmapped@example.com',
        ]));

        $data = $customer->transformToZohoData();

        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('email', $data);
    }

    public function test_get_excluded_zoho_fields(): void
    {
        $customer = new TestCustomer;

        $excluded = $customer->getExcludedZohoFields();

        $this->assertContains('password', $excluded);
        $this->assertContains('remember_token', $excluded);
    }

    public function test_zoho_sync_relationship(): void
    {
        $customer = TestCustomer::withoutZohoSync(fn () => TestCustomer::create([
            'name' => 'Relation Test',
            'email' => 'relation@example.com',
        ]));

        ZohoSync::create([
            'zohoable_type' => TestCustomer::class,
            'zohoable_id' => $customer->id,
            'zoho_module' => 'Contacts',
            'zoho_record_id' => 'relation-zoho-id',
        ]);

        $this->assertNotNull($customer->zohoSync);
        $this->assertEquals('relation-zoho-id', $customer->zohoSync->zoho_record_id);
    }

    public function test_get_zoho_record_id(): void
    {
        $customer = TestCustomer::withoutZohoSync(fn () => TestCustomer::create([
            'name' => 'Record ID Test',
            'email' => 'recordid@example.com',
        ]));

        $this->assertNull($customer->getZohoRecordId());

        ZohoSync::create([
            'zohoable_type' => TestCustomer::class,
            'zohoable_id' => $customer->id,
            'zoho_module' => 'Contacts',
            'zoho_record_id' => 'record-id-123',
        ]);

        $customer = $customer->fresh();
        $this->assertEquals('record-id-123', $customer->getZohoRecordId());
    }

    public function test_sync_job_dispatched_on_correct_queue(): void
    {
        config(['zoho.sync.queue' => 'zoho-sync']);

        TestCustomer::create([
            'name' => 'Queue Test',
            'email' => 'queue@example.com',
        ]);

        Queue::assertPushedOn('zoho-sync', SyncModelToZoho::class);
    }

    public function test_get_zoho_model_class_returns_null_by_default(): void
    {
        $customer = new TestCustomer;

        $this->assertNull($customer->getZohoModelClass());
    }

    public function test_get_zoho_model_class_returns_class_when_overridden(): void
    {
        $customer = new TestCustomModuleCustomer;

        $this->assertEquals(ZohoPropertyListing::class, $customer->getZohoModelClass());
    }

    public function test_custom_module_field_mapping(): void
    {
        $customer = TestCustomModuleCustomer::withoutZohoSync(fn () => TestCustomModuleCustomer::create([
            'name' => 'Custom Property',
            'email' => 'agent@realty.com',
            'company' => 'Realty Corp',
        ]));

        $data = $customer->transformToZohoData();

        $this->assertEquals('Custom Property', $data['Listing_Name']);
        $this->assertEquals('agent@realty.com', $data['Agent_Email']);
        $this->assertEquals('Realty Corp', $data['Agency']);
        $this->assertArrayNotHasKey('name', $data);
        $this->assertArrayNotHasKey('email', $data);
    }
}
