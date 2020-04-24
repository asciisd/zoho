<?php

namespace Asciisd\Zoho\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;

class InvalidLead extends Exception
{
    /**
     * Create a new InvalidZohoLead instance.
     *
     * @param Model $owner
     * @return static
     */
    public static function nonLead($owner)
    {
        return new static(class_basename($owner) . ' is not a Zoho lead. See the createAsZohoLead method.');
    }

    /**
     * Create a new InvalidZohoLead instance.
     *
     * @param Model $owner
     * @return static
     */
    public static function exists($owner)
    {
        return new static(class_basename($owner) . " is already a Zoho lead with ID {$owner->lead_id}.");
    }
}
