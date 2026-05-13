<?php

namespace Asciisd\Zoho\Tests\Mocks;

use Asciisd\Zoho\Traits\SyncsWithZoho;
use Illuminate\Database\Eloquent\Model;

class TestCustomer extends Model
{
    use SyncsWithZoho;

    protected $table = 'test_customers';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
    ];

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
            'company' => 'Company',
        ];
    }
}
