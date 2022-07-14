<?php

namespace Asciisd\Zoho;

use Illuminate\Database\Eloquent\Model;
use Asciisd\Zoho\Traits\Zohoable as ZohoableModel;
use Asciisd\Zoho\Contracts\Repositories\ZohoableRepository;

abstract class Zohoable extends Model implements ZohoableRepository
{
    use ZohoableModel;

    protected $zoho_module_name;
    protected $zoho_module;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->zoho_module = $this->getZohoModule();
    }
}
