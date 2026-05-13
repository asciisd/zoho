<?php

use Asciisd\Zoho\Http\Controllers\ZohoWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Zoho CRM Webhook Routes
|--------------------------------------------------------------------------
|
| These routes handle incoming webhooks from Zoho CRM.
|
*/

Route::prefix('zoho')->name('zoho.')->group(function () {
    // OAuth callback handler
    Route::get('callback', [ZohoWebhookController::class, 'callback'])
        ->name('callback');

    // Webhook handler
    Route::post('webhook', [ZohoWebhookController::class, 'handle'])
        ->name('webhook');

    // Webhook verification (for initial setup)
    Route::get('webhook', [ZohoWebhookController::class, 'verify'])
        ->name('webhook.verify');
});
