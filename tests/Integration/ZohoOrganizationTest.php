<?php

namespace Asciisd\Zoho\Tests\Integration;

use zcrmsdk\crm\setup\org\ZCRMOrganization;

class ZohoOrganizationTest extends IntegrationTestCase
{
    private $org;

    protected function setUp(): void
    {
        parent::setUp();

        $client = $this->getClient();
        $this->org = $client->currentOrg();
    }

    /** @test */
    public function it_can_instantiate_an_organization()
    {
        $organization = $this->org->getOrganizationInstance();

        self::assertInstanceOf(ZCRMOrganization::class, $organization);
    }

    /** @test */
    public function it_can_get_organization_details()
    {
        $organization = $this->org->getOrganizationDetails();

        self::assertInstanceOf(ZCRMOrganization::class, $organization);
    }
}
