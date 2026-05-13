<?php

namespace Asciisd\Zoho\Jobs;

use Asciisd\Zoho\Models\ZohoSync;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncModelToZoho implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [60, 120, 300]; // 1 min, 2 min, 5 min
    }

    /**
     * The model class name.
     */
    public string $modelClass;

    /**
     * The model ID.
     */
    public int|string $modelId;

    /**
     * Create a new job instance.
     */
    public function __construct(
        Model $model,
        public string $operation
    ) {
        $this->modelClass = get_class($model);
        $this->modelId = $model->getKey();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Reconstruct the model from class and ID
            $model = $this->modelClass::find($this->modelId);

            if (! $model) {
                Log::warning('Model not found for Zoho sync', [
                    'model_class' => $this->modelClass,
                    'model_id' => $this->modelId,
                ]);

                return;
            }

            // Check if the model has the required method
            if (! method_exists($model, 'getZohoModule')) {
                Log::warning('Model does not implement SyncsWithZoho trait properly', [
                    'model' => get_class($model),
                    'model_id' => $model->id,
                ]);

                return;
            }

            $zohoModule = $model->getZohoModule();
            $zohoModelClass = $this->resolveZohoModelClass($model, $zohoModule);

            if (! class_exists($zohoModelClass)) {
                throw new \Exception("Zoho model class {$zohoModelClass} not found for module {$zohoModule}. "
                    .'Define getZohoModelClass() on your model, add it to the zoho.modules config, '
                    .'or create a matching ZohoModel class.');
            }

            match ($this->operation) {
                'create' => $this->handleCreate($zohoModelClass, $model),
                'update' => $this->handleUpdate($zohoModelClass, $model),
                'delete' => $this->handleDelete($zohoModelClass, $model),
                default => throw new \Exception("Invalid operation: {$this->operation}"),
            };
        } catch (\Exception $e) {
            Log::error('Zoho sync failed', [
                'model' => $this->modelClass,
                'model_id' => $this->modelId,
                'operation' => $this->operation,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // If this is the final attempt, log it
            if ($this->attempts() >= $this->tries) {
                Log::error('Zoho sync failed after all retries', [
                    'model' => $this->modelClass,
                    'model_id' => $this->modelId,
                    'operation' => $this->operation,
                    'attempts' => $this->attempts(),
                ]);
            }

            throw $e;
        }
    }

    /**
     * Handle create operation.
     */
    protected function handleCreate(string $zohoModelClass, Model $model): void
    {
        $data = $model->transformToZohoData();
        $response = $zohoModelClass::create($data);

        $zohoRecordId = $response['details']['id'] ?? null;

        if ($zohoRecordId) {
            ZohoSync::updateOrCreate(
                [
                    'zohoable_type' => get_class($model),
                    'zohoable_id' => $model->id,
                ],
                [
                    'zoho_module' => $model->getZohoModule(),
                    'zoho_record_id' => $zohoRecordId,
                    'last_synced_at' => now(),
                ]
            );

            Log::info('Zoho record created', [
                'model' => get_class($model),
                'model_id' => $model->id,
                'zoho_record_id' => $zohoRecordId,
            ]);
        }
    }

    /**
     * Handle update operation.
     */
    protected function handleUpdate(string $zohoModelClass, Model $model): void
    {
        $zohoSync = $model->zohoSync;

        if (! $zohoSync || ! $zohoSync->zoho_record_id) {
            // If no Zoho record exists, create it instead
            $this->handleCreate($zohoModelClass, $model);

            return;
        }

        $data = $model->transformToZohoData();
        $response = $zohoModelClass::update($zohoSync->zoho_record_id, $data);

        $zohoSync->update([
            'last_synced_at' => now(),
        ]);

        Log::info('Zoho record updated', [
            'model' => get_class($model),
            'model_id' => $model->id,
            'zoho_record_id' => $zohoSync->zoho_record_id,
        ]);
    }

    /**
     * Handle delete operation.
     */
    protected function handleDelete(string $zohoModelClass, Model $model): void
    {
        $zohoSync = $model->zohoSync;

        if (! $zohoSync || ! $zohoSync->zoho_record_id) {
            Log::info('No Zoho record to delete', [
                'model' => get_class($model),
                'model_id' => $model->id,
            ]);

            return;
        }

        $zohoModelClass::delete($zohoSync->zoho_record_id);

        $zohoSync->delete();

        Log::info('Zoho record deleted', [
            'model' => get_class($model),
            'model_id' => $model->id,
            'zoho_record_id' => $zohoSync->zoho_record_id,
        ]);
    }

    /**
     * Resolve the ZohoModel class for the given model and module.
     *
     * Resolution order:
     * 1. Model's getZohoModelClass() method (explicit override)
     * 2. Config map at zoho.modules.{Module_Name}
     * 3. Naming convention: "Contacts" -> Asciisd\Zoho\Models\ZohoContact
     */
    protected function resolveZohoModelClass(Model $model, string $module): string
    {
        if (method_exists($model, 'getZohoModelClass')) {
            $fromModel = $model->getZohoModelClass();
            if ($fromModel !== null) {
                return $fromModel;
            }
        }

        $fromConfig = config("zoho.modules.{$module}");
        if ($fromConfig !== null) {
            return $fromConfig;
        }

        return $this->guessZohoModelClass($module);
    }

    /**
     * Guess the ZohoModel class from the module name using naming convention.
     *
     * Standard modules: "Contacts" -> ZohoContact, "Leads" -> ZohoLead
     */
    protected function guessZohoModelClass(string $module): string
    {
        $className = 'Zoho'.rtrim($module, 's');

        return "Asciisd\\Zoho\\Models\\{$className}";
    }
}
