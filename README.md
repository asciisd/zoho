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
2. Under the Client previously registered, click the vertical three points then `Self Client`.
3. Enter the default scope , then click `View Code`
```text
aaaserver.profile.READ,ZohoCRM.modules.ALL,ZohoCRM.settings.ALL
```    
> If you want to apply a different scope, see the [link](https://www.zoho.com/crm//developer/docs/api/oauth-overview.html#scopes)

4. Copy the generated code.

Finally, run the following command:

```bash
$ php artisan zoho:grant {generated_grant_token}
```

Enter the previously generated code.

**Zoho CRM is ready to use.**

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
// we can now deals with leads module
$leads = Zoho::useModule('Leads');
```

this will return an instance of **ZohoModules**

## CRUD Can be used like this:-

**READ**

```php
// we can now deals with leads module
$leads = Zoho::useModule('Leads');

// find record by it's ID
$lead = $leads->getRecord('3582074000002383003');
```

**UPDATE**

```php
// find record by it's ID
$lead = $leads->getRecord('3582074000002383003');

// Set field with new value
$lead->setFieldValue('Last_Name', 'Ahmed');

// Then call update() method
$lead = $lead->update()->getData();

and that's it
```

**CREATE**

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

**DELETE**
```php
// find record by it's ID
$lead = $leads->getRecord('3582074000002383003');

$lead->delete();

```

**SEARCH**

you can search by word
```php
// get module records
$records = Zoho::useModule('Trading_Accounts')->searchRecordsByWord('word to be searched');
$first_record = $records[0];

//OR
$records = Zoho::useModule('Trading_Accounts')->searchRecordsByPhone('phone number');
$first_record = $records[0];

//OR
$records = Zoho::useModule('Trading_Accounts')->searchRecordsByEmail('email address');
$first_record = $records[0];

```



## License

[MIT License](https://opensource.org/licenses/MIT). Copyright (c) 2020, Asciisd

## Support

Contact:<br>
[asciisd.com](https://asciisd.com)<br>
aemad@asciisd.com<br>
+2-010-1144-1444
