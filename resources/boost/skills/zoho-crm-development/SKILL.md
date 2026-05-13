---
name: zoho-crm-development
description: Build and work with Zoho CRM integration features, including module CRUD operations, Eloquent model sync, OAuth authentication, webhooks, and search.
---

# Zoho CRM Development

## When to use this skill

Use this skill when:

- Creating, reading, updating, or deleting Zoho CRM records
- Setting up Eloquent model sync with Zoho CRM modules
- Configuring OAuth authentication for Zoho CRM
- Handling Zoho CRM webhooks
- Searching or querying Zoho CRM data
- Working with related records across Zoho CRM modules
- Performing bulk operations on Zoho CRM records

## Package Structure

```
Asciisd\Zoho\
├── Facades\Zoho              # Facade for ZohoClient
├── ZohoClient                # Factory for module instances
├── Models\
│   ├── ZohoModel             # Abstract base — all CRUD/search/upsert methods
│   ├── ZohoContact           # Contacts module
│   ├── ZohoAccount           # Accounts module
│   ├── ZohoLead              # Leads module
│   ├── ZohoDeal              # Deals module
│   ├── ZohoTask              # Tasks module
│   ├── ZohoEvent             # Events module
│   ├── ZohoCall              # Calls module
│   ├── ZohoNote              # Notes module
│   ├── ZohoProduct           # Products module
│   ├── ZohoInvoice           # Invoices module
│   ├── ZohoOAuthToken        # Eloquent model for stored tokens
│   └── ZohoSync              # Polymorphic sync tracking model
├── Auth\OAuthManager          # OAuth flow and token lifecycle
├── Storage\TokenStorage       # Token persistence (cache/database)
├── Traits\SyncsWithZoho       # Auto-sync Eloquent models to Zoho
├── Jobs\SyncModelToZoho       # Queued sync job
├── Events\                    # Webhook events
├── Exceptions\                # Typed exceptions
└── Http\Controllers\ZohoWebhookController
```

## Accessing Modules

Always use the `Zoho` facade to access CRM modules:

```php
use Asciisd\Zoho\Facades\Zoho;

$contacts = Zoho::contacts();
$leads    = Zoho::leads();
$deals    = Zoho::deals();
$accounts = Zoho::accounts();
$tasks    = Zoho::tasks();
$events   = Zoho::events();
$calls    = Zoho::calls();
$notes    = Zoho::notes();
$products = Zoho::products();
$invoices = Zoho::invoices();
```

You can also use module classes directly:

```php
use Asciisd\Zoho\Models\ZohoContact;

$contact = ZohoContact::find('record_id');
```

## CRUD Operations

Every module model inherits these static methods from `ZohoModel`:

```php
// Create a record
$result = Zoho::contacts()->create([
    'First_Name' => 'Jane',
    'Last_Name'  => 'Doe',
    'Email'      => 'jane@example.com',
    'Phone'      => '+1234567890',
]);

// Find a record by ID
$contact = Zoho::contacts()->find('5344xxxxxxxxxxxx');

// Get all records (paginated)
$contacts = Zoho::contacts()->all();
$contacts = Zoho::contacts()->all(['per_page' => 50]);

// Update a record
$result = Zoho::contacts()->update('5344xxxxxxxxxxxx', [
    'Phone' => '+0987654321',
]);

// Delete a record
$deleted = Zoho::contacts()->delete('5344xxxxxxxxxxxx');

// Clone a record (copies all non-system fields)
$clone = Zoho::contacts()->clone('5344xxxxxxxxxxxx');

// Get record count
$count = Zoho::contacts()->count();
```

## Module-Specific Behavior

### Calls — `Call_Duration` normalization

`ZohoCall` overrides `create`, `update`, `upsert`, and `updateMultiple` to normalize `Call_Duration` before sending. Zoho's Calls API expects `"mm:ss"` (minutes:seconds), but the package accepts:

- **Bare numbers as minutes** — `30` or `"30"` → `"30:00"`, `90` → `"90:00"`, `125` → `"125:00"`
- **`"HH:mm:ss"` strings** — converted to total minutes:seconds → `"01:02:05"` becomes `"62:05"`
- **`"mm:ss"` strings** — normalized with zero-padding → `"5:30"` becomes `"05:30"`
- **`null`, empty string, or non-numeric input** — passed through unchanged

