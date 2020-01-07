<?php

namespace Asciisd\Zoho\Tests;

use Asciisd\Zoho\Providers\ZohoServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

/**
 * Override the standard PHPUnit testcase with the Testbench testcase
 *
 * @see https://github.com/orchestral/testbench#usage
 */
class TestCase extends OrchestraTestCase
{
    /**
     * Include the package's service provider(s)
     *
     * @see https://github.com/orchestral/testbench#custom-service-provider
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ZohoServiceProvider::class
        ];
    }
}
