<?php

namespace LeKoala\PhoneNumber;

use Exception;
use SilverStripe\Forms\FieldGroup;
use libphonenumber\PhoneNumberFormat;
use SilverStripe\Forms\DropdownField;

/**
 * A country/phone combo field
 */
class CountryPhoneField extends FieldGroup
{
    public function __construct($name, $title = null, $value = null)
    {
        $country = new DropdownField($name . "[CountryCode]", "");
        $country->setSource(PhoneHelper::getCountriesList());
        $country->setHasEmptyDefault(true);
        $country->setAttribute('style', 'max-width:166px'); // Match FieldGroup min width
        $country->setAttribute('size', 1); // fix some weird sizing issue in cms

        $number = new PhoneField($name . "[Number]", "");

        parent::__construct($title, $country, $number);

        $this->name = $name;
    }


    public function hasData()
    {
        // Turn this into a datafield
        return true;
    }

    /**
     * @return DropdownField
     */
    public function getCountryField()
    {
        return $this->fieldByName($this->name . "[CountryCode]");
    }

    /**
     * @return PhoneField
     */
    public function getPhoneField()
    {
        return $this->fieldByName($this->name . "[Number]");
    }

    public function setValue($value, $data = null)
    {
        // An array of value to assign to sub fields
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $this->fieldByName($this->name . "[$k]")->setValue($v);
            }
            return;
        }
        // It's an international number
        if (strpos($value, '+') === 0) {
            $util = PhoneHelper::getPhoneNumberUtil();
            try {
                $number = $util->parse($value);
                $regionCode = $util->getRegionCodeForNumber($number);
                $this->getCountryField()->setValue($regionCode);
                $phone = $util->format($number, PhoneNumberFormat::NATIONAL);
                $this->getPhoneField()->setValue($phone);
            } catch (Exception $ex) {
                // We were unable to parse, simply set the value as is
                $this->getPhoneField()->setValue($phone);
            }
        } else {
            $this->getPhoneField()->setValue($value);
        }
    }

    /**
     * Value in E164 format (no formatting)
     *
     * @return string
     */
    public function dataValue()
    {
        $countryValue = $this->getCountryField()->Value();
        $phoneValue = $this->getPhoneField()->Value();
        if (!$phoneValue) {
            return '';
        }
        if (!$countryValue && strpos($phoneValue, '+') !== 0) {
            return $phoneValue;
        }

        $util = PhoneHelper::getPhoneNumberUtil();

        try {
            $number = $util->parse($phoneValue, $countryValue);
            return $util->format($number, PhoneNumberFormat::E164);
        } catch (Exception $ex) {
            // We were unable to parse, simply return the value as is
            return $phoneValue;
        }
    }
}
