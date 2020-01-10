<?php

namespace Tests\Feature;

use Asciisd\Zoho\Facades\Zoho;
use Asciisd\Zoho\Tests\Integration\IntegrationTestCase;
use zcrmsdk\crm\exception\ZCRMException;
use zcrmsdk\crm\setup\org\ZCRMOrganization;

class ZohoOrganizationTest extends IntegrationTestCase
{
    private $org;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Zoho::currentOrg();
    }

    /** @test */
    public function it_can_instantiate_an_organization()
    {
        $organization = $this->org->getOrganizationInstance();

        dump($organization);

        self::assertInstanceOf(ZCRMOrganization::class, $organization);
    }

    /** @test */
    public function it_can_get_organization_details()
    {
        $this->expectException(ZCRMException::class);

        $organization = $this->org->getOrganizationDetails();

//        self::assertInstanceOf(ZCRMOrganization::class, $organization);
    }
}
