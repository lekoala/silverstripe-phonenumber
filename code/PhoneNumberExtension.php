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

    /**
     * Make a lookup with Twilio
     * 
     * @param string $phone
     * @param string $country 2 chars country code
     * @return string A json string
     * @throws Exception
     */
    public static function twilioLookup($phone, $country)
    {
        if(strlen($country) != 2) {
            throw new Exception("Country code must be 2 characters long");
        }
        $username = TWILIO_ACCOUNT_SID;
        $password = TWILIO_AUTH_TOKEN;
        $url      = 'https://lookups.twilio.com/v1/PhoneNumbers/'.urlencode($phone).'?CountryCode='.$country;

        $context = stream_context_create(array(
            'http' => array(
                'method' => 'GET',
                'header' => "Authorization: Basic ".base64_encode("$username:$password")
            )
        ));
        return @file_get_contents($url, false, $context);
    }

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