```php
// Logging a 30-minute call against a Lead
Zoho::calls()->create([
    'Subject'       => 'Discovery call',
    'Call_Type'     => 'Outbound',
    'Call_Duration' => 30,                 // sent as "30:00"
    'What_Id'       => $leadId,            // What_Id for Leads, Accounts, Deals
    '$se_module'    => 'Leads',
]);

// Logging a call against a Contact (Who_Id, no $se_module needed)
Zoho::calls()->create([
    'Subject'       => 'Follow-up call',
    'Call_Type'     => 'Outbound',
    'Call_Duration' => 15,                 // sent as "15:00"
    'Who_Id'        => $contactId,         // Who_Id is for Contacts only
]);
```

**`Who_Id` vs `What_Id`**: Use `Who_Id` only for Contacts (the person). For Leads, Accounts, Deals, and other modules, use `What_Id` with `$se_module` set to the module API name.

Other modules pass payloads through unchanged — only `Calls` has this transformation.

## Search Operations

```php
// Search with Zoho criteria syntax
$results = Zoho::contacts()->search('(Last_Name:equals:Doe)');
$results = Zoho::leads()->search('(Company:starts_with:Acme)');

// Convenience search methods
$results = Zoho::contacts()->searchByEmail('jane@example.com');
$results = Zoho::contacts()->searchByPhone('+1234567890');
```

Search criteria syntax follows Zoho CRM conventions: `(Field_Name:operator:value)`. Supported operators include `equals`, `starts_with`, `contains`, `greater_than`, `less_than`, `between`, etc.

## Upsert (Create or Update)

```php
$result = Zoho::contacts()->upsert(
    [
        'Email'     => 'jane@example.com',
        'Last_Name' => 'Doe',
        'Phone'     => '+1234567890',
    ],
    ['Email'] // duplicate check fields
);
```

## Bulk Operations

```php
// Update multiple records
$results = Zoho::contacts()->updateMultiple([
    ['id' => '111', 'Phone' => '+1111111111'],
    ['id' => '222', 'Phone' => '+2222222222'],
]);

// Delete multiple records
$results = Zoho::contacts()->deleteMultiple(['111', '222', '333']);
```

## Related Records

```php
// Get deals related to a contact
$deals = Zoho::contacts()->getRelatedRecords('contact_id', 'Deals');

// Get notes related to an account
$notes = Zoho::accounts()->getRelatedRecords('account_id', 'Notes');
```

## Lead Conversion

```php
$result = Zoho::leads()->convert('lead_id', [
    'overwrite' => true,
    'notify_lead_owner' => true,
]);
```

## Field Metadata

```php
// Get all fields for a module
$fields = Zoho::contacts()->getFieldMetadata();

// Clear cached field names
Zoho::contacts()->clearFieldCache();
ZohoModel::clearAllFieldCache();
```

## Eloquent Model Sync

The `SyncsWithZoho` trait auto-syncs Eloquent models to Zoho CRM via queued jobs on create, update, and delete.

### Required implementation

```php
use Asciisd\Zoho\Traits\SyncsWithZoho;

class Customer extends Model
{
    use SyncsWithZoho;

    protected $fillable = ['name', 'email', 'phone', 'company'];

    // Required: specify which Zoho CRM module this model maps to
    public function getZohoModule(): string
    {
        return 'Contacts';
    }

    // Optional: map model attributes to Zoho field API names
    public function getZohoFieldMapping(): array
    {
        return [
            'name'    => 'Last_Name',
            'email'   => 'Email',
            'phone'   => 'Phone',
            'company' => 'Company',
        ];
    }

    // Optional: conditionally sync (defaults to true)
    protected function shouldSyncToZoho(): bool
    {
        return $this->is_active && !empty($this->email);
    }

    // Optional: exclude sensitive fields from sync
    public function getExcludedZohoFields(): array
    {
        return ['password', 'remember_token', 'api_key'];
    }
}
```

### Custom module support

