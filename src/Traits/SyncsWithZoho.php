<?php

namespace Asciisd\Zoho\Traits;

use Asciisd\Zoho\Jobs\SyncModelToZoho;
use Asciisd\Zoho\Models\ZohoSync;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait SyncsWithZoho
{
    /**
     * Track if syncing is disabled.
     */
    protected static bool $zohoSyncDisabled = false;

    /**
     * Boot the trait and register model observers.
     */
    protected static function bootSyncsWithZoho(): void
    {
        static::created(function ($model) {
            if (! static::$zohoSyncDisabled && $model->shouldSyncToZoho()) {
                $model->dispatchZohoSync('create');
            }
        });

        static::updated(function ($model) {
            if (! static::$zohoSyncDisabled && $model->shouldSyncToZoho()) {
                $model->dispatchZohoSync('update');
            }
        });

        static::deleted(function ($model) {
            if (! static::$zohoSyncDisabled && $model->shouldSyncToZoho()) {
                $model->dispatchZohoSync('delete');
            }
        });
    }

    /**
     * Get the Zoho module name for this model.
     * Must be implemented by the model using this trait.
     */
    abstract public function getZohoModule(): string;

    /**
     * Get the ZohoModel class for this model's module.
     * Override this to point to a custom ZohoModel subclass.
     * Returns null to fall back to config map or naming convention.
     */
    public function getZohoModelClass(): ?string
    {
        return null;
    }

    /**
     * Get the field mapping between model attributes and Zoho fields.
     * Override this method to provide custom field mapping.
     */
    public function getZohoFieldMapping(): array
    {
        return [];
    }

    /**
     * Determine if this model should sync to Zoho.
     * Override this method to add conditional logic.
     */
    protected function shouldSyncToZoho(): bool
    {
        // Check if sync is globally enabled
        if (! config('zoho.sync.enabled', true)) {
            return false;
        }

        return true;
    }

    /**
     * Get the polymorphic relationship to ZohoSync.
     */
    public function zohoSync(): MorphOne
    {
        return $this->morphOne(ZohoSync::class, 'zohoable');
    }

    /**
     * Dispatch the sync job to the queue.
     */
    protected function dispatchZohoSync(string $operation): void
    {
        $queueName = config('zoho.sync.queue', 'default');

        SyncModelToZoho::dispatch($this, $operation)
            ->onQueue($queueName);
    }

    /**
     * Sync this model to Zoho immediately (non-queued).
     */
    public function syncToZohoNow(string $operation = 'create'): void
    {
        SyncModelToZoho::dispatchSync($this, $operation);
    }

    /**
     * Get the Zoho record ID for this model.
     */
    public function getZohoRecordId(): ?string
    {
        return $this->zohoSync?->zoho_record_id;
    }

    /**
     * Get fields to exclude from Zoho sync.
     * Override this method to specify fields that should not be sent to Zoho.
     */
    public function getExcludedZohoFields(): array
    {
        return ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];
    }

    /**
     * Transform model data to Zoho format using field mapping.
     */
    public function transformToZohoData(): array
    {
        $mapping = $this->getZohoFieldMapping();
        $excluded = $this->getExcludedZohoFields();
        $data = [];

        if (empty($mapping)) {
            // No mapping provided, use all fillable attributes as-is (excluding sensitive fields)
            $fillable = array_diff($this->getFillable(), $excluded);
            $data = $this->only($fillable);
        } else {
            // Apply custom mapping
            foreach ($mapping as $modelField => $zohoField) {
                if (! in_array($modelField, $excluded) && isset($this->$modelField)) {
                    $data[$zohoField] = $this->$modelField;
                }
            }

            // Add unmapped fillable fields with the same name (excluding sensitive fields)
            foreach ($this->getFillable() as $field) {
                if (! isset($mapping[$field]) && ! in_array($field, $excluded) && isset($this->$field)) {
                    $data[$field] = $this->$field;
                }
            }
        }

        return $data;
    }

    /**
     * Execute a callback without triggering Zoho sync.
     */
    public static function withoutZohoSync(callable $callback): mixed
    {
        $previousValue = static::$zohoSyncDisabled;
        static::$zohoSyncDisabled = true;

        try {
            return $callback();
        } finally {
            static::$zohoSyncDisabled = $previousValue;
        }
    }

    /**
     * Check if Zoho sync is currently disabled.
     */
    public static function isZohoSyncDisabled(): bool
    {
        return static::$zohoSyncDisabled;
    }
}
