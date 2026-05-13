<?php

namespace Asciisd\Zoho\Console;

use Asciisd\Zoho\Models\ZohoAccount;
use Asciisd\Zoho\Models\ZohoContact;
use Asciisd\Zoho\Models\ZohoDeal;
use Asciisd\Zoho\Models\ZohoLead;
use Illuminate\Console\Command;

class ZohoTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'zoho:test
                            {module=Contacts : Module to test (Contacts, Leads, Accounts, Deals)}
                            {--operation=all : Operation to test (create, read, update, delete, all)}';

    /**
     * The console command description.
     */
    protected $description = 'Test Zoho CRM CRUD operations';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $module = $this->argument('module');
        $operation = $this->option('operation');

        $this->info("🧪 Testing {$module} module...");
        $this->newLine();

        $modelClass = $this->getModelClass($module);

        if (! $modelClass) {
            $this->error("❌ Invalid module: {$module}");

            return self::FAILURE;
        }

        // Clear field cache to ensure fresh field metadata
        $modelClass::clearFieldCache();

        try {
            if ($operation === 'all' || $operation === 'create') {
                $this->testCreate($modelClass, $module);
            }

            if ($operation === 'all' || $operation === 'read') {
                $this->testRead($modelClass, $module);
            }

            if ($operation === 'all' || $operation === 'search') {
                $this->testSearch($modelClass, $module);
            }

            if ($operation === 'all' || $operation === 'count') {
                $this->testCount($modelClass, $module);
            }

            $this->newLine();
            $this->info('✓ All tests completed successfully!');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Test failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Test create operation.
     */
    protected function testCreate($modelClass, string $module): void
    {
        $this->line('📝 Testing CREATE operation...');

        $data = $this->getTestData($module);
        $record = $modelClass::create($data);

        $this->info('✓ Record created successfully!');
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $record['details']['id'] ?? $record['id'] ?? 'N/A'],
                ['Status', $record['status'] ?? 'success'],
            ]
        );
        $this->newLine();
    }

    /**
     * Test read operation.
     */
    protected function testRead($modelClass, string $module): void
    {
        $this->line('📖 Testing READ operation...');

        $records = $modelClass::all(['per_page' => 5]);

        $this->info("✓ Retrieved {$records->count()} records");

        if ($records->isNotEmpty()) {
            $first = $records->first();
            $this->table(
                ['Field', 'Value'],
                [
                    ['ID', $first['id'] ?? 'N/A'],
                    ['Name', $first['Full_Name'] ?? $first['Account_Name'] ?? $first['Deal_Name'] ?? $first['Company'] ?? 'N/A'],
                    ['Created Time', $first['Created_Time'] ?? 'N/A'],
                ]
            );
        }

        $this->newLine();
    }

    /**
     * Test search operation.
     */
    protected function testSearch($modelClass, string $module): void
    {
        $this->line('🔍 Testing SEARCH operation...');

        $criteria = match ($module) {
            'Contacts' => '(Last_Name:starts_with:Test)',
            'Leads' => '(Last_Name:starts_with:Test)',
            'Accounts' => '(Account_Name:starts_with:Test)',
            'Deals' => '(Deal_Name:starts_with:Test)',
            default => '(id:equals:0)',
        };

        $records = $modelClass::search($criteria);

        $this->info("✓ Search returned {$records->count()} records");
        $this->newLine();
    }

    /**
     * Test count operation.
     */
    protected function testCount($modelClass, string $module): void
    {
        $this->line('🔢 Testing COUNT operation...');

        $count = $modelClass::count();

        $this->info("✓ Total records: {$count}");
        $this->newLine();
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

    /**
     * Get test data for module.
     */
    protected function getTestData(string $module): array
    {
        $timestamp = time();

        return match ($module) {
            'Contacts' => [
                'First_Name' => 'Test',
                'Last_Name' => "Contact {$timestamp}",
                'Email' => "test.contact.{$timestamp}@example.com",
                'Phone' => substr($timestamp, -10), // Use last 10 digits of timestamp as phone
            ],
            'Leads' => [
                'First_Name' => 'Test',
                'Last_Name' => "Lead {$timestamp}",
                'Email' => "test.lead.{$timestamp}@example.com",
                'Company' => "Test Company {$timestamp}",
            ],
            'Accounts' => [
                'Account_Name' => "Test Account {$timestamp}",
                'Phone' => substr($timestamp, -10), // Use last 10 digits of timestamp as phone
                'Website' => 'https://example.com',
            ],
            'Deals' => [
                'Deal_Name' => "Test Deal {$timestamp}",
                'Stage' => 'Qualification',
                'Amount' => 10000,
            ],
            default => [],
        };
    }
}
