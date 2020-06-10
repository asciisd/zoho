<?php

namespace Asciisd\Zoho\Models;

use Illuminate\Database\Eloquent\Model;

class Zoho extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'zohoable_type', 'zohoable_id', 'zoho_id'
    ];

    /**
     * Get the owning zohoable model.
     */
    public function zohoable()
    {
        return $this->morphTo();
    }
}
