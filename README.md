# Zoho

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

This package used to integrate with the new Zoho CRM

## Requirements

* Get yourself a [Zoho CRM account](https://www.zoho.com/crm/).
* [Register your application](https://www.zoho.com/crm/developer/docs/php-sdk/clientapp.html)
* PHP >= 5.6.4
* Laravel >= 6.*

## Installation

Add Zoho CRM to your composer file via the `composer require` command:

```bash
$ composer require asciisd/zoho
```

Or add it to `composer.json` manually:

```json
"require": {
    "asciisd/zoho": "0.0.1"
}
```

Zoho CRM's service providers will be automatically registered using Laravel's auto-discovery feature.

## Configuration

The defaults configuration settings are set in `config/zoho.php`. Copy this file to your own config directory to modify the values. You can publish the config using this command:

```bash
$ php artisan zoho:install
```

You'll need to add the following variables to your .env file. Use the credentials previously obtained registering your application.

```dotenv
ZOHO_CLIENT_ID=
ZOHO_CLIENT_SECRET=
ZOHO_REDIRECT_URI=
ZOHO_CURRENT_USER_EMAIL=
```

Then, follow the next steps:
1. Go to [Zoho CRM Developer Console](https://accounts.zoho.com/developerconsole).
2. ADD CLIENT `Server-based Applications`.
3. Enter Client Name `Any name you want`
4. Enter Homepage URL `your base home url`
5. Enter Authorized Redirect URIs `config('app.url') . /zoho/oauth2callback`
6. Go to your project location on terminal and enter
    ```bash
    php artisan zoho:authenticate
    ```
7. Copy the generated link and past it in the browser to complete the oAuth process.

**Now Zoho CRM is ready to use.**

## Testing

before testing make sure to create file ZCRMClientLibrary.log on 
```text
tests/Fixture/Storage/oauth/logs/ZCRMClientLibrary.log
```

and put your zcrm_oauthtokens.txt on 
```text
tests/Fixture/Storage/oauth/tokens/zcrm_oauthtokens.txt
```

finally put your Env keys
```dotenv
ZOHO_CLIENT_ID=
ZOHO_CLIENT_SECRET=
ZOHO_REDIRECT_URI=
ZOHO_CURRENT_USER_EMAIL=
```

# How to use
use **ZOHO** Facade like this
```php
use Asciisd\Zoho\Facades\ZohoManager;

// we can now deals with leads module
$leads = ZohoManager::useModule('Leads');
```

this will return an instance of **ZohoModules**

## CRUD Can be used like this:-

#### READ

```php
use Asciisd\Zoho\Facades\ZohoManager;

// we can now deals with leads module
$leads = ZohoManager::useModule('Leads');

// find record by it's ID
$lead = $leads->getRecord('3582074000002383003');
```

#### UPDATE

```php
// find record by it's ID
$lead = $leads->getRecord('3582074000002383003');

// Set field with new value
$lead->setFieldValue('Last_Name', 'Ahmed');

// Then call update() method
$lead = $lead->update()->getData();
```

#### CREATE

```php
// initiating a new empty instance of leads
$record = $leads->getRecordInstance();

// fill this instance with data
$record->setFieldValue('First_Name', 'Amr');
$record->setFieldValue('Last_Name', 'Emad');
$record->setFieldValue('Email', 'test@asciisd.com');
$record->setFieldValue('Phone', '012345678910');

// create the record into zoho crm then get the created instance data
$lead = $record->create()->getData();

```

#### DELETE
```php
// find record by it's ID
$lead = $leads->getRecord('3582074000002383003');

$lead->delete();
```

#### SEARCH

##### Word
```php
use Asciisd\Zoho\Facades\ZohoManager;

$records = ZohoManager::useModule('Leads')->searchRecordsByWord('word to be searched');
$first_record = $records[0];
```

##### Phone
```php
use Asciisd\Zoho\Facades\ZohoManager;

$records = ZohoManager::useModule('Leads')->searchRecordsByPhone('12345678910');
$first_record = $records[0];
```

##### Email
```php
use Asciisd\Zoho\Facades\ZohoManager;

$records = ZohoManager::useModule('Leads')->searchRecordsByEmail('nobody@asciisd.com');
$first_record = $records[0];
```

##### Criteria
```php
use Asciisd\Zoho\Facades\ZohoManager;

$records = ZohoManager::useModule('Leads')->searchRecordsByCriteria('(City:equals:NY) and (State:equals:Alden)');
$first_record = $records[0];
```

##### Custom
```php
use Asciisd\Zoho\Facades\ZohoManager;

$records = ZohoManager::useModule('Leads')
                    ->where('City', 'NY')
                    ->andWhere('State','Alden')
                    ->search();

$first_record = $records[0];
```


## License

[MIT License](https://opensource.org/licenses/MIT). Copyright (c) 2020, Asciisd

## Support

Contact:<br>
[asciisd.com](https://asciisd.com)<br>
aemad@asciisd.com<br>
+2-010-1144-1444

[ico-version]: https://img.shields.io/packagist/v/asciisd/zoho.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/asciisd/zoho.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/asciisd/zoho.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/asciisd/zoho.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/asciisd/zoho
[link-scrutinizer]: https://scrutinizer-ci.com/g/asciisd/zoho/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/asciisd/zoho
[link-downloads]: https://packagist.org/packages/asciisd/zoho
[link-author]: https://github.com/asciisd
[link-contributors]: ../../contributors
