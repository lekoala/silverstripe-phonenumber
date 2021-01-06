<?php

namespace LeKoala\PhoneNumber;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;

/**
 * PhoneNumberExtension
 *
 * @author lekoala
 */
class PhoneNumberExtension extends DataExtension
{
    private static $db = array(
        'CountryCode' => 'Varchar(2)',
        'PhoneNumber' => DBPhone::class,
        'MobilePhoneNumber' => DBPhone::class,
    );

    public function updateCMSFields(FieldList $fields)
    {
        $CountryCode = new DropdownField(
            'CountryCode',
            _t('LibPhoneNumberExtension.CountryCode', 'Country')
        );
        $CountryCode->setSource(PhoneHelper::getCountriesList());
        $fields->addFieldToTab('Root.Main', $CountryCode);

        $PhoneNumber = new PhoneField(
            'PhoneNumber',
            _t('LibPhoneNumberExtension.PhoneNumber', 'Phone')
        );
        $PhoneNumber->setCountryField('CountryCode');
        $fields->addFieldToTab('Root.Main', $PhoneNumber);


        $MobilePhoneNumber = new PhoneField(
            'MobilePhoneNumber',
            _t('LibPhoneNumberExtension.MobilePhoneNumber', 'Mobile Phone')
        );
        $MobilePhoneNumber->setCountryField('CountryCode');
        $fields->addFieldToTab('Root.Main', $MobilePhoneNumber);

        return $fields;
    }
}
