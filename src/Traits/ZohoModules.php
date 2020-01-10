<?php

namespace Asciisd\Zoho\Traits;

use zcrmsdk\crm\crud\ZCRMModule;
use zcrmsdk\crm\crud\ZCRMRecord;

trait ZohoModules
{
    protected $module_api_name;
    protected $moduleIns;

    public function useModule($module_api_name = 'leads')
    {
        $this->module_api_name = $module_api_name;
        $this->moduleIns = $this->getModuleInstance();

        return $this;
    }

    /**
     * to get the the modules in form of ZCRMModule instances array
     *
     * @return ZCRMModule[]
     */
    public function getAllModules()
    {
        return $this->rest->getAllModules()->getData();
    }

    /**
     * to get the module in form of ZCRMModule instance
     *
     * @return ZCRMModule|object
     */
    public function getModule()
    {
        return $this->rest->getModule($this->module_api_name)->getData();
    }

    /**
     * get record instance
     *
     * @param $record_id
     * @return ZCRMRecord
     */
    public function getRecordInstance($record_id = null)
    {
        return $this->rest->getRecordInstance($this->module_api_name, $record_id);
    }

    /**
     * get dummy module object
     *
     * @return ZCRMModule
     */
    public function getModuleInstance()
    {
        return $this->rest->getModuleInstance($this->module_api_name);
    }

    /**
     * get the records array of given module api name
     *
     * @return ZCRMRecord[]
     */
    public function getRecords()
    {
        $response = $this->moduleIns->getRecords();
        return $response->getData();
    }

    /**
     * get the record object of given module api name and record id
     *
     * @param string $record_id
     * @return ZCRMRecord
     */
    public function getRecord($record_id)
    {
        $response = $this->moduleIns->getRecord($record_id);
        return $response->getData();
    }

    /**
     * get module records
     *
     * @param string $word //word to be searched
     * @param int $page //to get the list of records from the respective pages. Default value for page is 1.
     * @param int $perPage //To get the list of records available per page. Default value for per page is 200.
     * @return ZCRMRecord[]
     */
    public function searchRecordsByWord($word = '', $page = 1, $perPage = 200)
    {
        $param_map = ["page" => $page, "per_page" => $perPage];
        $response = $this->moduleIns->searchRecordsByWord($word, $param_map);
        return $response->getData();
    }

    /**
     * get module records
     *
     * @param string $phone //phone to be searched
     * @param int $page //to get the list of records from the respective pages. Default value for page is 1.
     * @param int $perPage //To get the list of records available per page. Default value for per page is 200.
     * @return ZCRMRecord[]
     */
    public function searchRecordsByPhone($phone = '', $page = 1, $perPage = 200)
    {
        $param_map = ["page" => $page, "per_page" => $perPage];
        $response = $this->moduleIns->searchRecordsByPhone($phone, $param_map);
        return $response->getData();
    }

    /**
     * get module records
     *
     * @param string $email //email to be searched
     * @param int $page //to get the list of records from the respective pages. Default value for page is 1.
     * @param int $perPage //To get the list of records available per page. Default value for per page is 200.
     * @return ZCRMRecord[]
     */
    public function searchRecordsByEmail($email = '', $page = 1, $perPage = 200)
    {
        $param_map = ["page" => $page, "per_page" => $perPage];
        $response = $this->moduleIns->searchRecordsByEmail($email, $param_map);
        return $response->getData();
    }
}
