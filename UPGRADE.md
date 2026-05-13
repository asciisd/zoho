# Upgrading from asciisd/zoho v2.x to v8.0.0

This document describes the breaking changes when upgrading from `asciisd/zoho` v2.x to v8.0.0. Version 8.0.0 is a complete rewrite targeting Zoho CRM API v8.

## Requirements

| | v2.x | v8.0.0 |
|--|------|--------|
| PHP | >= 7.2 | >= 8.4 |
| Laravel | >= 6.0 | >= 11.0 |
| Zoho CRM API | v2 | v8 |

## Installation

```bash
# Remove old package (if needed)
composer remove asciisd/zoho

# Install new version
composer require asciisd/zoho:^8.0
```

Publish the new configuration and migrations:

```bash
php artisan vendor:publish --tag=zoho-config --force
php artisan vendor:publish --tag=zoho-migrations
php artisan migrate
```

## Namespace

The namespace remains `Asciisd\Zoho`, but all classes have been rewritten. Update any `use` statements to reference the new class structure:

```php
// v2.x
use Asciisd\Zoho\Facades\ZohoManager;
use Asciisd\Zoho\Zohoable;
use Asciisd\Zoho\CriteriaBuilder;

// v8.0.0
use Asciisd\Zoho\Facades\Zoho;
use Asciisd\Zoho\Traits\SyncsWithZoho;
use Asciisd\Zoho\Models\ZohoContact;
use Asciisd\Zoho\Models\ZohoLead;
// etc.
```

## Facade Changes

The `ZohoManager` facade has been replaced with the `Zoho` facade:

```php
// v2.x
use Asciisd\Zoho\Facades\ZohoManager;
$leads = ZohoManager::useModule('Leads');
$lead = $leads->getRecord('123');

// v8.0.0
use Asciisd\Zoho\Facades\Zoho;
$lead = Zoho::leads()->find('123');

// Or use static model methods directly:
use Asciisd\Zoho\Models\ZohoLead;
$lead = ZohoLead::find('123');
```

## CRUD Operations

### Create

```php
// v2.x
$leads = ZohoManager::useModule('Leads');
$record = $leads->getRecordInstance();
$record->setFieldValue('First_Name', 'John');
$record->setFieldValue('Last_Name', 'Doe');
$lead = $record->create()->getData();

// v8.0.0
$lead = ZohoLead::create([
    'First_Name' => 'John',
    'Last_Name' => 'Doe',
]);
```

### Read

```php
// v2.x
$lead = $leads->getRecord('3582074000002383003');

// v8.0.0
$lead = ZohoLead::find('3582074000002383003');
```

### Update

```php
// v2.x
$lead = $leads->getRecord('123');
$lead->setFieldValue('Last_Name', 'Smith');
$lead->update()->getData();

// v8.0.0
ZohoLead::update('123', ['Last_Name' => 'Smith']);
```

### Delete

```php
// v2.x
$lead = $leads->getRecord('123');
$lead->delete();

// v8.0.0
ZohoLead::delete('123');
```

### Search

```php
// v2.x
$records = ZohoManager::useModule('Leads')->searchRecordsByEmail('test@example.com');
$records = ZohoManager::useModule('Leads')->searchRecordsByCriteria('(City:equals:NY)');
$records = ZohoManager::useModule('Leads')
    ->where('City', 'NY')
    ->andWhere('State', 'Alden')
    ->search();

// v8.0.0
$records = ZohoLead::searchByEmail('test@example.com');
$records = ZohoLead::search('(City:equals:NY)');
$records = ZohoLead::search('(City:equals:NY) and (State:equals:Alden)');
```

## Model Sync (Zohoable -> SyncsWithZoho)

The `Zohoable` abstract model has been replaced with a composable `SyncsWithZoho` trait:

