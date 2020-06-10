<?php

namespace Asciisd\Zoho\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;

class InvalidZohoable extends Exception
{
    /**
     * Create a new InvalidTapCustomer instance.
     *
     * @param Model $owner
     * @return static
     */
    public static function nonZohoable($owner)
    {
        return new static(class_basename($owner) . ' is not a Zohoable. See the createAsZohoable method.');
    }

    /**
     * Create a new InvalidTapCustomer instance.
     *
     * @param Model $owner
     * @return static
     */
    public static function exists($owner)
    {
        return new static(class_basename($owner) . " is already a Zohoable with ID {$owner->zohoId()}");
    }
}
