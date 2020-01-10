<?php


namespace Asciisd\Zoho\Traits;


use zcrmsdk\crm\setup\org\ZCRMOrganization;
use zcrmsdk\crm\setup\users\ZCRMUser;

trait ZohoOrganization
{

    public function currentOrg()
    {
        return $this;
    }

    /**
     * get the organization in form of ZCRMOrganization instance
     *
     * @return ZCRMOrganization|object
     */
    public function getOrganizationDetails()
    {
        return $this->rest->getOrganizationDetails()->getData();
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
