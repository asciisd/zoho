<?php

namespace Asciisd\Zoho\Tests\Unit\Jobs;

use Asciisd\Zoho\Jobs\SyncModelToZoho;
use Asciisd\Zoho\Models\ZohoContact;
use Asciisd\Zoho\Models\ZohoSync;
use Asciisd\Zoho\Tests\Mocks\TestCustomer;
use Asciisd\Zoho\Tests\Mocks\TestCustomModuleCustomer;
use Asciisd\Zoho\Tests\Mocks\ZohoPropertyListing;
use Asciisd\Zoho\Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncModelToZohoTest extends TestCase
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

    public function test_it_stores_model_class_and_id(): void
    {
        $customer = TestCustomer::withoutZohoSync(fn () => TestCustomer::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]));

        $job = new SyncModelToZoho($customer, 'create');

        $this->assertEquals(TestCustomer::class, $job->modelClass);
        $this->assertEquals($customer->id, $job->modelId);
        $this->assertEquals('create', $job->operation);
    }

    public function test_handle_create_creates_zoho_record_and_sync(): void
    {
        Http::fake([
            '*/crm/v8/Contacts' => Http::response([
                'data' => [
                    [
                        'code' => 'SUCCESS',
                        'details' => ['id' => 'zoho-record-123'],
                        'status' => 'success',
                    ],
                ],
            ]),
        ]);

        $customer = TestCustomer::withoutZohoSync(fn () => TestCustomer::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+1234567890',
        ]));

        $job = new SyncModelToZoho($customer, 'create');
        $job->handle();

        $sync = ZohoSync::where('zohoable_type', TestCustomer::class)
            ->where('zohoable_id', $customer->id)
            ->first();

        $this->assertNotNull($sync);
        $this->assertEquals('zoho-record-123', $sync->zoho_record_id);
        $this->assertEquals('Contacts', $sync->zoho_module);
    }

    public function test_handle_update_updates_existing_zoho_record(): void
    {
        $customer = TestCustomer::withoutZohoSync(fn () => TestCustomer::create([
            'name' => 'Update Me',
            'email' => 'update@example.com',
        ]));

        ZohoSync::create([
            'zohoable_type' => TestCustomer::class,
            'zohoable_id' => $customer->id,
            'zoho_module' => 'Contacts',
            'zoho_record_id' => 'existing-zoho-id',
        ]);

        Http::fake([
            '*/crm/v8/Contacts' => Http::response([
                'data' => [
                    ['code' => 'SUCCESS', 'status' => 'success'],
                ],
            ]),
        ]);

        $job = new SyncModelToZoho($customer, 'update');
        $job->handle();

        Http::assertSent(function ($request) {
            return $request->method() === 'PUT'
                && str_contains($request->url(), '/Contacts');
        });

        $sync = $customer->zohoSync->fresh();
        $this->assertNotNull($sync->last_synced_at);
    }

    public function test_handle_update_creates_record_if_no_sync_exists(): void
    {
        Http::fake([
            '*/crm/v8/Contacts' => Http::response([
                'data' => [
                    [
                        'code' => 'SUCCESS',
                        'details' => ['id' => 'new-zoho-id'],
                        'status' => 'success',
                    ],
                ],
            ]),
        ]);

        $customer = TestCustomer::withoutZohoSync(fn () => TestCustomer::create([
            'name' => 'No Sync Yet',
            'email' => 'nosync@example.com',
        ]));

        $job = new SyncModelToZoho($customer, 'update');
        $job->handle();

        $sync = ZohoSync::where('zohoable_type', TestCustomer::class)
            ->where('zohoable_id', $customer->id)
            ->first();

        $this->assertNotNull($sync);
        $this->assertEquals('new-zoho-id', $sync->zoho_record_id);
    }

    public function test_handle_delete_deletes_zoho_record(): void
    {
        $customer = TestCustomer::withoutZohoSync(fn () => TestCustomer::create([
            'name' => 'Delete Me',
            'email' => 'delete@example.com',
        ]));

        ZohoSync::create([
            'zohoable_type' => TestCustomer::class,
            'zohoable_id' => $customer->id,
            'zoho_module' => 'Contacts',
            'zoho_record_id' => 'delete-zoho-id',
        ]);

        Http::fake([
            '*/crm/v8/Contacts/delete-zoho-id' => Http::response([
                'data' => [['code' => 'SUCCESS', 'status' => 'success']],
            ]),
        ]);

        $job = new SyncModelToZoho($customer, 'delete');
        $job->handle();

        $this->assertNull(
            ZohoSync::where('zohoable_type', TestCustomer::class)
                ->where('zohoable_id', $customer->id)
                ->first()
        );
    }

    public function test_handle_delete_does_nothing_when_no_sync_record(): void
    {
        $customer = TestCustomer::withoutZohoSync(fn () => TestCustomer::create([
            'name' => 'No Sync',
            'email' => 'nosync@example.com',
        ]));

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message) {
                return str_contains($message, 'No Zoho record to delete');
            });

        $job = new SyncModelToZoho($customer, 'delete');
        $job->handle();

        Http::assertNothingSent();
    }

    public function test_handle_logs_warning_when_model_not_found(): void
    {
        $customer = TestCustomer::withoutZohoSync(fn () => TestCustomer::create([
            'name' => 'Ghost',
            'email' => 'ghost@example.com',
        ]));

        $job = new SyncModelToZoho($customer, 'create');

        TestCustomer::withoutZohoSync(fn () => $customer->delete());

        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message) {
                return str_contains($message, 'Model not found');
            });

        $job->handle();
    }

    public function test_it_has_correct_retry_configuration(): void
    {
        $customer = TestCustomer::withoutZohoSync(fn () => TestCustomer::create([
            'name' => 'Retry Test',
            'email' => 'retry@example.com',
        ]));

        $job = new SyncModelToZoho($customer, 'create');

        $this->assertEquals(3, $job->tries);
        $this->assertEquals([60, 120, 300], $job->backoff());
    }

    public function test_handle_throws_for_invalid_operation(): void
    {
        $customer = TestCustomer::withoutZohoSync(fn () => TestCustomer::create([
            'name' => 'Invalid Op',
            'email' => 'invalid@example.com',
        ]));

        $job = new SyncModelToZoho($customer, 'invalid');

        $this->expectException(\Exception::class);

        $job->handle();
    }

    public function test_naming_convention_fallback_maps_standard_modules(): void
    {
        $customer = TestCustomer::withoutZohoSync(fn () => TestCustomer::create([
            'name' => 'Module Map',
            'email' => 'map@example.com',
        ]));

        $job = new SyncModelToZoho($customer, 'create');

        $reflection = new \ReflectionMethod($job, 'guessZohoModelClass');
        $reflection->setAccessible(true);

        $this->assertEquals(
            'Asciisd\Zoho\Models\ZohoContact',
            $reflection->invoke($job, 'Contacts')
        );

        $this->assertEquals(
            'Asciisd\Zoho\Models\ZohoLead',
            $reflection->invoke($job, 'Leads')
        );

        $this->assertEquals(
            'Asciisd\Zoho\Models\ZohoDeal',
            $reflection->invoke($job, 'Deals')
        );
    }

    public function test_resolve_uses_model_class_when_provided(): void
    {
        $customer = TestCustomModuleCustomer::withoutZohoSync(fn () => TestCustomModuleCustomer::create([
            'name' => 'Custom Module',
            'email' => 'custom@example.com',
        ]));

        $job = new SyncModelToZoho($customer, 'create');

        $reflection = new \ReflectionMethod($job, 'resolveZohoModelClass');
        $reflection->setAccessible(true);

        $resolved = $reflection->invoke($job, $customer, 'Property_Listings');

        $this->assertEquals(ZohoPropertyListing::class, $resolved);
    }

    public function test_resolve_uses_config_map_when_model_returns_null(): void
    {
        config(['zoho.modules.Contacts' => ZohoPropertyListing::class]);

        $customer = TestCustomer::withoutZohoSync(fn () => TestCustomer::create([
            'name' => 'Config Map',
            'email' => 'configmap@example.com',
        ]));

        $job = new SyncModelToZoho($customer, 'create');

        $reflection = new \ReflectionMethod($job, 'resolveZohoModelClass');
        $reflection->setAccessible(true);

        $resolved = $reflection->invoke($job, $customer, 'Contacts');

        $this->assertEquals(ZohoPropertyListing::class, $resolved);
    }

    public function test_resolve_falls_back_to_naming_convention(): void
    {
        $customer = TestCustomer::withoutZohoSync(fn () => TestCustomer::create([
            'name' => 'Fallback',
            'email' => 'fallback@example.com',
        ]));

        $job = new SyncModelToZoho($customer, 'create');

        $reflection = new \ReflectionMethod($job, 'resolveZohoModelClass');
        $reflection->setAccessible(true);

        $resolved = $reflection->invoke($job, $customer, 'Contacts');

        $this->assertEquals(ZohoContact::class, $resolved);
    }

    public function test_custom_module_create_syncs_correctly(): void
    {
        Http::fake([
            '*/crm/v8/Property_Listings' => Http::response([
                'data' => [
                    [
                        'code' => 'SUCCESS',
                        'details' => ['id' => 'custom-record-456'],
                        'status' => 'success',
                    ],
                ],
            ]),
        ]);

        $customer = TestCustomModuleCustomer::withoutZohoSync(fn () => TestCustomModuleCustomer::create([
            'name' => 'Custom Listing',
            'email' => 'listing@example.com',
            'company' => 'Realty Co',
        ]));

        $job = new SyncModelToZoho($customer, 'create');
        $job->handle();

        $sync = ZohoSync::where('zohoable_type', TestCustomModuleCustomer::class)
            ->where('zohoable_id', $customer->id)
            ->first();

        $this->assertNotNull($sync);
        $this->assertEquals('custom-record-456', $sync->zoho_record_id);
        $this->assertEquals('Property_Listings', $sync->zoho_module);

        Http::assertSent(function ($request) {
            $body = $request->data();

            return str_contains($request->url(), '/Property_Listings')
                && $body['data'][0]['Listing_Name'] === 'Custom Listing'
                && $body['data'][0]['Agent_Email'] === 'listing@example.com'
                && $body['data'][0]['Agency'] === 'Realty Co';
        });
    }
}
