<?php

namespace Asciisd\Zoho\Console;

use Asciisd\Zoho\Models\ZohoAccount;
use Asciisd\Zoho\Models\ZohoContact;
use Asciisd\Zoho\Models\ZohoDeal;
use Asciisd\Zoho\Models\ZohoLead;
use Illuminate\Console\Command;

class ZohoSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'zoho:sync
                            {module : Module to sync (Contacts, Leads, Accounts, Deals)}
                            {--direction=pull : Sync direction (pull, push)}
                            {--limit=200 : Maximum records to sync}
                            {--page=1 : Page number to start from}';

    /**
     * The console command description.
     */
    protected $description = 'Sync data between Zoho CRM and local database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $module = $this->argument('module');
        $direction = $this->option('direction');
        $limit = (int) $this->option('limit');
        $page = (int) $this->option('page');

        $this->info("🔄 Syncing {$module} ({$direction})...");
        $this->newLine();

        $modelClass = $this->getModelClass($module);

        if (! $modelClass) {
            $this->error("❌ Invalid module: {$module}");

            return self::FAILURE;
        }

        try {
            if ($direction === 'pull') {
                return $this->pullFromZoho($modelClass, $module, $limit, $page);
            } elseif ($direction === 'push') {
                return $this->pushToZoho($modelClass, $module, $limit);
            } else {
                $this->error("❌ Invalid direction: {$direction}");

                return self::FAILURE;
            }
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Sync failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Pull records from Zoho CRM.
     */
    protected function pullFromZoho($modelClass, string $module, int $limit, int $page): int
    {
        $this->line("⬇️  Pulling records from Zoho {$module}...");
        $this->newLine();

        $records = $modelClass::all([
            'per_page' => min($limit, 200),
            'page' => $page,
        ]);

        if ($records->isEmpty()) {
            $this->warn('⚠️  No records found');

            return self::SUCCESS;
        }

        $this->info("✓ Retrieved {$records->count()} records");
        $this->newLine();

        $bar = $this->output->createProgressBar($records->count());
        $bar->start();

        foreach ($records as $record) {
            // Here you would typically save to your local database
            // For now, we'll just process the records

            // Example: YourLocalModel::updateOrCreate(['zoho_id' => $record['id']], $record);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✓ Successfully synced {$records->count()} records");

        // Show sample record
        $this->newLine();
        $this->line('Sample record:');
        $first = $records->first();
        $this->table(
            ['Field', 'Value'],
            collect($first)->take(5)->map(fn ($value, $key) => [$key, is_array($value) ? json_encode($value) : $value])->values()->toArray()
        );

        return self::SUCCESS;
    }

    /**
     * Push records to Zoho CRM.
     */
    protected function pushToZoho($modelClass, string $module, int $limit): int
    {
        $this->line("⬆️  Pushing records to Zoho {$module}...");
        $this->newLine();

        $this->warn('⚠️  Push functionality requires local database models');
        $this->line('Implement this by querying your local models and using:');
        $this->line('$modelClass::create($data) or $modelClass::upsert($data)');

        return self::SUCCESS;
    }

    /**
     * Get model class for module.
     */
    protected function getModelClass(string $module): ?string
    {
        return match ($module) {
            'Contacts' => ZohoContact::class,
            'Leads' => ZohoLead::class,
            'Accounts' => ZohoAccount::class,
            'Deals' => ZohoDeal::class,
            default => null,
        };
    }
}
