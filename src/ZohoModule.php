<?php

namespace Asciisd\Zoho;

use com\zoho\crm\api\exception\SDKException;
use com\zoho\crm\api\fields\FieldsOperations;
use com\zoho\crm\api\Initializer;
use com\zoho\crm\api\modules\Module;
use com\zoho\crm\api\modules\ModulesOperations;
use com\zoho\crm\api\modules\ResponseWrapper;
use com\zoho\crm\api\ParameterMap;
use com\zoho\crm\api\record\BodyWrapper;
use com\zoho\crm\api\record\Field;
use com\zoho\crm\api\record\Record;
use com\zoho\crm\api\record\RecordOperations;
use com\zoho\crm\api\record\SearchRecordsParam;
use com\zoho\crm\api\util\APIResponse;
use zcrmsdk\crm\crud\ZCRMModule;
use zcrmsdk\crm\crud\ZCRMRecord;
use zcrmsdk\crm\setup\restclient\ZCRMRestClient;

class ZohoModule
{
    protected Initializer $initializer;
    protected string $moduleApiName;
    protected array $operators = ['equals', 'starts_with'];

    /**
     * ZohoModule constructor.
     */
    public function __construct(Initializer $rest, string $moduleApiName)
    {
        $this->initializer = $rest;
        $this->moduleApiName = $moduleApiName;
    }


    /**
     * to get the the modules in form of ZCRMModule instances array
     *
     * @return APIResponse
     */
    public function getAllModules()
    {
        $modules = new ModulesOperations();
        return $modules->getModules();
    }

    /**
     * to get the module in form of ZCRMModule instance
     *
     * @return Module|null
     */
    public function getModule(): ?Module
    {
        try {
            $modules = new ModulesOperations();
            /** @var ResponseWrapper $module */
            $module = $modules->getModule($this->moduleApiName)->getObject();
            if (count($module->getModules()) > 0) {
                return $module->getModules()[0] ?? null;
            } else {
                return null;
            }
        } catch (SDKException) {
            return null;
        }
    }

    /**
     * @return APIResponse
     */
    public function getRecords(): APIResponse
    {
        try {
            $recordOperations = new RecordOperations();
            return $recordOperations->getRecords($this->moduleApiName);
        } catch (SDKException $exception) {
            dd($exception);
        }
    }

    /**
     * @param string $recordId
     * @return APIResponse
     */
    public function getRecord(string $recordId): APIResponse
    {
        $recordOperations = new RecordOperations();
        return $recordOperations->getRecord($recordId, $this->moduleApiName);
    }

    /**
     * get module records
     *
     * @param string $word //word to be searched
     * @param int $page //to get the list of records from the respective pages. Default value for page is 1.
     * @param int $perPage //To get the list of records available per page. Default value for per page is 200.
     * @return APIResponse
     */
    public function searchRecordsByWord($word = '', $page = 1, $perPage = 200)
    {
        try {
            $recordOperations = new RecordOperations();
            $paramInstance = new ParameterMap();
            $paramInstance->add(SearchRecordsParam::word(), $word);
            $paramInstance->add(SearchRecordsParam::page(), $page);
            $paramInstance->add(SearchRecordsParam::perPage(), $perPage);
            return $recordOperations->searchRecords($this->moduleApiName, $paramInstance);
        } catch (SDKException $exception) {
            return null;
        }
    }

    /**
     * get module records
     *
     * @param string $phone //phone to be searched
     * @param int $page //to get the list of records from the respective pages. Default value for page is 1.
     * @param int $perPage //To get the list of records available per page. Default value for per page is 200.
     * @return APIResponse
     */
    public function searchRecordsByPhone($phone = '', $page = 1, $perPage = 200)
    {
        try {
            $recordOperations = new RecordOperations();
            $paramInstance = new ParameterMap();
            $paramInstance->add(SearchRecordsParam::phone(), $phone);
            $paramInstance->add(SearchRecordsParam::page(), $page);
            $paramInstance->add(SearchRecordsParam::perPage(), $perPage);
            return $recordOperations->searchRecords($this->moduleApiName, $paramInstance);
        } catch (SDKException $exception) {
            return null;
        }
    }

    /**
     * get module records
     *
     * @param string $email //email to be searched
     * @param int $page //to get the list of records from the respective pages. Default value for page is 1.
     * @param int $perPage //To get the list of records available per page. Default value for per page is 200.
     * @return APIResponse
     */
    public function searchRecordsByEmail($email = '', $page = 1, $perPage = 200)
    {
        try {
            $recordOperations = new RecordOperations();
            $paramInstance = new ParameterMap();
            $paramInstance->add(SearchRecordsParam::email(), $email);
            $paramInstance->add(SearchRecordsParam::page(), $page);
            $paramInstance->add(SearchRecordsParam::perPage(), $perPage);
            return $recordOperations->searchRecords($this->moduleApiName, $paramInstance);
        } catch (SDKException $exception) {
            return null;
        }
    }

    /**
     * Add new entities to a module.
     *
     */
    public function insert($record)
    {
        $recordOperations = new RecordOperations();
        $bodyWrapper = new BodyWrapper();
        $records = [$record];
        return $this->moduleIns->createRecords($records)->getData();
    }

    /**
     * @param array $args
     * @return bool
     */
    public function create(array $args = []): bool
    {
        try {
            $recordOperations = new RecordOperations();
            $request = new BodyWrapper();
            $record = $this->getRecordInstance();
            foreach ($args as $key => $value) {
                $record->addFieldValue(new Field($key), $value);
            }
            $request->setData([$record]);
            $request = $recordOperations->createRecords($this->moduleApiName, $request);
            if ($request->getStatusCode() == '201') {
                $successResponse = $request->getObject()->getData()[0];
                return true;
            } else {
                return false;
            }
        } catch (SDKException $exception) {
            info('ZOHO-SDK-ERROR: ' . $exception->getMessage());
            return false;
        }
    }

    /**
     * get record instance
     *
     * @param $recordId
     * @return Record
     */
    public function getRecordInstance($recordId = null): Record
    {
        $record = new Record();
        if ($recordId) {
            $record->setId($recordId);
        }
        return $record;
    }

    public function getFields(): APIResponse
    {
        $fieldOperations = new FieldsOperations($this->moduleApiName);
        $paramInstance = new ParameterMap();
        return $fieldOperations->getFields($paramInstance);
    }

    /**
     * update existing entities in the module.
     *
     * @param ZCRMRecord $record
     * @return ZCRMRecord[]
     */
    public function update($record)
    {
        $records = [];

        array_push($records, $record);
        return $this->moduleIns->updateRecords($records)->getData();
    }

    /**
     * @param CriteriaBuilder $builder
     * @param int $page //to get the list of records from the respective pages. Default value for page is 1.
     * @param int $perPage //To get the list of records available per page. Default value for per page is 200.
     * @return ZCRMRecord[]
     */
    public function search($builder, $page = 1, $perPage = 200)
    {
        if ($builder->toString() !== "") {
            return $this->searchRecordsByCriteria($builder->toString(), $page, $perPage);
        }

        return null;
    }

    /**
     * get module records
     *
     * @param string $criteria //criteria to search for
     * @param int $page //to get the list of records from the respective pages. Default value for page is 1.
     * @param int $perPage //To get the list of records available per page. Default value for per page is 200.
     * @return ZCRMRecord[]
     */
    public function searchRecordsByCriteria($criteria, $page = 1, $perPage = 200)
    {
        $param_map = ["page" => $page, "per_page" => $perPage];
        return $this->moduleIns->searchRecordsByCriteria($criteria, $param_map)->getData();
    }
}