```php
// v2.x
use Asciisd\Zoho\Zohoable;
use Asciisd\Zoho\CriteriaBuilder;

class Invoice extends Zohoable
{
    protected $zoho_module_name = 'Invoices';

    public function searchCriteria()
    {
        return CriteriaBuilder::where('Invoice_ID', $this->id)->toString();
    }

    public function zohoMandatoryFields()
    {
        return ['Subject' => $this->subject, 'Amount' => $this->amount];
    }
}

// Usage
$invoice->createOrUpdateZohoId();
$invoice->createAsZohoable();

// v8.0.0
use Asciisd\Zoho\Traits\SyncsWithZoho;

class Invoice extends Model
{
    use SyncsWithZoho;

    protected $fillable = ['subject', 'amount', 'email'];

    protected function getZohoModule(): string
    {
        return 'Invoices';
    }

    protected function getZohoFieldMapping(): array
    {
        return [
            'subject' => 'Subject',
            'amount' => 'Amount',
            'email' => 'Email',
        ];
    }
}

// Usage — sync happens automatically on create/update/delete via queued jobs
$invoice = Invoice::create(['subject' => 'Test', 'amount' => 100]);

// Access stored Zoho record ID
$invoice->getZohoRecordId();
```

Key differences:
- **Automatic sync**: No manual `createAsZohoable()` calls needed — model events trigger sync automatically
- **Queued**: Sync jobs run in the background with 3 automatic retries
- **No search criteria**: The trait handles create/update/delete; searching is done via ZohoModel classes directly
- **Polymorphic storage**: Zoho record IDs stored in `zoho_syncs` table (replaces `zohos` table)

## Configuration

### Environment Variables

```env
# v2.x
ZOHO_CLIENT_ID=
ZOHO_CLIENT_SECRET=
ZOHO_REDIRECT_URI=
ZOHO_CURRENT_USER_EMAIL=
ZOHO_ACCOUNTS_URL=https://accounts.zoho.eu    # for EU
ZOHO_API_BASE_URL=www.zohoapis.eu             # for EU

# v8.0.0
ZOHO_CLIENT_ID=
ZOHO_CLIENT_SECRET=
ZOHO_REDIRECT_URI=https://yourapp.com/zoho/callback
ZOHO_DATA_CENTER=US                           # US, EU, IN, CN, JP, AU, CA
ZOHO_ENVIRONMENT=production                   # production, sandbox, developer
ZOHO_TOKEN_STORAGE=both                       # cache, database, both
ZOHO_SYNC_ENABLED=true
ZOHO_SYNC_QUEUE=default
```

Removed variables:
- `ZOHO_CURRENT_USER_EMAIL` — no longer needed
- `ZOHO_ACCOUNTS_URL` — replaced by `ZOHO_DATA_CENTER`
- `ZOHO_API_BASE_URL` — replaced by `ZOHO_DATA_CENTER`

## Artisan Commands

| v2.x | v8.0.0 | Notes |
|-------|--------|-------|
| `zoho:install` | `vendor:publish --tag=zoho-config` | Config publishing |
| `zoho:authentication` | `zoho:setup` | Interactive OAuth setup |
| — | `zoho:auth status/url/refresh/revoke` | Auth management |
| — | `zoho:test Contacts` | CRUD testing |
| — | `zoho:sync Contacts --direction=pull` | Data sync |
| — | `zoho:token:refresh` | Token refresh |

## Database Migration

The old `zohos` table is replaced by `zoho_syncs` and `zoho_oauth_tokens` tables. If you have existing data in the `zohos` table, you'll need to migrate it manually:

```php
// Example migration to move data from old zohos table to new zoho_syncs table
Schema::table('zoho_syncs', function (Blueprint $table) {
    // The new table uses:
    // - zohoable_type (polymorphic model class)
    // - zohoable_id (model ID)
    // - zoho_module (e.g. 'Contacts')
    // - zoho_record_id (the Zoho CRM record ID)
    // - last_synced_at (timestamp)
});
```

## Removed Features

- `CriteriaBuilder` class — use criteria strings directly: `'(Field:equals:Value)'`
- `$zoho_module_name` property — replaced by `getZohoModule()` method
- `zohoMandatoryFields()` method — replaced by `getZohoFieldMapping()`
- `searchCriteria()` method — no longer needed (sync is event-based)
- `hasZohoId()` / `zohoId()` methods — replaced by `getZohoRecordId()`
- `createOrUpdateZohoId()` — replaced by automatic event-driven sync
- `createAsZohoable()` — replaced by automatic event-driven sync

## Getting Help

If you encounter issues during migration:

1. Check the [README](README.md) for complete documentation
2. Open an issue on [GitHub](https://github.com/asciisd/zoho/issues)
3. Contact support at aemaddin@gmail.com