For custom Zoho modules (or modules whose API names don't follow the standard naming convention like `Contacts` -> `ZohoContact`), the `SyncModelToZoho` job resolves the ZohoModel class using a 3-step chain:

1. **Model method** — override `getZohoModelClass()` on the Eloquent model to return a specific class
2. **Config map** — add an entry in `zoho.modules` mapping the module API name to a class
3. **Naming convention** — falls back to `Zoho` + singular module name

```php
// Option 1: Override getZohoModelClass() on the Eloquent model
class Property extends Model
{
    use SyncsWithZoho;

    public function getZohoModule(): string { return 'Property_Listings'; }

    public function getZohoModelClass(): ?string
    {
        return \App\Zoho\ZohoPropertyListing::class;
    }

    public function getZohoFieldMapping(): array
    {
        return ['address' => 'Listing_Address', 'price' => 'Asking_Price'];
    }
}
```

```php
// Option 2: Config map in config/zoho.php (no model changes needed)
'modules' => [
    'Property_Listings' => \App\Zoho\ZohoPropertyListing::class,
],
```

The custom ZohoModel class extends `ZohoModel` and sets `MODULE_API_NAME`:

```php
use Asciisd\Zoho\Models\ZohoModel;

class ZohoPropertyListing extends ZohoModel
{
    protected const MODULE_API_NAME = 'Property_Listings';
}
```

### Multi-model sync

Multiple Eloquent models can sync to the same Zoho module. The polymorphic `ZohoSync` model (`zohoable_type` + `zohoable_id`) keeps each model's sync records independent — different field mappings, separate Zoho record IDs, no collisions.

```php
// Both User and DemoAccount sync to Leads — each gets its own Zoho record
class User extends Model
{
    use SyncsWithZoho;

    public function getZohoModule(): string { return 'Leads'; }

    public function getZohoFieldMapping(): array
    {
        return ['name' => 'Last_Name', 'email' => 'Email'];
    }
}

class DemoAccount extends Model
{
    use SyncsWithZoho;

    public function getZohoModule(): string { return 'Leads'; }

    public function getZohoFieldMapping(): array
    {
        return ['company_name' => 'Company', 'contact_email' => 'Email'];
    }
}
```

Key details:
- Each model instance creates a **separate** record in Zoho (1 User + 1 DemoAccount = 2 Leads)
- `withoutZohoSync` is per-class — `User::withoutZohoSync(...)` does not affect `DemoAccount`
- To converge multiple models on one Zoho record, customize `SyncModelToZoho` to use `upsert` with a duplicate check field (e.g. `Email`)

### Sync helpers

```php
// Get the Zoho record ID linked to this model
$zohoId = $customer->getZohoRecordId();

// Access the ZohoSync relationship
$sync = $customer->zohoSync;

// Sync immediately (bypasses queue)
$customer->syncToZohoNow('create');

// Disable sync temporarily
Customer::withoutZohoSync(function () {
    Customer::create([...]);  // will NOT trigger Zoho sync
});
```

### Sync tracking

The `zoho_syncs` table stores polymorphic relationships between Eloquent models and Zoho records. The `ZohoSync` model provides scopes:

```php
use Asciisd\Zoho\Models\ZohoSync;

ZohoSync::forModule('Contacts')->get();
ZohoSync::withZohoRecordId('5344xxxx')->first();
ZohoSync::synced()->get();
ZohoSync::notSynced()->get();
```

## Webhook Handling

Routes are registered automatically:

- `POST /zoho/webhook` — receives webhooks
- `GET /zoho/webhook` — verification endpoint
- `GET /zoho/callback` — OAuth callback

### Listening to events

```php
// In EventServiceProvider or a listener
use Asciisd\Zoho\Events\ZohoWebhookReceived;
use Asciisd\Zoho\Events\ZohoRecordCreated;
use Asciisd\Zoho\Events\ZohoRecordUpdated;
use Asciisd\Zoho\Events\ZohoRecordDeleted;

// Generic webhook event
Event::listen(ZohoWebhookReceived::class, function ($event) {
    $payload = $event->payload;
    $module  = $event->module;
    $type    = $event->event;
    $data    = $event->getData();
});

// Specific CRUD events
Event::listen(ZohoRecordCreated::class, function ($event) {
    $record   = $event->record;
    $module   = $event->module;
    $recordId = $event->getRecordId();
});
```

Set `ZOHO_WEBHOOK_SECRET` to enable HMAC-SHA256 signature verification.

## OAuth Authentication

### Initial setup

```bash
php artisan zoho:setup
```

### Managing tokens

```bash
php artisan zoho:auth status     # Check auth status
php artisan zoho:auth url        # Get authorization URL
php artisan zoho:auth refresh    # Refresh access token
php artisan zoho:auth revoke     # Revoke token

php artisan zoho:token:refresh   # Refresh with optional --clear-cache
```

### Programmatic access

```php
$oauth = app('zoho.oauth');

$url    = $oauth->getAuthorizationUrl();
$tokens = $oauth->generateAccessToken($grantCode);
$token  = $oauth->getValidAccessToken();
$oauth->refreshAccessToken();
$oauth->revokeToken();
$oauth->isAuthenticated();
```

## Configuration

Publish the config with:

```bash
php artisan vendor:publish --tag=zoho-config
```

Key settings in `config/zoho.php`:

| Key | Env Variable | Default | Purpose |
|-----|-------------|---------|---------|
| `client_id` | `ZOHO_CLIENT_ID` | — | OAuth client ID |
| `client_secret` | `ZOHO_CLIENT_SECRET` | — | OAuth client secret |
| `redirect_uri` | `ZOHO_REDIRECT_URI` | — | OAuth redirect URI |
| `data_center` | `ZOHO_DATA_CENTER` | `US` | Data center region |
| `environment` | `ZOHO_ENVIRONMENT` | `production` | production/sandbox/developer |
| `token_storage` | `ZOHO_TOKEN_STORAGE` | `both` | cache/database/both |
| `modules` | — | `[]` | Module-to-class map for custom modules |
| `sync.enabled` | `ZOHO_SYNC_ENABLED` | `true` | Enable model sync |
| `sync.queue` | `ZOHO_SYNC_QUEUE` | `default` | Queue for sync jobs |

## Error Handling

The package provides typed exceptions:

```php
use Asciisd\Zoho\Exceptions\ZohoApiException;
use Asciisd\Zoho\Exceptions\ZohoAuthException;
use Asciisd\Zoho\Exceptions\ZohoTokenException;

try {
    $contact = Zoho::contacts()->find('invalid_id');
} catch (ZohoApiException $e) {
    // API errors: recordNotFound, invalidModule, requestFailed,
    //             invalidData, rateLimitExceeded, insufficientPermissions
} catch (ZohoAuthException $e) {
    // Auth errors: invalidCredentials, missingConfiguration,
    //              tokenGenerationFailed, tokenRefreshFailed, tokenExpired
} catch (ZohoTokenException $e) {
    // Token errors: missingToken, invalidToken,
    //               storageFailed, retrievalFailed, refreshTokenExpired
}
```

## Testing Commands

```bash
php artisan zoho:test Contacts                  # Test all operations
php artisan zoho:test Contacts --operation=read  # Test specific operation
php artisan zoho:test Leads --operation=search
```

## Service Container Bindings

```php
app('zoho');         // ZohoClient instance
app('zoho.oauth');   // OAuthManager instance
app('zoho.storage'); // TokenStorage instance
```

## Testing

Tests use [Orchestra Testbench](https://github.com/orchestral/testbench) and PHPUnit 10. All Zoho API calls are mocked with `Http::fake()` — no real HTTP requests are made.

### Running tests

```bash
vendor/bin/phpunit
```

### Test structure

```
tests/
├── TestCase.php                       # Base class — loads provider, config, migrations
├── Mocks/
│   ├── TestCustomer.php              # Eloquent model with SyncsWithZoho + field mapping
│   ├── TestCustomerNoMapping.php     # Eloquent model with SyncsWithZoho, no mapping
│   ├── TestCustomModuleCustomer.php  # Eloquent model syncing to a custom Zoho module
│   └── ZohoPropertyListing.php       # ZohoModel for custom module (Property_Listings)
├── database/migrations/              # Test-only migrations (test_customers table)
├── Unit/
│   ├── Auth/OAuthManagerTest.php
│   ├── Storage/TokenStorageTest.php
│   ├── Models/                       # ZohoModelTest, ZohoSyncTest, ZohoOAuthTokenTest
│   ├── Jobs/SyncModelToZohoTest.php
│   ├── Traits/SyncsWithZohoTest.php
│   ├── Exceptions/ExceptionsTest.php
│   ├── Events/EventsTest.php
│   └── ZohoClientTest.php
└── Feature/
    ├── Http/ZohoWebhookControllerTest.php
    ├── Console/                      # Auth, RefreshToken, Setup command tests
    └── ZohoServiceProviderTest.php
```

### Writing tests — key patterns

```php
// 1. Mock Zoho API responses
Http::fake([
    '*/crm/v8/Contacts' => Http::response([
        'data' => [['code' => 'SUCCESS', 'details' => ['id' => '123']]],
    ]),
]);

// 2. Seed tokens before testing API-dependent code
app('zoho.storage')->storeTokens([
    'access_token' => 'test-token',
    'refresh_token' => 'test-refresh',
    'expires_in' => 3600,
]);

// 3. Prevent sync side effects in unrelated tests
$customer = TestCustomer::withoutZohoSync(fn () => TestCustomer::create([
    'name' => 'Test', 'email' => 'test@example.com',
]));

// 4. Assert sync jobs are dispatched
Queue::fake();
TestCustomer::create([...]);
Queue::assertPushed(SyncModelToZoho::class, fn ($job) => $job->operation === 'create');

// 5. Test webhook signature verification
$payload = json_encode(['event' => 'create', 'module' => 'Contacts']);
$signature = hash_hmac('sha256', $payload, 'webhook-secret');
$this->postJson('/zoho/webhook', [...], ['X-Zoho-Webhook-Signature' => $signature]);
```
