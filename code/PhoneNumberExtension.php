<?php

/**
 * PhoneNumberExtension
 *
 * @author lekoala
 */
class PhoneNumberExtension extends DataExtension
{
    private static $db = array(
        'CountryCode' => 'Varchar(2)',
        'Phone' => 'Varchar(255)',
        'MobilePhone' => 'Varchar(255)',
    );

    public function updateCMSFields(FieldList $fields)
    {

        $fields->AddFieldToTab('Root.Main',
            new CountryDropdownField('CountryCode',
            _t('LibPhoneNumberExtension.CountryCode', 'Country')));
        $fields->AddFieldToTab('Root.Main',
            $phonefield       = new LibPhoneNumberField('Phone',
            _t('LibPhoneNumberExtension.PhoneNumber', 'Phone')));
        $fields->AddFieldToTab('Root.Main',
            $mobilephonefield = new LibPhoneNumberField('MobilePhone',
            _t('LibPhoneNumberExtension.MobilePhoneNumber', 'Mobile Phone')));

        $phonefield->setCountryField('CountryCode');
        $mobilephonefield->setCountryField('CountryCode');

        return $fields;
    }
}