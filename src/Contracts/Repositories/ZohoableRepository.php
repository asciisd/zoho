<?php

namespace Asciisd\Zoho\Contracts\Repositories;

use Asciisd\Zoho\CriteriaBuilder;

interface ZohoableRepository
{
    /**
     * This used when we need to search for your current model record on zoho
     *
     * @return String|CriteriaBuilder
     */
    public function searchCriteria();

    /**
     * Array for mandatory fields that required to create new record
     *
     * @return array
     */
    public function zohoMandatoryFields();
}
