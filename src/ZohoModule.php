<?php

namespace Asciisd\Zoho;

use zcrmsdk\crm\crud\ZCRMModule;
use zcrmsdk\crm\crud\ZCRMRecord;
use zcrmsdk\crm\setup\restclient\ZCRMRestClient;

class ZohoModule
{
    protected $rest;
    protected $module_api_name;
    protected $moduleIns;
    protected $criteria = "";
    protected $operators = ['equals', 'starts_with'];

    /**
     * ZohoModule constructor.
     *
     * @param ZCRMRestClient $rest
     * @param $module_api_name
     */
    public function __construct($rest, $module_api_name)
    {
        $this->rest = $rest;
        $this->module_api_name = $module_api_name;
        $this->moduleIns = $this->getModuleInstance();
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
        return $this->moduleIns->getRecords()->getData();
    }

    /**
     * get the record object of given module api name and record id
     *
     * @param string $record_id
     * @return object|ZCRMRecord
     */
    public function getRecord($record_id)
    {
        return $this->moduleIns->getRecord($record_id)->getData();
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
        return $this->moduleIns->searchRecordsByWord($word, $param_map)->getData();
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
        return $this->moduleIns->searchRecordsByPhone($phone, $param_map)->getData();
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
        return $this->moduleIns->searchRecordsByEmail($email, $param_map)->getData();
    }

    /**
     * get module records
     *
     * @param string $criteria //criteria to search for
     * @param int $page //to get the list of records from the respective pages. Default value for page is 1.
     * @param int $perPage //To get the list of records available per page. Default value for per page is 200.
     * @return ZCRMRecord[]
     */
    public function searchRecordsByCriteria($criteria = "", $page = 1, $perPage = 200)
    {
        $param_map = ["page" => $page, "per_page" => $perPage];
        return $this->moduleIns->searchRecordsByCriteria($criteria, $param_map)->getData();
    }

    /**
     * Add new entities to a module.
     *
     * @param ZCRMRecord $record
     * @return ZCRMRecord[]
     */
    public function insert($record)
    {
        $records = [];

        array_push($records, $record);
        return $this->moduleIns->createRecords($records)->getData();
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
     * @param int $page //to get the list of records from the respective pages. Default value for page is 1.
     * @param int $perPage //To get the list of records available per page. Default value for per page is 200.
     * @return ZCRMRecord[]
     */
    public function search($page = 1, $perPage = 200)
    {
        if ($this->criteria !== "") {
            return $this->searchRecordsByCriteria($this->criteria);
        }

        return null;
    }

    /**
     * add criteria to the search
     *
     * @param $field
     * @param $value
     * @param string $operator
     * @return $this
     */
    public function where($field, $value, $operator = 'equals')
    {
        $this->criteria = "";

        $this->criteria .= $this->queryBuilder($field, $operator, $value);

        return $this;
    }

    public function andWhere($field, $value, $operator = 'equals')
    {
        $this->criteria .= ' and ' . $this->queryBuilder($field, $operator, $value);

        return $this;
    }

    public function orWhere($field, $value, $operator = 'equals')
    {
        $this->criteria .= ' or ' . $this->queryBuilder($field, $operator, $value);

        return $this;
    }

    private function queryBuilder(...$args)
    {
        return "($args[0]:$args[1]:$args[2])";
    }
}
