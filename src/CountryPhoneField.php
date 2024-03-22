<?php

namespace LeKoala\PhoneNumber;

use Exception;
use SilverStripe\Forms\FieldGroup;
use libphonenumber\PhoneNumberFormat;
use SilverStripe\Forms\DropdownField;
use SilverStripe\ORM\DataObject;

/**
 * A country/phone combo field
 * You might want to use https://github.com/lekoala/silverstripe-form-elements/blob/master/src/TelInputField.php instead
 */
class CountryPhoneField extends FieldGroup
{
    /**
     * @var int
     */
    protected $dataformat = 0; // E164, see libphonenumber/PhoneNumberFormat

    public function __construct($name, $title = null, $value = null)
    {
        PhoneHelper::listCountryPrefixes();
        $country = new DropdownField($name . "[CountryCode]", "");
        $country->setSource(PhoneHelper::getCountriesList());
        $country->setHasEmptyDefault(true);
        $country->setAttribute('style', 'max-width:166px'); // Match FieldGroup min width
        $country->setAttribute('size', 1); // fix some weird sizing issue in cms

        $number = new PhoneField($name . "[Number]", "");
        $number->setAttribute('data-value', $value);

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

    /**
     * @return int
     */
    public function getDataFormat()
    {
        return $this->dataformat;
    }

    /**
     * @param int $v Any of the libphonenumber/PhoneNumberFormat constant
     * @return $this
     */
    public function setDataFormat($v)
    {
        $this->dataformat = $v;
        return $this;
    }

    /**
     * @param mixed $value Either the parent object, or array of source data being loaded
     * @param array<mixed>|DataObject|null $data {@see Form::loadDataFrom}
     * @return $this
     */
    public function setValue($value, $data = null)
    {
        $this->fieldByName($this->name . "[Number]")->setAttribute('data-value', $value);

        // An array of value to assign to sub fields
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $this->fieldByName($this->name . "[$k]")->setValue($v);
            }
            return $this;
        }
        // It's an international number
        if (strpos((string)$value, '+') === 0) {
            $util = PhoneHelper::getPhoneNumberUtil();
            try {
                $number = $util->parse($value);
                $regionCode = $util->getRegionCodeForNumber($number);
                $this->getCountryField()->setValue($regionCode);
                $phone = $util->format($number, PhoneNumberFormat::NATIONAL);
                $this->getPhoneField()->setValue($phone);
            } catch (Exception $ex) {
                // We were unable to parse, simply set the value as is
                $this->getPhoneField()->setValue($value);
            }
        } else {
            $this->getPhoneField()->setValue($value);
        }
        return $this;
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
        if (!$countryValue && strpos((string)$phoneValue, '+') !== 0) {
            return $phoneValue;
        }

        $util = PhoneHelper::getPhoneNumberUtil();

        try {
            $number = $util->parse($phoneValue, $countryValue);
            $format = $this->dataformat;
            return $util->format($number, $format);
        } catch (Exception $ex) {
            // We were unable to parse, simply return the value as is
            return $phoneValue;
        }
    }
}
