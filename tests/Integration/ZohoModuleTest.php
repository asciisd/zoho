<?php

namespace Tests\Feature;

use Asciisd\Zoho\Tests\Integration\IntegrationTestCase;
use zcrmsdk\crm\crud\ZCRMModule;
use zcrmsdk\crm\crud\ZCRMRecord;

class ZohoModuleTest extends IntegrationTestCase
{

    private $client;
    private $module;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->getClient();
        $this->module = $this->client->useModule('leads');
    }

    /** @test */
    public function it_can_get_all_modules()
    {
        $leads = $this->client->getAllModules();

        self::assertInstanceOf(ZCRMModule::class, $leads[0]);
    }

    /** @test */
    public function is_can_get_module_by_name()
    {
        $leads = $this->client->getModule();

        self::assertInstanceOf(ZCRMModule::class, $leads);
    }

    /** @test */
    public function it_can_instantiate_a_record_with_id()
    {
        $record = $this->module->getRecordInstance('3582074000005414003');
        self::assertInstanceOf(ZCRMRecord::class, $record);
        self::assertEquals($record->getModuleApiName(), 'leads');
        self::assertEquals($record->getEntityId(), '3582074000005414003');
    }

    /** @test */
    public function it_can_instantiate_a_module_with_api_name()
    {
        $module = $this->module->getModuleInstance();
        self::assertInstanceOf(ZCRMModule::class, $module);
        self::assertEquals($module->getAPIName(), 'leads');
    }

    /** @test */
    public function it_can_get_records_for_given_module_api_name()
    {
        $records = $this->module->getRecords();
        self::assertInstanceOf(ZCRMRecord::class, $records[0]);
    }

    /** @test */
    public function it_can_get_record_by_module_api_name_and_record_id()
    {
        $record = $this->module->getRecord('3582074000005414003');
        self::assertInstanceOf(ZCRMRecord::class, $record);
    }

    /** @test */
    public function it_can_search_for_word_on_specific_module()
    {
        $records = $this->module->searchRecordsByWord('mohamed');

        self::assertInstanceOf(ZCRMRecord::class, $records[0]);
        self::assertEquals('3582074000000655089', $records[0]->getOwner()->getId());
    }

    /** @test */
    public function it_can_search_for_phone_on_specific_module()
    {
        $records = $this->module->searchRecordsByPhone('01011441444');

        self::assertInstanceOf(ZCRMRecord::class, $records[0]);
        self::assertEquals('01011441444', $records[0]->getFieldValue('Phone'));
    }

    /** @test */
    public function it_can_search_for_email_on_specific_module()
    {
        $records = $this->module->searchRecordsByEmail('aemaddin@gmail.com');

        self::assertInstanceOf(ZCRMRecord::class, $records[0]);
        self::assertEquals('aemaddin@gmail.com', $records[0]->getFieldValue('Email'));
    }

    /** @test */
    public function it_can_search_by_criteria() {
        $records = $this->module->searchRecordsByCriteria("(City:equals:Al Wasitah) and (State:equals:Al Fayyum)");

        self::assertInstanceOf(ZCRMRecord::class, $records[0]);
        self::assertEquals('falah.alhajeri6999@hotmail.com', $records[0]->getFieldValue('Email'));
    }

    /** @test */
    public function it_can_search_by_field_name() {
        $records = $this->module->where('City', 'Al Wasitah')->search();

        self::assertInstanceOf(ZCRMRecord::class, $records[0]);
        self::assertEquals('falah.alhajeri6999@hotmail.com', $records[0]->getFieldValue('Email'));
    }

    /** @test */
    public function it_can_search_with_multiple_criteria() {
        $records = $this->module
            ->where('City', 'Al Wasitah')
            ->andWhere('State', 'Al Fayyum')
            ->search();

        self::assertInstanceOf(ZCRMRecord::class, $records[0]);
        self::assertEquals('falah.alhajeri6999@hotmail.com', $records[0]->getFieldValue('Email'));
    }

    /** @test */
    public function it_can_create_new_record()
    {
        $lead = $this->module->getRecordInstance();

        $lead->setFieldValue('First_Name', 'Amr');
        $lead->setFieldValue('Last_Name', 'Emad');
        $lead->setFieldValue('Email', 'test@caveo.com.kw');
        $lead->setFieldValue('Phone', '012345678910');

        $lead = $lead->create()->getData();

        self::assertEquals('Amr', $lead->getFieldValue('First_Name'));
        self::assertEquals('Emad', $lead->getFieldValue('Last_Name'));
        self::assertEquals('test@caveo.com.kw', $lead->getFieldValue('Email'));
        self::assertEquals('012345678910', $lead->getFieldValue('Phone'));

        $lead->delete();
    }

    /** @test */
    public function it_can_update_records()
    {
        $lead = $this->module->getRecord('3582074000002383003');

        $lead->setFieldValue('Last_Name', 'Ahmed');
        $lead = $lead->update()->getData();

        self::assertEquals('Ahmed', $lead->getFieldValue('Last_Name'));
    }
}
