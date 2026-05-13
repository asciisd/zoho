<?php

namespace Asciisd\Zoho\Tests\Feature\Http;

use Asciisd\Zoho\Events\ZohoRecordCreated;
use Asciisd\Zoho\Events\ZohoRecordDeleted;
use Asciisd\Zoho\Events\ZohoRecordUpdated;
use Asciisd\Zoho\Events\ZohoWebhookReceived;
use Asciisd\Zoho\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

class ZohoWebhookControllerTest extends TestCase
{
    public function test_webhook_handles_valid_request_with_correct_signature(): void
    {
        Event::fake();

        $payload = json_encode([
            'event' => 'create',
            'module' => 'Contacts',
            'data' => ['id' => '123', 'Last_Name' => 'Doe'],
        ]);

        $signature = hash_hmac('sha256', $payload, 'test-webhook-secret');

        $response = $this->postJson('/zoho/webhook', json_decode($payload, true), [
            'X-Zoho-Webhook-Signature' => $signature,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        Event::assertDispatched(ZohoWebhookReceived::class);
        Event::assertDispatched(ZohoRecordCreated::class);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $response = $this->postJson('/zoho/webhook', [
            'event' => 'create',
            'module' => 'Contacts',
        ], [
            'X-Zoho-Webhook-Signature' => 'invalid-signature',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['success' => false, 'message' => 'Invalid signature']);
    }

    public function test_webhook_rejects_missing_signature_when_secret_configured(): void
    {
        $response = $this->postJson('/zoho/webhook', [
            'event' => 'create',
            'module' => 'Contacts',
        ]);

        $response->assertStatus(401);
    }

    public function test_webhook_allows_request_when_no_secret_configured(): void
    {
        Event::fake();
        config(['zoho.webhook_secret' => null]);

        $response = $this->postJson('/zoho/webhook', [
            'event' => 'create',
            'module' => 'Contacts',
            'data' => ['id' => '123'],
        ]);

        $response->assertOk();
        Event::assertDispatched(ZohoWebhookReceived::class);
    }

    public function test_webhook_dispatches_create_event(): void
    {
        Event::fake();
        config(['zoho.webhook_secret' => null]);

        $this->postJson('/zoho/webhook', [
            'event' => 'create',
            'module' => 'Contacts',
            'data' => ['id' => '123'],
        ]);

        Event::assertDispatched(ZohoRecordCreated::class, function ($event) {
            return $event->module === 'Contacts';
        });
    }

    public function test_webhook_dispatches_insert_event(): void
    {
        Event::fake();
        config(['zoho.webhook_secret' => null]);

        $this->postJson('/zoho/webhook', [
            'event' => 'insert',
            'module' => 'Leads',
            'data' => ['id' => '456'],
        ]);

        Event::assertDispatched(ZohoRecordCreated::class, function ($event) {
            return $event->module === 'Leads';
        });
    }

    public function test_webhook_dispatches_update_event(): void
    {
        Event::fake();
        config(['zoho.webhook_secret' => null]);

        $this->postJson('/zoho/webhook', [
            'event' => 'update',
            'module' => 'Contacts',
            'data' => ['id' => '123'],
        ]);

        Event::assertDispatched(ZohoRecordUpdated::class);
    }

    public function test_webhook_dispatches_edit_event(): void
    {
        Event::fake();
        config(['zoho.webhook_secret' => null]);

        $this->postJson('/zoho/webhook', [
            'event' => 'edit',
            'module' => 'Contacts',
            'data' => ['id' => '123'],
        ]);

        Event::assertDispatched(ZohoRecordUpdated::class);
    }

    public function test_webhook_dispatches_delete_event(): void
    {
        Event::fake();
        config(['zoho.webhook_secret' => null]);

        $this->postJson('/zoho/webhook', [
            'event' => 'delete',
            'module' => 'Contacts',
            'data' => ['id' => '123'],
        ]);

        Event::assertDispatched(ZohoRecordDeleted::class);
    }

    public function test_webhook_dispatches_remove_event(): void
    {
        Event::fake();
        config(['zoho.webhook_secret' => null]);

        $this->postJson('/zoho/webhook', [
            'event' => 'remove',
            'module' => 'Contacts',
            'data' => ['id' => '123'],
        ]);

        Event::assertDispatched(ZohoRecordDeleted::class);
    }

    public function test_webhook_does_not_dispatch_specific_event_without_event_key(): void
    {
        Event::fake();
        config(['zoho.webhook_secret' => null]);

        $this->postJson('/zoho/webhook', [
            'module' => 'Contacts',
            'data' => ['id' => '123'],
        ]);

        Event::assertDispatched(ZohoWebhookReceived::class);
        Event::assertNotDispatched(ZohoRecordCreated::class);
        Event::assertNotDispatched(ZohoRecordUpdated::class);
        Event::assertNotDispatched(ZohoRecordDeleted::class);
    }

    public function test_webhook_verify_returns_challenge(): void
    {
        $response = $this->getJson('/zoho/webhook?challenge=test-challenge-123');

        $response->assertOk();
        $response->assertJson(['challenge' => 'test-challenge-123']);
    }

    public function test_webhook_verify_returns_active_status_without_challenge(): void
    {
        $response = $this->getJson('/zoho/webhook');

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Webhook endpoint is active',
        ]);
    }

    public function test_oauth_callback_generates_tokens(): void
    {
        Http::fake([
            'accounts.zoho.com/oauth/v2/token' => Http::response([
                'access_token' => 'callback-access-token',
                'refresh_token' => 'callback-refresh-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]),
        ]);

        $response = $this->getJson('/zoho/callback?code=test-auth-code&location=us');

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => ['access_token', 'refresh_token', 'expires_in'],
        ]);
    }

    public function test_oauth_callback_fails_without_code(): void
    {
        $response = $this->getJson('/zoho/callback');

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Authorization code not provided',
        ]);
    }

    public function test_oauth_callback_handles_token_generation_error(): void
    {
        Http::fake([
            'accounts.zoho.com/oauth/v2/token' => Http::response([
                'error' => 'invalid_code',
            ], 400),
        ]);

        $response = $this->getJson('/zoho/callback?code=invalid-code');

        $response->assertStatus(500);
        $response->assertJson(['success' => false]);
    }
}
