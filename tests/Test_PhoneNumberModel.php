<?php

namespace LeKoala\PhoneNumber\Test;

use SilverStripe\ORM\DataObject;
use SilverStripe\Dev\TestOnly;
use LeKoala\PhoneNumber\DBPhone;

class Test_PhoneNumberModel extends DataObject implements TestOnly
{
    private static $db = [
        "Phone" =>  DBPhone::class,
        "CountryCode" => 'Varchar(2)',
    ];
    private static $table_name = 'PhoneNumberModel';
}
