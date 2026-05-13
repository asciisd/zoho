<?php

namespace Asciisd\Zoho\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ZohoSync extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'zoho_syncs';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'zohoable_type',
        'zohoable_id',
        'zoho_module',
        'zoho_record_id',
        'last_synced_at',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
        ];
    }

    /**
     * Get the parent zohoable model.
     */
    public function zohoable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include syncs for a specific module.
     */
    public function scopeForModule($query, string $module)
    {
        return $query->where('zoho_module', $module);
    }

    /**
     * Scope a query to only include syncs with a specific Zoho record ID.
     */
    public function scopeWithZohoRecordId($query, string $recordId)
    {
        return $query->where('zoho_record_id', $recordId);
    }

    /**
     * Scope a query to only include syncs that have been synced.
     */
    public function scopeSynced($query)
    {
        return $query->whereNotNull('zoho_record_id');
    }

    /**
     * Scope a query to only include syncs that have not been synced yet.
     */
    public function scopeNotSynced($query)
    {
        return $query->whereNull('zoho_record_id');
    }
}
