# Changelog

All notable changes to `asciisd/zoho` will be documented in this file.

## 8.0.0 - 2026-05-13

### Breaking Changes

This is a complete rewrite of the package. If upgrading from v2.x, see [UPGRADE.md](UPGRADE.md) for a full migration guide.

- **Package renamed** from `asciisd/zoho-v8` to `asciisd/zoho` (replaces the legacy v2.x package)
- **Namespace changed** from `Asciisd\ZohoV8\` to `Asciisd\Zoho\`
- **Removed official SDK dependency** — no longer requires `zohocrm/php-sdk-8.0`; uses Laravel HTTP client directly
- **PHP 8.4+ required** (up from 7.2 in v2.x)
- **Laravel 11+ required** (up from 6.x in v2.x)
- **Architecture overhaul** — replaces `ZohoManager` facade with static ZohoModel classes and `SyncsWithZoho` trait
- Old `Zohoable` base model replaced by `SyncsWithZoho` trait (composable, works with any Eloquent model)
- Old `CriteriaBuilder` replaced by direct criteria string format

### Added

- Static ZohoModel classes: `ZohoContact`, `ZohoLead`, `ZohoDeal`, `ZohoAccount`, `ZohoProduct`, `ZohoInvoice`, `ZohoEvent`, `ZohoTask`, `ZohoCall`, `ZohoNote`
- Full CRUD via static methods: `::create()`, `::find()`, `::update()`, `::delete()`, `::search()`, `::upsert()`
- `SyncsWithZoho` trait for automatic Eloquent model synchronization via queued jobs
- Polymorphic `ZohoSync` model for tracking Zoho record IDs per model
- Custom module support with 3-step resolution chain (model method, config map, naming convention)
- Automatic field detection — dynamically fetches and caches module field names
- Comprehensive Artisan commands: `zoho:setup`, `zoho:auth`, `zoho:test`, `zoho:sync`, `zoho:token:refresh`
- Webhook support with typed Laravel events (`ZohoRecordCreated`, `ZohoRecordUpdated`, `ZohoRecordDeleted`)
- Hybrid token storage (cache + database) with automatic refresh
- Support for all Zoho CRM data centers (US, EU, IN, CN, JP, AU, CA)
- Batch operations (`updateMultiple`, `deleteMultiple`)
- Lead conversion, record cloning, record count
- Related records support
- Typed exceptions: `ZohoApiException`, `ZohoAuthException`, `ZohoTokenException`

### Removed

- `zohocrm/php-sdk-8.0` dependency (direct HTTP calls via Laravel `Http` facade)
- `ZohoManager` facade (replaced by `Zoho` facade with module accessors)
- `Zohoable` abstract model (replaced by `SyncsWithZoho` trait)
- `CriteriaBuilder` class (use criteria strings directly)
- `zoho:authentication` command (replaced by `zoho:setup`)

## 1.2.1 - 2026-03-13

### Fixed

- Fixed `zoho:setup` command asking for grant token even when the OAuth callback route already handles token exchange
- Grant codes are single-use, so the manual prompt would always fail when the redirect URI pointed to the app's `/zoho/callback` route

### Changed

- `zoho:setup` now auto-detects whether the redirect URI uses the app's callback route
- When using the callback route, the command polls for token completion instead of prompting for manual input
- Manual grant token prompt is preserved for non-callback redirect URIs (e.g., self-client setups)
- Extracted `waitForCallback()`, `manualGrantToken()`, and `verifyConnection()` methods for cleaner separation of concerns

## 1.2.0 - 2026-03-13

### Added

- Custom module support with 3-step ZohoModel class resolution chain:
  1. Model method — override `getZohoModelClass()` on Eloquent models to return a specific ZohoModel class
  2. Config map — register module-to-class mappings in `zoho.modules` config
  3. Naming convention — existing fallback (`Contacts` -> `ZohoContact`)
- New `getZohoModelClass(): ?string` method on `SyncsWithZoho` trait
- New `zoho.modules` config key for mapping custom module API names to ZohoModel classes
- New `resolveZohoModelClass()` and `guessZohoModelClass()` methods on `SyncModelToZoho` job
- Test mocks: `TestCustomModuleCustomer`, `ZohoPropertyListing` for custom module testing
- Tests covering all three resolution paths and end-to-end custom module sync

### Changed

- `SyncModelToZoho` job now uses `resolveZohoModelClass()` instead of the previous `getZohoModelClass()` with its naive `rtrim($module, 's')` convention
- Improved error message when ZohoModel class is not found, guiding users to the three resolution options

## 1.0.1 - 2025-11-21

### Fixed

- Fixed invalid `fields=All` parameter issue - now dynamically fetches actual field names from Zoho CRM API
- Field names are now automatically retrieved from `/settings/fields` endpoint for each module
- Added field name caching to improve performance and reduce API calls
- Added required `fields` parameter to all GET requests to comply with Zoho CRM v8 API requirements
- Updated `all()`, `find()`, `search()`, `getRelatedRecords()`, and `getDeletedRecords()` methods to use actual field names
- Fixed "REQUIRED_PARAM_MISSING" error when fetching records from Zoho CRM
- Added missing OAuth callback route (`/zoho/callback`) to handle authorization flow

### Added

- New `getModuleFieldNames()` method to fetch and cache field names for each module
- New `getDefaultFields()` fallback method for when field metadata fetch fails
- Automatic fallback to common system fields if field metadata fetch fails

### Changed

- `default_fields` configuration is now deprecated - field names are fetched automatically
- `find()` method now accepts optional `$params` array as second parameter
- `getRelatedRecords()` method now accepts optional `$params` array as third parameter

### Deprecated

- `default_fields` configuration option (kept for backward compatibility but no longer used)

## 1.0.0 - 2024-01-01

### Added

- Initial release
- Model-like interface for all Zoho CRM modules
- Automatic OAuth token management with hybrid cache + database storage
- Full CRUD operations (Create, Read, Update, Delete, Search, Upsert)
- Webhook support with Laravel events
- Comprehensive Artisan commands:
  - `zoho:setup` - Interactive OAuth setup
  - `zoho:auth` - Authentication management
  - `zoho:test` - CRUD operations testing
  - `zoho:sync` - Data synchronization
  - `zoho:token:refresh` - Token refresh
- Support for all Zoho CRM data centers (US, EU, IN, CN, JP, AU, CA)
- Custom exception classes for better error handling
- Laravel 11+ support
- PHP 8.2+ support

### Features

- ZohoContact, ZohoAccount, ZohoLead, ZohoDeal models
- ZohoTask, ZohoEvent, ZohoCall, ZohoNote models
- ZohoProduct, ZohoInvoice models
- Facade support for fluent interface
- Webhook signature verification
- Automatic token refresh
- Related records support
- Batch operations (update/delete multiple records)
- Lead conversion support
- Record count and search capabilities
- Clone record functionality
