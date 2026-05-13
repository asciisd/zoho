# Zoho CRM Laravel Package (API v8)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/asciisd/zoho?style=flat-square)](https://packagist.org/packages/asciisd/zoho)
[![Total Downloads](https://img.shields.io/packagist/dt/asciisd/zoho?style=flat-square)](https://packagist.org/packages/asciisd/zoho)

A minimal and elegant Laravel wrapper for Zoho CRM API v8. This package provides a clean, Laravel-style interface for interacting with Zoho CRM with model-like classes, automatic token management, and webhook support.

## Features

✅ **Model-like Interface** - Use intuitive model classes like `ZohoContact::create()`, `ZohoLead::find()`, etc.  
✅ **Automatic Token Management** - Hybrid cache + database token storage with auto-refresh  
✅ **Automatic Field Detection** - Dynamically fetches and caches all available fields from your CRM  
✅ **Full CRUD Operations** - Create, Read, Update, Delete, Search, Upsert, and more  
✅ **Webhook Support** - Handle Zoho CRM webhooks with Laravel events  
✅ **Comprehensive Artisan Commands** - Easy setup, testing, and data synchronization  
✅ **Laravel 11+ Support** - Built for modern Laravel applications  
✅ **Minimal Code** - Super easy to integrate and use  
✅ **Multiple Data Centers** - Support for US, EU, IN, CN, JP, AU, CA  

## Requirements

- PHP 8.2 or higher
- Laravel 11.0 or higher
- Zoho CRM Account with API access

## Installation

Install the package via Composer:

```bash
composer require asciisd/zoho
```

### Publish Configuration

Publish the configuration file and migrations:

```bash
php artisan vendor:publish --tag=zoho-config
php artisan vendor:publish --tag=zoho-migrations
```

### Run Migrations

Run the migration to create the OAuth tokens table:

```bash
php artisan migrate
```

### Get Zoho API Credentials

Before configuring the package, you need to create a Zoho API Client to get your OAuth credentials.

> **Don't have a Zoho account?** Sign up at [Zoho CRM](https://www.zoho.com/crm/signup.html) and choose your preferred data center during registration.

#### Step 1: Choose Your Data Center

Visit the [Zoho API Console](https://api-console.zoho.com/add) and sign in with the **correct data center** where your Zoho CRM account is registered:

| Data Center | CRM URL | API Console Region |
|-------------|---------|-------------------|
| **US** (United States) | crm.zoho.com | accounts.zoho.com |
| **EU** (Europe) | crm.zoho.eu | accounts.zoho.eu |
| **IN** (India) | crm.zoho.in | accounts.zoho.in |
| **CN** (China) | crm.zoho.com.cn | accounts.zoho.com.cn |
| **JP** (Japan) | crm.zoho.jp | accounts.zoho.jp |
| **AU** (Australia) | crm.zoho.com.au | accounts.zoho.com.au |
| **CA** (Canada) | crm.zohocloud.ca | accounts.zohocloud.ca |

**Important:** The data center you choose must match where your Zoho CRM data is stored. Check your Zoho CRM URL to determine your data center.

#### Step 2: Create API Client

1. Sign in to the [Zoho API Console](https://api-console.zoho.com)
2. Click **"Add Client"** or **"Get Started"**
3. Choose **"Server-based Applications"**
4. Fill in the required details:
   - **Client Name**: Your application name (e.g., "Laravel Zoho Integration")
   - **Homepage URL**: Your website URL (e.g., `https://yourapp.com`)
   - **Authorized Redirect URIs**: Your callback URL (e.g., `https://yourapp.test/zoho/callback` or `http://localhost:8000/zoho/callback` for local development)

5. Click **"Create"**

#### Step 3: Copy Credentials

After creating the client, you'll see:

- **Client ID** - A long string like `1000.XXXXXXXXXXXXX`
- **Client Secret** - Your secret key

**Keep these credentials secure!** Never commit them to version control.

#### Step 4: Configure OAuth Scopes

When setting up authentication, the package will request these scopes by default:

```
ZohoCRM.modules.ALL
ZohoCRM.settings.ALL
ZohoCRM.users.ALL
```

For additional features, you may need:

- `ZohoCRM.org.ALL` - Organization information
- `ZohoCRM.bulk.ALL` - Bulk operations
- `ZohoCRM.notifications.ALL` - Webhooks and notifications

### Configure Environment Variables

Add the credentials to your `.env` file:

```env
ZOHO_CLIENT_ID=1000.XXXXXXXXXXXXX
ZOHO_CLIENT_SECRET=your_secret_here
ZOHO_REDIRECT_URI=https://yourapp.test/zoho/callback
ZOHO_DATA_CENTER=US
ZOHO_ENVIRONMENT=production
```

**For Local Development:**

```env
ZOHO_REDIRECT_URI=http://localhost:8000/zoho/callback
# or
ZOHO_REDIRECT_URI=https://yourapp.test/zoho/callback
```

Make sure the redirect URI matches exactly what you entered in the Zoho API Console (including protocol and port).

### Setup Checklist

Before running the setup command, ensure:

- [ ] You have a Zoho CRM account
- [ ] You've signed in to the correct data center in API Console
- [ ] You've created a Server-based Application client
- [ ] Your `ZOHO_CLIENT_ID` is set in `.env`
- [ ] Your `ZOHO_CLIENT_SECRET` is set in `.env`
- [ ] Your `ZOHO_REDIRECT_URI` exactly matches the API Console
- [ ] Your `ZOHO_DATA_CENTER` matches your CRM URL
- [ ] You've run `php artisan migrate`

## Quick Start

### 1. Authentication Setup

Run the setup command to authenticate with Zoho CRM:

```bash
php artisan zoho:setup
```

This interactive command will:

1. Generate an authorization URL
2. Accept your grant token/code
3. Generate and store access & refresh tokens
4. Test the connection

### 2. Basic Usage

#### Create a Contact

```php
use Asciisd\Zoho\Models\ZohoContact;

$contact = ZohoContact::create([
    'First_Name' => 'John',
    'Last_Name' => 'Doe',
    'Email' => 'john.doe@example.com',
    'Phone' => '+1234567890',
]);
```

#### Find a Contact

```php
$contact = ZohoContact::find('4150868000000624001');
```

#### Update a Contact

```php
ZohoContact::update('4150868000000624001', [
    'Phone' => '+0987654321',
    'Title' => 'Senior Developer',
]);
```

#### Delete a Contact

```php
ZohoContact::delete('4150868000000624001');
```

#### Get All Contacts

```php
$contacts = ZohoContact::all([
    'per_page' => 200,
    'page' => 1,
]);

foreach ($contacts as $contact) {
    echo $contact['Full_Name'];
}
```

#### Search Contacts

```php
// Search by email
$contacts = ZohoContact::searchByEmail('john.doe@example.com');

// Search by phone
$contacts = ZohoContact::searchByPhone('+1234567890');

// Custom search
$contacts = ZohoContact::search('(Last_Name:starts_with:Doe)');
```

#### Upsert (Create or Update)

```php
$contact = ZohoContact::upsert([
    'First_Name' => 'Jane',
    'Last_Name' => 'Smith',
    'Email' => 'jane.smith@example.com',
], ['Email']); // Duplicate check by Email
```

## Available Modules

The package provides model classes for all major Zoho CRM modules:

- `ZohoContact` - Contacts module
- `ZohoAccount` - Accounts module
- `ZohoLead` - Leads module
- `ZohoDeal` - Deals module
- `ZohoTask` - Tasks module
- `ZohoEvent` - Events module
- `ZohoCall` - Calls module
- `ZohoNote` - Notes module
- `ZohoProduct` - Products module
- `ZohoInvoice` - Invoices module

All modules extend the base `ZohoModel` class and support the same methods.

## Using the Facade

You can also use the `Zoho` facade for a fluent interface:

```php
use Asciisd\Zoho\Facades\Zoho;

// Create a contact
$contact = Zoho::contacts()->create([...]);

// Create a lead
$lead = Zoho::leads()->create([...]);

// Create a deal
$deal = Zoho::deals()->create([...]);
```

## Advanced Usage

### Get Related Records

```php
$deals = ZohoContact::getRelatedRecords('4150868000000624001', 'Deals');
```

### Update Multiple Records

```php
ZohoContact::updateMultiple([
    ['id' => '123', 'Phone' => '111'],
    ['id' => '456', 'Phone' => '222'],
]);
```

### Delete Multiple Records

```php
ZohoContact::deleteMultiple(['123', '456', '789']);
```

### Get Deleted Records

```php
$deletedContacts = ZohoContact::getDeletedRecords([
    'type' => 'permanent',
]);
```

### Convert Lead

```php
ZohoLead::convert('4150868000000624001', [
    'overwrite' => true,
    'notify_lead_owner' => true,
    'notify_new_entity_owner' => true,
]);
```

### Get Record Count

```php
$count = ZohoContact::count();
```

### Clone a Record

```php
$clonedContact = ZohoContact::clone('4150868000000624001');
```

### Field Management

The package automatically fetches all available field names for each module from the Zoho CRM API and caches them for improved performance. This ensures you always get all fields without needing to manually specify them.

#### Get Field Metadata

Fetch complete field metadata including field types, properties, and configurations:

```php
$fields = ZohoContact::getFieldMetadata();

// Each field contains information like:
// - api_name
// - field_label
// - data_type
// - read_only
// - required
// - and more...
```

#### Specify Custom Fields

You can override the automatic field fetching by specifying custom fields for individual requests:

```php
// Fetch only specific fields
$contact = ZohoContact::find('123', [
    'fields' => 'id,First_Name,Last_Name,Email'
]);

// Search with specific fields
$contacts = ZohoContact::all([
    'fields' => 'Full_Name,Email,Phone',
    'per_page' => 50,
]);
```

#### Clear Field Cache

If you've added new custom fields to your Zoho CRM or need to refresh the cached field names:

```php
// Clear cache for specific module
ZohoContact::clearFieldCache();

// Clear cache for all modules
ZohoContact::clearAllFieldCache();
```

**Note:** Field names are automatically cached after the first request to each module. The cache persists for the duration of the application runtime. If you modify fields in your Zoho CRM (add/remove custom fields), you should clear the cache to fetch the updated field list.

## Artisan Commands

### Setup Authentication

```bash
php artisan zoho:setup
```

Interactive OAuth setup wizard.

### Authentication Management

```bash
# Show authentication status
php artisan zoho:auth status

# Show authorization URL
php artisan zoho:auth url

# Refresh access token
php artisan zoho:auth refresh

# Revoke access token
php artisan zoho:auth revoke
```

### Test CRUD Operations

```bash
# Test all operations on Contacts
php artisan zoho:test Contacts

# Test specific operation
php artisan zoho:test Leads --operation=create
```

### Sync Data

```bash
# Pull contacts from Zoho CRM
php artisan zoho:sync Contacts --direction=pull --limit=200

# Push contacts to Zoho CRM (requires implementation)
php artisan zoho:sync Contacts --direction=push
```

### Token Management

```bash
# Refresh access token
php artisan zoho:token:refresh

# Clear cached tokens
php artisan zoho:token:refresh --clear-cache
```

## Webhook Integration

### Setup Webhook in Zoho CRM

1. Go to **Setup** → **Developer Space** → **Webhooks**
2. Create a new webhook
3. Set URL to: `https://your-domain.com/zoho/webhook`
4. Select the modules and events you want to track

### Handle Webhook Events

Listen to webhook events in your application:

```php
use Asciisd\Zoho\Events\ZohoRecordCreated;
use Asciisd\Zoho\Events\ZohoRecordUpdated;
use Asciisd\Zoho\Events\ZohoRecordDeleted;
use Asciisd\Zoho\Events\ZohoWebhookReceived;

// Listen to all webhooks
Event::listen(ZohoWebhookReceived::class, function ($event) {
    $module = $event->module;
    $eventType = $event->event;
    $data = $event->getData();
    
    // Handle webhook
});

// Listen to specific events
Event::listen(ZohoRecordCreated::class, function ($event) {
    $module = $event->module; // e.g., 'Contacts'
    $record = $event->getData();
    $recordId = $event->getRecordId();
    
    // Handle record creation
});

Event::listen(ZohoRecordUpdated::class, function ($event) {
    // Handle record update
});

Event::listen(ZohoRecordDeleted::class, function ($event) {
    // Handle record deletion
});
```

### Webhook Security

Add a webhook secret to your `.env` file for signature verification:

```env
ZOHO_WEBHOOK_SECRET=your_secret_key
```

## Model Synchronization

The package provides a powerful trait-based system to automatically sync any Laravel model with any Zoho CRM module. When you create, update, or delete a model in your Laravel application, it will automatically sync with Zoho CRM in the background using Laravel queues.

### Features

✅ **Automatic Syncing** - Sync on create, update, and delete events  
✅ **Queued Processing** - Non-blocking background sync with Laravel queues  
✅ **Automatic Retries** - 3 automatic retries with exponential backoff  
✅ **Field Mapping** - Flexible field mapping between Laravel models and Zoho fields  
✅ **Polymorphic Storage** - Store Zoho record IDs using polymorphic relationships  
✅ **Conditional Sync** - Add custom logic to control when syncing occurs  
✅ **Manual Control** - Temporarily disable syncing or trigger manual syncs  

### Installation

Publish and run the sync migration:

```bash
php artisan vendor:publish --tag=zoho-migrations
php artisan migrate
```

This will create the `zoho_syncs` table to store the relationship between your Laravel models and Zoho records.

### Basic Usage

Add the `SyncsWithZoho` trait to any model and define the `getZohoModule()` method:

```php
use Asciisd\Zoho\Traits\SyncsWithZoho;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use SyncsWithZoho;
    
    protected $fillable = ['name', 'email', 'phone'];
    
    /**
     * Specify which Zoho module to sync with.
     */
    protected function getZohoModule(): string
    {
        return 'Contacts';
    }
}
```

Now, whenever you create, update, or delete a user, it will automatically sync with Zoho CRM Contacts:

```php
// Automatically creates a Contact in Zoho CRM (queued)
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '+1234567890',
]);

// Automatically updates the Contact in Zoho CRM (queued)
$user->update(['phone' => '+0987654321']);

// Automatically deletes the Contact in Zoho CRM (queued)
$user->delete();
```

### Field Mapping

By default, the trait will sync all fillable attributes using the same field names. To customize field mapping between your Laravel model and Zoho fields:

```php
class User extends Authenticatable
{
    use SyncsWithZoho;
    
    protected $fillable = ['name', 'email', 'phone', 'company'];
    
    protected function getZohoModule(): string
    {
        return 'Contacts';
    }
    
    /**
     * Map Laravel model fields to Zoho CRM fields.
     */
    protected function getZohoFieldMapping(): array
    {
        return [
            'name' => 'Full_Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'company' => 'Account_Name',
        ];
    }
}
```

### DemoAccount to Lead Example

Here's how to sync a `DemoAccount` model with Zoho CRM `Leads`:

```php
use Asciisd\Zoho\Traits\SyncsWithZoho;
use Illuminate\Database\Eloquent\Model;

class DemoAccount extends Model
{
    use SyncsWithZoho;
    
    protected $fillable = [
        'name',
        'email',
        'phone',
        'country',
        'company',
    ];
    
    protected function getZohoModule(): string
    {
        return 'Leads';
    }
    
    protected function getZohoFieldMapping(): array
    {
        return [
            'name' => 'Last_Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'company' => 'Company',
            // 'country' will map automatically with the same name
        ];
    }
}
```

### Conditional Syncing

Add custom logic to control when a model should sync:

```php
protected function shouldSyncToZoho(): bool
{
    // Only sync verified users
    if ($this->email_verified_at === null) {
        return false;
    }
    
    // Only sync users with specific role
    if (!$this->hasRole('customer')) {
        return false;
    }
    
    return parent::shouldSyncToZoho();
}
```

### Temporarily Disable Syncing

Use the `withoutZohoSync()` method to temporarily disable syncing:

```php
// Import data without syncing to Zoho
User::withoutZohoSync(function () {
    User::create(['name' => 'John', 'email' => 'john@example.com']);
    User::create(['name' => 'Jane', 'email' => 'jane@example.com']);
    // ... import 1000 more users
});

// Or disable for a single operation
User::withoutZohoSync(fn () => $user->update(['internal_notes' => 'test']));
```

### Manual Sync

Trigger a manual sync immediately (not queued):

```php
// Force immediate sync to Zoho
$user->syncToZohoNow('create');
$user->syncToZohoNow('update');
```

### Accessing Zoho Record ID

Get the Zoho CRM record ID for any synced model:

```php
$user = User::find(1);

// Get the Zoho record ID
$zohoRecordId = $user->getZohoRecordId();

// Access the full sync relationship
$zohoSync = $user->zohoSync;
echo $zohoSync->zoho_record_id;
echo $zohoSync->zoho_module;
echo $zohoSync->last_synced_at;
```

### Configuration

Configure sync behavior in `config/zoho.php`:

```php
'sync' => [
    // Enable or disable automatic syncing globally
    'enabled' => env('ZOHO_SYNC_ENABLED', true),
    
    // Queue connection to use for sync jobs
    'queue' => env('ZOHO_SYNC_QUEUE', 'default'),
    
    // Number of retry attempts
    'retry_attempts' => env('ZOHO_SYNC_RETRY_ATTEMPTS', 3),
    
    // Backoff delays between retries (in seconds)
    'retry_backoff' => [60, 120, 300], // 1 min, 2 min, 5 min
],
```

### Environment Variables

Add these to your `.env` file:

```env
# Enable/disable model syncing globally
ZOHO_SYNC_ENABLED=true

# Queue connection for sync jobs
ZOHO_SYNC_QUEUE=default

# Number of retry attempts before giving up
ZOHO_SYNC_RETRY_ATTEMPTS=3
```

### How It Works

1. **Model Event**: When you create/update/delete a model with the `SyncsWithZoho` trait
2. **Job Dispatch**: A `SyncModelToZoho` job is dispatched to the queue
3. **Field Transform**: Model data is transformed using your field mapping
4. **API Call**: The job makes the appropriate Zoho CRM API call
5. **Record Storage**: The Zoho record ID is stored in the `zoho_syncs` table
6. **Retry Logic**: If the API call fails, it retries 3 times with exponential backoff
7. **Logging**: Success and failures are logged for monitoring

### Queue Workers

Make sure you have queue workers running to process sync jobs:

```bash
# Run queue worker
php artisan queue:work

# Or use Supervisor/Laravel Horizon in production
```

### Error Handling

Failed syncs are automatically retried 3 times. After all retries fail, errors are logged to your application log:

```php
// Check logs for sync failures
tail -f storage/logs/laravel.log | grep "Zoho sync"
```

## Configuration

The package configuration file (`config/zoho.php`) includes:

```php
return [
    'client_id' => env('ZOHO_CLIENT_ID'),
    'client_secret' => env('ZOHO_CLIENT_SECRET'),
    'redirect_uri' => env('ZOHO_REDIRECT_URI'),
    'refresh_token' => env('ZOHO_REFRESH_TOKEN'),
    'access_token' => env('ZOHO_ACCESS_TOKEN'),
    
    // Environment: production, sandbox, developer
    'environment' => env('ZOHO_ENVIRONMENT', 'production'),
    
    // Data Center: US, EU, IN, CN, JP, AU, CA
    'data_center' => env('ZOHO_DATA_CENTER', 'US'),
    
    // Token Storage: cache, database, both
    'token_storage' => env('ZOHO_TOKEN_STORAGE', 'both'),
    
    'cache_driver' => env('ZOHO_CACHE_DRIVER', 'file'),
    'cache_ttl' => env('ZOHO_CACHE_TTL', 3600),
    
    'webhook_secret' => env('ZOHO_WEBHOOK_SECRET'),
    
    'pagination' => [
        'per_page' => env('ZOHO_PER_PAGE', 200),
        'max_records' => env('ZOHO_MAX_RECORDS', 200),
    ],
];
```

## Error Handling

The package provides custom exceptions for better error handling:

```php
use Asciisd\Zoho\Exceptions\ZohoException;
use Asciisd\Zoho\Exceptions\ZohoAuthException;
use Asciisd\Zoho\Exceptions\ZohoApiException;
use Asciisd\Zoho\Exceptions\ZohoTokenException;

try {
    $contact = ZohoContact::find('invalid_id');
} catch (ZohoApiException $e) {
    // Handle API error
    echo $e->getMessage();
} catch (ZohoAuthException $e) {
    // Handle authentication error
} catch (ZohoTokenException $e) {
    // Handle token error
}
```

## Data Centers

The package supports all Zoho CRM data centers:

- `US` - United States (<https://www.zohoapis.com>)
- `EU` - Europe (<https://www.zohoapis.eu>)
- `IN` - India (<https://www.zohoapis.in>)
- `CN` - China (<https://www.zohoapis.com.cn>)
- `JP` - Japan (<https://www.zohoapis.jp>)
- `AU` - Australia (<https://www.zohoapis.com.au>)
- `CA` - Canada (<https://www.zohoapis.ca>)

Set your data center in `.env`:

```env
ZOHO_DATA_CENTER=EU
```

## Token Storage

The package supports three token storage methods:

1. **Cache Only** - Fast but volatile
2. **Database Only** - Persistent but slower
3. **Both** (Recommended) - Cache with database fallback

Configure in `.env`:

```env
ZOHO_TOKEN_STORAGE=both
```

## Testing

```bash
# Test Contacts module
php artisan zoho:test Contacts

# Test specific operations
php artisan zoho:test Leads --operation=create
php artisan zoho:test Accounts --operation=read
```

## Troubleshooting

### Wrong Data Center Error

**Error:** `Invalid OAuth credentials` or `Authentication failed`

**Solution:** Make sure your `ZOHO_DATA_CENTER` in `.env` matches where your Zoho CRM account is registered:

```bash
# Check your Zoho CRM URL:
# crm.zoho.com → ZOHO_DATA_CENTER=US
# crm.zoho.eu → ZOHO_DATA_CENTER=EU
# crm.zoho.in → ZOHO_DATA_CENTER=IN
# etc.
```

The API Console you use to create credentials **must match** your data center.

### Redirect URI Mismatch

**Error:** `redirect_uri_mismatch` during OAuth setup

**Solution:**

1. Check that `ZOHO_REDIRECT_URI` in `.env` **exactly matches** what you entered in Zoho API Console
2. Include the protocol (`http://` or `https://`)
3. Include the port if using non-standard ports (`:8000`, `:3000`, etc.)
4. No trailing slashes unless you added them in the API Console

```env
# ✅ Correct
ZOHO_REDIRECT_URI=https://yourapp.test/zoho/callback
ZOHO_REDIRECT_URI=http://localhost:8000/zoho/callback

# ❌ Wrong (if you registered without port)
ZOHO_REDIRECT_URI=https://yourapp.test:443/zoho/callback
```

### Token Expired Error

If you get a token expired error, refresh the token:

```bash
php artisan zoho:token:refresh
```

### Invalid Credentials

Make sure your `.env` file has the correct credentials:

```bash
php artisan zoho:auth status
```

If credentials are invalid:

1. Verify `ZOHO_CLIENT_ID` and `ZOHO_CLIENT_SECRET` from API Console
2. Check that you're using the correct data center
3. Run `php artisan zoho:setup` to re-authenticate

### Grant Token Expired

**Error:** `invalid_code` or `Grant token has expired`

**Solution:** Grant tokens from Zoho expire quickly (usually within 2-3 minutes). When running `php artisan zoho:setup`:

1. Generate the authorization URL
2. **Immediately** open it in your browser
3. Copy the code from the redirect URL
4. **Quickly** paste it into the terminal

If it expires, just run `php artisan zoho:setup` again.

### Rate Limit Exceeded

Zoho CRM has API rate limits. The package will throw a `ZohoApiException` with code 429. Implement retry logic with exponential backoff.

**Rate Limits:**

- 100 API calls per minute per user
- 5,000 API calls per day (varies by plan)

### Connection Timeout

**Error:** `Connection timed out` or `Failed to connect`

**Solution:**

1. Check your internet connection
2. Verify your firewall allows outbound HTTPS connections
3. Check if Zoho services are down: [Zoho Status](https://status.zoho.com)
4. Try a different data center if you have a regional account

### Cache Issues

If tokens aren't refreshing properly:

```bash
# Clear Laravel cache
php artisan cache:clear

# Clear Zoho token cache specifically
php artisan zoho:token:refresh --clear-cache
```

## FAQ

### How do I know which data center I'm in?

Check the URL you use to access Zoho CRM:

- `crm.zoho.com` = US
- `crm.zoho.eu` = EU
- `crm.zoho.in` = IN
- And so on...

### Can I use the same credentials for multiple environments?

Yes, but you should:

1. Create separate API clients for development and production
2. Use different redirect URIs for each environment
3. Store credentials separately in each `.env` file

### What scopes do I need?

For basic CRUD operations:

```
ZohoCRM.modules.ALL
ZohoCRM.settings.ALL
ZohoCRM.users.ALL
```

For advanced features (webhooks, bulk operations):

```
ZohoCRM.modules.ALL
ZohoCRM.settings.ALL
ZohoCRM.users.ALL
ZohoCRM.org.ALL
ZohoCRM.bulk.ALL
ZohoCRM.notifications.ALL
```

The `zoho:setup` command uses the default scopes, but you can specify custom scopes when generating the authorization URL.

### How long do tokens last?

- **Access Token**: 1 hour (auto-refreshed by the package)
- **Refresh Token**: No expiration (unless revoked)
- **Grant Token**: 2-3 minutes (use immediately)

### Can I test without SSL locally?

Yes, for local development you can use:

```env
ZOHO_REDIRECT_URI=http://localhost:8000/zoho/callback
```

However, for production, always use HTTPS.

### Do I need to create a route for the redirect URI?

No, the package doesn't require an actual route at the redirect URI. The redirect URI is only used during the OAuth setup process to receive the authorization code, which you'll copy manually from the browser's address bar.

If you want to automate this, you can create a route that captures the code and displays it, but it's not required for the package to work.

### How do I switch data centers?

1. Update `ZOHO_DATA_CENTER` in `.env`
2. Clear tokens: `php artisan zoho:token:refresh --clear-cache`
3. Re-authenticate: `php artisan zoho:setup`

Note: Your data must exist in the target data center.

### Can I use this package with Zoho Sandbox?

Yes! Set in your `.env`:

```env
ZOHO_ENVIRONMENT=sandbox
```

Then authenticate with your sandbox credentials.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Security

If you discover any security-related issues, please email <aemaddin@gmail.com> instead of using the issue tracker.

## Credits

- [Ascii SD](https://github.com/asciisd)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

For support, please open an issue on GitHub or contact <aemaddin@gmail.com>.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.
