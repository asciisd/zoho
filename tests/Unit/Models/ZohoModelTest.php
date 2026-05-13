<?php

namespace Asciisd\ZohoV8\Tests\Unit\Models;

use Asciisd\ZohoV8\Exceptions\ZohoApiException;
use Asciisd\ZohoV8\Models\ZohoContact;
use Asciisd\ZohoV8\Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class ZohoModelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seedValidToken();
        ZohoContact::clearAllFieldCache();
    }

    protected function seedValidToken(): void
    {
        app('zoho.storage')->storeTokens([
            'access_token' => 'valid-access-token',
            'refresh_token' => 'valid-refresh-token',
            'expires_in' => 3600,
        ]);
    }

    protected function fakeFieldMetadataResponse(): void
    {
        Http::fake([
            '*/crm/v8/settings/fields*' => Http::response([
                'fields' => [
                    ['api_name' => 'id'],
                    ['api_name' => 'First_Name'],
                    ['api_name' => 'Last_Name'],
                    ['api_name' => 'Email'],
                    ['api_name' => 'Phone'],
                ],
            ]),
        ]);
    }

    public function test_create_sends_correct_payload(): void
    {
        Http::fake([
            '*/crm/v8/Contacts' => Http::response([
                'data' => [
                    [
                        'code' => 'SUCCESS',
                        'details' => ['id' => '123456789'],
                        'status' => 'success',
                    ],
                ],
            ]),
        ]);

        $result = ZohoContact::create([
            'Last_Name' => 'Doe',
            'Email' => 'doe@example.com',
        ]);

        $this->assertEquals('SUCCESS', $result['code']);
        $this->assertEquals('123456789', $result['details']['id']);

        Http::assertSent(function ($request) {
            $body = $request->data();

            return str_contains($request->url(), '/Contacts')
                && $request->method() === 'POST'
                && $body['data'][0]['Last_Name'] === 'Doe';
        });
    }

    public function test_find_returns_record(): void
    {
        Http::fake([
            '*/crm/v8/settings/fields*' => Http::response([
                'fields' => [['api_name' => 'id'], ['api_name' => 'Last_Name']],
            ]),
            '*/crm/v8/Contacts/123*' => Http::response([
                'data' => [
                    ['id' => '123', 'Last_Name' => 'Doe', 'Email' => 'doe@example.com'],
                ],
            ]),
        ]);

        $record = ZohoContact::find('123');

        $this->assertEquals('123', $record['id']);
        $this->assertEquals('Doe', $record['Last_Name']);
    }

    public function test_find_throws_when_record_not_found(): void
    {
        Http::fake([
            '*/crm/v8/settings/fields*' => Http::response(['fields' => [['api_name' => 'id']]]),
            '*/crm/v8/Contacts/nonexistent*' => Http::response(['data' => []]),
        ]);

        $this->expectException(ZohoApiException::class);
        $this->expectExceptionMessage('Record not found');

        ZohoContact::find('nonexistent');
    }

    public function test_all_returns_collection(): void
    {
        Http::fake([
            '*/crm/v8/settings/fields*' => Http::response(['fields' => [['api_name' => 'id']]]),
            '*/crm/v8/Contacts?*' => Http::response([
                'data' => [
                    ['id' => '1', 'Last_Name' => 'Doe'],
                    ['id' => '2', 'Last_Name' => 'Smith'],
                ],
            ]),
        ]);

        $records = ZohoContact::all();

        $this->assertInstanceOf(Collection::class, $records);
        $this->assertCount(2, $records);
    }

    public function test_all_returns_empty_collection_when_no_data(): void
    {
        Http::fake([
            '*/crm/v8/settings/fields*' => Http::response(['fields' => [['api_name' => 'id']]]),
            '*/crm/v8/Contacts?*' => Http::response([]),
        ]);

        $records = ZohoContact::all();

        $this->assertInstanceOf(Collection::class, $records);
        $this->assertCount(0, $records);
    }

    public function test_update_sends_correct_payload(): void
    {
        Http::fake([
            '*/crm/v8/Contacts' => Http::response([
                'data' => [
                    [
                        'code' => 'SUCCESS',
                        'details' => ['id' => '123'],
                        'status' => 'success',
                    ],
                ],
            ]),
        ]);

        $result = ZohoContact::update('123', ['Phone' => '+1234567890']);

        $this->assertEquals('SUCCESS', $result['code']);

        Http::assertSent(function ($request) {
            $body = $request->data();

            return $request->method() === 'PUT'
                && $body['data'][0]['id'] === '123'
                && $body['data'][0]['Phone'] === '+1234567890';
        });
    }

    public function test_delete_returns_true_on_success(): void
    {
        Http::fake([
            '*/crm/v8/Contacts/123' => Http::response([
                'data' => [
                    ['code' => 'SUCCESS', 'status' => 'success'],
                ],
            ]),
        ]);

        $result = ZohoContact::delete('123');

        $this->assertTrue($result);
    }

    public function test_search_returns_collection(): void
    {
        Http::fake([
            '*/crm/v8/settings/fields*' => Http::response(['fields' => [['api_name' => 'id']]]),
            '*/crm/v8/Contacts/search*' => Http::response([
                'data' => [
                    ['id' => '1', 'Last_Name' => 'Doe', 'Email' => 'doe@example.com'],
                ],
            ]),
        ]);

        $results = ZohoContact::search('(Email:equals:doe@example.com)');

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertCount(1, $results);
    }

    public function test_search_by_email(): void
    {
        Http::fake([
            '*/crm/v8/settings/fields*' => Http::response(['fields' => [['api_name' => 'id']]]),
            '*/crm/v8/Contacts/search*' => Http::response([
                'data' => [
                    ['id' => '1', 'Email' => 'test@example.com'],
                ],
            ]),
        ]);

        $results = ZohoContact::searchByEmail('test@example.com');

        $this->assertCount(1, $results);
    }

    public function test_search_by_phone(): void
    {
        Http::fake([
            '*/crm/v8/settings/fields*' => Http::response(['fields' => [['api_name' => 'id']]]),
            '*/crm/v8/Contacts/search*' => Http::response([
                'data' => [
                    ['id' => '1', 'Phone' => '+1234567890'],
                ],
            ]),
        ]);

        $results = ZohoContact::searchByPhone('+1234567890');

        $this->assertCount(1, $results);
    }

    public function test_search_returns_empty_collection_on_no_content_response(): void
    {
        Http::fake([
            '*/crm/v8/settings/fields*' => Http::response(['fields' => [['api_name' => 'id']]]),
            '*/crm/v8/Contacts/search*' => Http::response(null, 204),
        ]);

        $results = ZohoContact::searchByEmail('nonexistent@example.com');

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertCount(0, $results);
    }

    public function test_upsert_sends_correct_payload(): void
    {
        Http::fake([
            '*/crm/v8/Contacts/upsert' => Http::response([
                'data' => [
                    ['code' => 'SUCCESS', 'details' => ['id' => '123'], 'status' => 'success'],
                ],
            ]),
        ]);

        $result = ZohoContact::upsert(
            ['Last_Name' => 'Doe', 'Email' => 'doe@example.com'],
            ['Email']
        );

        $this->assertEquals('SUCCESS', $result['code']);

        Http::assertSent(function ($request) {
            $body = $request->data();

            return str_contains($request->url(), '/upsert')
                && $body['duplicate_check_fields'] === ['Email'];
        });
    }

    public function test_upsert_without_duplicate_check_fields(): void
    {
        Http::fake([
            '*/crm/v8/Contacts/upsert' => Http::response([
                'data' => [
                    ['code' => 'SUCCESS', 'status' => 'success'],
                ],
            ]),
        ]);

        ZohoContact::upsert(['Last_Name' => 'Doe']);

        Http::assertSent(function ($request) {
            $body = $request->data();

            return ! isset($body['duplicate_check_fields']);
        });
    }

    public function test_get_related_records(): void
    {
        Http::fake([
            '*/crm/v8/Contacts/123/Notes*' => Http::response([
                'data' => [
                    ['id' => 'n1', 'Note_Content' => 'Test note'],
                ],
            ]),
        ]);

        $records = ZohoContact::getRelatedRecords('123', 'Notes');

        $this->assertInstanceOf(Collection::class, $records);
        $this->assertCount(1, $records);
    }

    public function test_update_multiple_records(): void
    {
        Http::fake([
            '*/crm/v8/Contacts' => Http::response([
                'data' => [
                    ['code' => 'SUCCESS', 'details' => ['id' => '1']],
                    ['code' => 'SUCCESS', 'details' => ['id' => '2']],
                ],
            ]),
        ]);

        $result = ZohoContact::updateMultiple([
            ['id' => '1', 'Phone' => '+111'],
            ['id' => '2', 'Phone' => '+222'],
        ]);

        $this->assertCount(2, $result);
    }

    public function test_delete_multiple_records(): void
    {
        Http::fake([
            '*/crm/v8/Contacts?ids=1,2,3' => Http::response([
                'data' => [
                    ['code' => 'SUCCESS', 'status' => 'success'],
                    ['code' => 'SUCCESS', 'status' => 'success'],
                    ['code' => 'SUCCESS', 'status' => 'success'],
                ],
            ]),
        ]);

        $result = ZohoContact::deleteMultiple(['1', '2', '3']);

        $this->assertCount(3, $result);
    }

    public function test_count_returns_integer(): void
    {
        Http::fake([
            '*/crm/v8/Contacts/actions/count*' => Http::response([
                'count' => 42,
            ]),
        ]);

        $count = ZohoContact::count();

        $this->assertEquals(42, $count);
    }

    public function test_count_returns_zero_when_no_count_key(): void
    {
        Http::fake([
            '*/crm/v8/Contacts/actions/count*' => Http::response([]),
        ]);

        $count = ZohoContact::count();

        $this->assertEquals(0, $count);
    }

    public function test_convert_sends_correct_request(): void
    {
        Http::fake([
            '*/crm/v8/Contacts/123/actions/convert' => Http::response([
                'data' => [['Contacts' => '456', 'Deals' => '789']],
            ]),
        ]);

        $result = ZohoContact::convert('123', ['overwrite' => true]);

        $this->assertArrayHasKey('data', $result);
    }

    public function test_make_request_throws_on_api_error(): void
    {
        Http::fake([
            '*/crm/v8/Contacts' => Http::response([
                'message' => 'INVALID_MODULE',
            ], 400),
        ]);

        $this->expectException(ZohoApiException::class);

        ZohoContact::create(['Last_Name' => 'Fail']);
    }

    public function test_field_names_are_cached(): void
    {
        Http::fake([
            '*/crm/v8/settings/fields*' => Http::response([
                'fields' => [['api_name' => 'id'], ['api_name' => 'Last_Name']],
            ]),
            '*/crm/v8/Contacts?*' => Http::response(['data' => []]),
            '*/crm/v8/Contacts/search*' => Http::response(['data' => []]),
        ]);

        ZohoContact::all();
        ZohoContact::search('(Email:equals:test@test.com)');

        Http::assertSentCount(3);
    }

    public function test_clear_field_cache(): void
    {
        Http::fake([
            '*/crm/v8/settings/fields*' => Http::response([
                'fields' => [['api_name' => 'id']],
            ]),
            '*/crm/v8/Contacts?*' => Http::response(['data' => []]),
        ]);

        ZohoContact::all();
        ZohoContact::clearFieldCache();
        ZohoContact::all();

        Http::assertSentCount(4);
    }

    public function test_get_field_metadata(): void
    {
        Http::fake([
            '*/crm/v8/settings/fields*' => Http::response([
                'fields' => [
                    ['api_name' => 'id', 'data_type' => 'bigint'],
                    ['api_name' => 'Last_Name', 'data_type' => 'text'],
                ],
            ]),
        ]);

        $fields = ZohoContact::getFieldMetadata();

        $this->assertCount(2, $fields);
        $this->assertEquals('id', $fields[0]['api_name']);
    }

    public function test_get_deleted_records(): void
    {
        Http::fake([
            '*/crm/v8/settings/fields*' => Http::response(['fields' => [['api_name' => 'id']]]),
            '*/crm/v8/Contacts/deleted*' => Http::response([
                'data' => [
                    ['id' => '1', 'type' => 'all'],
                ],
            ]),
        ]);

        $deleted = ZohoContact::getDeletedRecords();

        $this->assertInstanceOf(Collection::class, $deleted);
        $this->assertCount(1, $deleted);
    }
}
