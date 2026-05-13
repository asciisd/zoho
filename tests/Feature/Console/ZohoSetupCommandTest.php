<?php

namespace Asciisd\Zoho\Tests\Feature\Console;

use Asciisd\Zoho\Tests\TestCase;

class ZohoSetupCommandTest extends TestCase
{
    public function test_setup_fails_when_client_id_missing(): void
    {
        config(['zoho.client_id' => '']);

        $this->artisan('zoho:setup')
            ->expectsOutputToContain('Missing Zoho CRM configuration')
            ->assertExitCode(1);
    }

    public function test_setup_fails_when_client_secret_missing(): void
    {
        config(['zoho.client_secret' => '']);

        $this->artisan('zoho:setup')
            ->expectsOutputToContain('Missing Zoho CRM configuration')
            ->assertExitCode(1);
    }
}
