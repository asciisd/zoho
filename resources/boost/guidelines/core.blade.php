## Zoho CRM for Laravel (asciisd/zoho)

This package provides a fluent Laravel wrapper for the Zoho CRM API v8. It offers model-style classes for CRM modules, OAuth token management, automatic field detection, CRUD/search/upsert operations, webhook handling, and bi-directional Eloquent model sync.

### Key Conventions

- Access Zoho CRM modules via the `Zoho` facade: `Zoho::contacts()`, `Zoho::leads()`, `Zoho::deals()`, etc.
- Each module method returns a dedicated model class (e.g., `ZohoContact`, `ZohoLead`) that exposes static CRUD methods.
- All module model classes extend `Asciisd\Zoho\Models\ZohoModel`.
- Configuration lives in `config/zoho.php` and uses `ZOHO_*` environment variables.
- OAuth tokens are managed automatically via `OAuthManager` and stored in cache, database, or both.
- Use `php artisan zoho:setup` for initial OAuth configuration.

### Available Modules

@verbatim
<code-snippet name="Available Zoho CRM modules" lang="php">
Zoho::contacts();   // Contacts module
Zoho::accounts();   // Accounts module
Zoho::leads();      // Leads module
Zoho::deals();      // Deals module
Zoho::tasks();      // Tasks module
Zoho::events();     // Events module
Zoho::calls();      // Calls module
Zoho::notes();      // Notes module
Zoho::products();   // Products module
Zoho::invoices();   // Invoices module
</code-snippet>
@endverbatim

### CRUD Operations

@verbatim
<code-snippet name="Basic CRUD operations" lang="php">
use Asciisd\Zoho\Facades\Zoho;

// Create
$contact = Zoho::contacts()->create([
    'First_Name' => 'John',
    'Last_Name' => 'Doe',
    'Email' => 'john@example.com',
]);

// Read
$contact = Zoho::contacts()->find('record_id');
$allContacts = Zoho::contacts()->all();

// Update
Zoho::contacts()->update('record_id', ['Phone' => '+1234567890']);

// Delete
Zoho::contacts()->delete('record_id');

// Search
$results = Zoho::contacts()->search('(Email:equals:john@example.com)');
$results = Zoho::contacts()->searchByEmail('john@example.com');

// Upsert (create or update based on duplicate check)
Zoho::contacts()->upsert(
    ['Email' => 'john@example.com', 'Last_Name' => 'Doe'],
    ['Email']
);
</code-snippet>
@endverbatim

### Eloquent Model Sync

Use the `SyncsWithZoho` trait on Eloquent models to automatically sync them to Zoho CRM on create, update, and delete. The model must implement `getZohoModule()` and optionally `getZohoFieldMapping()`.

@verbatim
<code-snippet name="Eloquent model sync with SyncsWithZoho trait" lang="php">
use Asciisd\Zoho\Traits\SyncsWithZoho;

class Customer extends Model
{
    use SyncsWithZoho;

    public function getZohoModule(): string
    {
        return 'Contacts';
    }

    public function getZohoFieldMapping(): array
    {
        return [
            'name' => 'Last_Name',
            'email' => 'Email',
            'phone' => 'Phone',
        ];
    }

    protected function shouldSyncToZoho(): bool
    {
        return $this->is_active;
    }
}
</code-snippet>
@endverbatim

#### Custom Module Support

For custom Zoho modules (or modules whose API names don't follow the standard naming convention), the package resolves ZohoModel classes using a 3-step chain:

1. **Model method** — override `getZohoModelClass()` to return a specific class
2. **Config map** — add an entry in `zoho.modules` mapping the module API name to a class
3. **Naming convention** — falls back to `Zoho` + singular module name (e.g. `Contacts` -> `ZohoContact`)

@verbatim
<code-snippet name="Custom module via model method" lang="php">
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

// The custom ZohoModel class extends ZohoModel with the module API name
class ZohoPropertyListing extends ZohoModel
{
    protected const MODULE_API_NAME = 'Property_Listings';
}
</code-snippet>
@endverbatim

@verbatim
<code-snippet name="Custom module via config map" lang="php">
// Option 2: Config map in config/zoho.php (no model changes needed)
'modules' => [
    'Property_Listings' => \App\Zoho\ZohoPropertyListing::class,
],
</code-snippet>
@endverbatim

#### Multi-Model Sync

Multiple Eloquent models can sync to the same Zoho module. The polymorphic `ZohoSync` model (`zohoable_type` + `zohoable_id`) keeps each model's sync records independent — different field mappings, separate Zoho record IDs, no collisions.

@verbatim
<code-snippet name="Multiple models syncing to the same Zoho module" lang="php">
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
</code-snippet>
@endverbatim

Each model instance creates a separate record in Zoho. `withoutZohoSync` is scoped per-class. To converge multiple models on one Zoho record, customize `SyncModelToZoho` to use `upsert` with a duplicate check field.

### Webhook Events

The package dispatches Laravel events for Zoho CRM webhooks. Listen for `ZohoWebhookReceived`, `ZohoRecordCreated`, `ZohoRecordUpdated`, or `ZohoRecordDeleted` events.

### Artisan Commands

- `zoho:setup` — Interactive OAuth setup wizard.
- `zoho:auth {action}` — Manage auth: `status`, `url`, `refresh`, `revoke`.
- `zoho:test {module}` — Test API connectivity and CRUD operations.
- `zoho:sync {module}` — Sync records between Laravel and Zoho CRM.
- `zoho:token:refresh` — Refresh the OAuth access token.

### Required Environment Variables

- `ZOHO_CLIENT_ID` — OAuth client ID from Zoho API Console.
- `ZOHO_CLIENT_SECRET` — OAuth client secret.
- `ZOHO_REDIRECT_URI` — OAuth redirect URI.
- `ZOHO_DATA_CENTER` — Data center: `US`, `EU`, `IN`, `CN`, `JP`, `AU`, `CA`.
