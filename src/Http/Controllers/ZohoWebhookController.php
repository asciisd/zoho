<?php

namespace Asciisd\Zoho\Http\Controllers;

use Asciisd\Zoho\Events\ZohoRecordCreated;
use Asciisd\Zoho\Events\ZohoRecordDeleted;
use Asciisd\Zoho\Events\ZohoRecordUpdated;
use Asciisd\Zoho\Events\ZohoWebhookReceived;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class ZohoWebhookController extends Controller
{
    /**
     * Handle Zoho CRM webhook.
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            // Verify webhook signature if secret is configured
            if (! $this->verifySignature($request)) {
                Log::warning('Zoho webhook signature verification failed');

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature',
                ], 401);
            }

            $payload = $request->all();

            // Log webhook for debugging
            Log::info('Zoho webhook received', ['payload' => $payload]);

            // Dispatch base webhook event
            event(new ZohoWebhookReceived($payload));

            // Dispatch specific events based on webhook type
            $this->dispatchSpecificEvent($payload);

            return response()->json([
                'success' => true,
                'message' => 'Webhook processed successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Zoho webhook processing failed: '.$e->getMessage(), [
                'exception' => $e,
                'payload' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify webhook signature.
     */
    protected function verifySignature(Request $request): bool
    {
        $webhookSecret = config('zoho.webhook_secret');

        // If no secret is configured, skip verification
        if (empty($webhookSecret)) {
            return true;
        }

        $signature = $request->header('X-Zoho-Webhook-Signature');

        if (empty($signature)) {
            return false;
        }

        // Generate expected signature
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Dispatch specific event based on webhook type.
     */
    protected function dispatchSpecificEvent(array $payload): void
    {
        $event = $payload['event'] ?? null;
        $module = $payload['module'] ?? null;
        $record = $payload['data'] ?? [];

        if (! $event || ! $module) {
            return;
        }

        // Normalize event name
        $event = strtolower($event);

        // Dispatch specific events
        if (str_contains($event, 'create') || str_contains($event, 'insert')) {
            event(new ZohoRecordCreated($record, $module));
        } elseif (str_contains($event, 'update') || str_contains($event, 'edit')) {
            event(new ZohoRecordUpdated($record, $module));
        } elseif (str_contains($event, 'delete') || str_contains($event, 'remove')) {
            event(new ZohoRecordDeleted($record, $module));
        }
    }

    /**
     * Handle webhook verification (for initial setup).
     */
    public function verify(Request $request): JsonResponse
    {
        // Zoho sends a verification challenge when setting up webhook
        $challenge = $request->input('challenge');

        if ($challenge) {
            return response()->json([
                'challenge' => $challenge,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Webhook endpoint is active',
        ]);
    }

    /**
     * Handle OAuth callback from Zoho.
     */
    public function callback(Request $request): JsonResponse
    {
        try {
            $code = $request->input('code');
            $location = $request->input('location');

            if (! $code) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authorization code not provided',
                ], 400);
            }

            $oauth = app('zoho.oauth');
            $tokens = $oauth->generateAccessToken($code);

            Log::info('Zoho OAuth successful', [
                'location' => $location,
                'has_refresh_token' => isset($tokens['refresh_token']),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Authorization successful! Tokens have been saved.',
                'data' => [
                    'access_token' => substr($tokens['access_token'], 0, 20).'...',
                    'refresh_token' => isset($tokens['refresh_token']) ? substr($tokens['refresh_token'], 0, 20).'...' : null,
                    'expires_in' => $tokens['expires_in'] ?? null,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Zoho OAuth callback failed: '.$e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Authorization failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
