<?php

namespace Asciisd\Zoho\Tests;

use Illuminate\Foundation\Application;
use Asciisd\Zoho\Providers\ZohoServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    /**
     * Include the package's service provider(s)
     *
     * @param  Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ZohoServiceProvider::class,
        ];
    }
}
