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
    const COUNTRY_CODE_FIELD = "CountryCode";
    const NUMBER_FIELD = 'Number';
    /**
     * @var int
     */
    protected $dataformat = 0; // E164, see libphonenumber/PhoneNumberFormat

    /**
     * @param string $name
     * @param string|null $title
     * @param mixed $value
     */
    public function __construct($name, $title = null, $value = null)
    {
        $country = new DropdownField($name . "[" . self::COUNTRY_CODE_FIELD . "]", "");
        $country->setSource(PhoneHelper::getCountriesList());
        $country->setHasEmptyDefault(true);
        $country->setAttribute('style', 'max-width:166px'); // Match FieldGroup min width
        $country->setAttribute('size', 1); // fix some weird sizing issue in cms

        $number = new PhoneField($name . "[" . self::NUMBER_FIELD . "]", "");
        $number->setAttribute('data-value', $value);
        // We can use a national friendly format because it's next to the country
        $number->setDisplayFormat(PhoneNumberFormat::NATIONAL);

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
        return $this->fieldByName($this->name . "[" . self::COUNTRY_CODE_FIELD . "]");
    }

    /**
     * @return PhoneField
     */
    public function getPhoneField()
    {
        return $this->fieldByName($this->name . "[" . self::NUMBER_FIELD . "]");
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
        $this->fieldByName($this->name . "[" . self::NUMBER_FIELD . "]")->setAttribute('data-value', $value);

        // An array of value to assign to sub fields
        if (is_array($value)) {
            $countryCode = $value[self::COUNTRY_CODE_FIELD] ?? null;
            $number = $value[self::NUMBER_FIELD] ?? null;
            if ($countryCode) {
                $this->getCountryField()->setValue($countryCode);
            }
            if ($number) {
                $this->getPhoneField()->setValue($number);
                $this->getPhoneField()->setCountryCode($countryCode);
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

    /**
     * Get the value of isMobile
     * @return bool
     */
    public function getIsMobile()
    {
        return $this->getPhoneField()->getIsMobile();
    }

    /**
     * Set the value of isMobile
     *
     * @param bool $isMobile
     * @return $this
     */
    public function setIsMobile(bool $isMobile)
    {
        $this->getPhoneField()->setIsMobile($isMobile);
        return $this;
    }
}
