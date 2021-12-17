<?php

namespace Asciisd\Zoho\Traits;

use Asciisd\Zoho\Exceptions\InvalidZohoable;
use Asciisd\Zoho\Facades\ZohoManager;
use Asciisd\Zoho\Models\Zoho as ZohoModel;
use Asciisd\Zoho\ZohoModule;
use Exception;
use Illuminate\Support\Str;
use zcrmsdk\crm\crud\ZCRMRecord;
use zcrmsdk\crm\exception\ZCRMException;

trait Zohoable
{
    public function zoho()
    {
        return $this->morphOne(ZohoModel::class, 'zohoable');
    }

    /**
     * Retrieve the Zoho ID for current model item.
     *
     * @return string|null
     */
    public function zohoId()
    {
        if (!$this->zoho) return null;

        return $this->zoho->zoho_id;
    }

    /**
     * Determine if the entity has a Zoho ID.
     *
     * @return bool
     */
    public function hasZohoId()
    {
        return !is_null($this->zohoId());
    }

    /**
     * create or update the current model with zoho id
     *
     * @param null $id
     * @return mixed
     * @throws Exception
     */
    public function createOrUpdateZohoId($id = null)
    {
        try {
            return $this->createZohoId($id);
        } catch (InvalidZohoable $e) {
            try {
                return $this->updateZohoId($id);
            } catch (InvalidZohoable $e) {
                throw new Exception('something went wrong!');
            }
        }
    }

    /**
     * Update current zoho id for this model
     *
     * @param null $id
     * @return mixed
     * @throws InvalidZohoable
     */
    public function updateZohoId($id = null)
    {
        if (!$this->hasZohoId()) {
            throw InvalidZohoable::nonZohoable($this);
        }

        if (!$id) {
            $id = $this->findByCriteria()->getEntityId();
        }

        $this->zoho()->update(['zoho_id' => $id]);
        return $this->load('zoho');
    }

    /**
     * create zoho id from $id or search on zoho by criteria
     *
     * @param null $id
     * @return mixed
     * @throws InvalidZohoable
     */
    public function createZohoId($id = null)
    {
        if ($this->hasZohoId()) {
            throw InvalidZohoable::exists($this);
        }

        if (!$id) {
            $id = $this->findByCriteria()->getEntityId();
        }

        $this->zoho()->create(['zoho_id' => $id]);
        return $this->load('zoho');
    }

    /**
     * delete zoho id from this model
     *
     * @return mixed
     * @throws InvalidZohoable
     */
    public function deleteZohoId()
    {
        if (!$this->hasZohoId()) {
            throw InvalidZohoable::nonZohoable($this);
        }

        $this->zoho()->delete();
        return $this->load('zoho');
    }

    protected function findByCriteria()
    {
        if ($this->searchCriteria() == '') return null;

        return last(
            $this->zoho_module->searchRecordsByCriteria(
                $this->searchCriteria()
            )
        );
    }

    /**
     * Create a Zoho record for the given model.
     *
     * @param array $options
     * @return object
     * @throws InvalidZohoable
     */
    public function createAsZohoable(array $options = [])
    {
        if ($this->zohoId()) {
            throw InvalidZohoable::exists($this);
        }

        $options = array_merge($this->zohoMandatoryFields(), $options);

        // Here we will create the ZCRMRecord instance on Zoho and store the ID of the
        // record from Zoho. This ID will correspond with the Zoho record instance
        // and allow us to retrieve records from Zoho later when we need to work.
        $record = $this->zoho_module->create($options);

        $this->createZohoId($record->getEntityId());

        return $record;
    }

    /**
     * Update Zoho record for the given model.
     *
     * @param array $options
     * @return object
     * @throws InvalidZohoable
     */
    public function updateZohoable(array $options = [])
    {
        if (!$this->hasZohoId()) {
            throw InvalidZohoable::nonZohoable($this);
        }

        $options = array_merge($this->zohoMandatoryFields(), $options);

        // Here we will create the ZCRMRecord instance on Zoho and store the ID of the
        // record from Zoho. This ID will correspond with the Zoho record instance
        // and allow us to retrieve records from Zoho later when we need to work.
        $record = $this->asZohoObject();

        foreach($options as $key => $value) {
            $record->setFieldValue($key, $value);
        }

        $isUpdated = $record->update();

        if($isUpdated) {
            $this->createOrUpdateZohoId($record->getEntityId());
        }

        return $record;
    }

    /**
     * Find and delete zoho record and remove zoho_id from model
     *
     * @return mixed
     * @throws InvalidZohoable
     * @throws ZCRMException
     */
    public function deleteZohoable()
    {
        $this->asZohoObject()->delete();
        return $this->deleteZohoId();
    }

    /**
     * Get the zoho module name associated with the model.
     *
     * @return string
     */
    public function getZohoModuleName()
    {
        return $this->zoho_module_name ?? Str::snake(Str::pluralStudly(class_basename($this)));
    }

    /**
     * Get the Zoho Module for this model
     *
     * @return ZohoModule
     */
    public function getZohoModule()
    {
        return ZohoManager::useModule($this->getZohoModuleName());
    }

    /**
     * get zoho object by the module id
     *
     * @return object|ZCRMRecord
     */
    public function asZohoObject()
    {
        return $this->findByZohoId($this->zohoId());
    }

    public function findByZohoEmail($email)
    {
        return last($this->zoho_module->searchRecordsByEmail($email));
    }

    /**
     * find record by it's ID
     *
     * @param $id
     * @return object|ZCRMRecord
     */
    public function findByZohoId($id)
    {
        return $this->zoho_module->getRecord($id);
    }

    /**
     * Determine if the entity has a Zoho ID and throw an exception if not.
     *
     * @return void
     * @throws InvalidZohoable
     */
    protected function assertZohoableExists()
    {
        if (!$this->zohoId()) {
            throw InvalidZohoable::nonZohoable($this);
        }
    }
}
