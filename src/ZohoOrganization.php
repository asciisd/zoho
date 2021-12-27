<?php


namespace Asciisd\Zoho;


use zcrmsdk\crm\setup\org\ZCRMOrganization;
use zcrmsdk\crm\setup\restclient\ZCRMRestClient;
use zcrmsdk\crm\setup\users\ZCRMUser;

class ZohoOrganization
{
    protected array $zohoOptions;

    /**
     * ZohoOrganization constructor.
     */
    public function __construct(array $zohoOptions)
    {
        $this->zohoOptions = $zohoOptions;
    }

    /**
     * get the organization in form of ZCRMOrganization instance
     *
     * @return ZCRMOrganization|object
     */
    public function getOrganizationDetails()
    {

        return $this->rest->getOrganizationInstance();
    }

    /**
     * get dummy organization object
     *
     * @return ZCRMOrganization
     */
    public function getOrganizationInstance()
    {
        return $this->rest->getOrganizationInstance();
    }

    /**
     * get the users in form of ZCRMUser instances array
     *
     * @return ZCRMUser|object
     */
    public function getCurrentUser()
    {
        return $this->rest->getCurrentUser()->getData();
    }
}
