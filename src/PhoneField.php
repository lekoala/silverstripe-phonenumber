<?php

namespace LeKoala\PhoneNumber;

use SilverStripe\Forms\TextField;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\NumberParseException;
use SilverStripe\ORM\DataObject;

/**
 * A simple phone field
 *
 * Formatting only works with international number because we don't know the country
 *
 * For national numbers, use CountryPhoneField that use a combination of CountryCode + PhoneNumber field
 */
class PhoneField extends TextField
{
    /**
     * @var string|null
     */
    protected $countryField = null;

    /**
     * @return string
     */
    public function getInputType()
    {
        return 'phone';
    }

    /**
     * @return string
     */
    public function Type()
    {
        return 'text';
    }

    /**
     * @param mixed $value Either the parent object, or array of source data being loaded
     * @param array<mixed>|DataObject|null $data {@see Form::loadDataFrom}
     * @return $this
     */
    public function setValue($value, $data = null)
    {
        $isInternational = strpos((string)$value, '+') === 0;
        if (!$isInternational && $this->countryField) {
            if (isset($data[$this->countryField])) {
                $countryValue = $data[$this->countryField];

                if (strpos((string)$countryValue, '+') === 0) {
                    // It's a + prefix, eg +33, +32
                    $value = $countryValue . ltrim((string)$value, "0");
                } elseif (is_numeric($countryValue)) {
                    // It's a plain prefix, eg 33, 32
                    $value = '+' . $countryValue . ltrim((string)$value, "0");
                } else {
                    // It's a country code (FR, BE...)
                    $countryValue = PhoneHelper::convertCountryCodeToPrefix($countryValue);
                    if ($countryValue) {
                        $value = '+' . $countryValue . ltrim((string)$value, "0");
                    }
                }
            }

            // Test again!
            $isInternational = strpos((string)$value, '+') === 0;
        }
        // We have an international number that we can format easily
        // without knowing the country
        if ($isInternational) {
            $util = PhoneHelper::getPhoneNumberUtil();
            try {
                $number = $util->parse($value);
                $newValue = $util->format($number, PhoneNumberFormat::INTERNATIONAL);
            } catch (NumberParseException $ex) {
                $newValue = $value;
            }
            $value = $newValue;
        }
        return parent::setValue($value, $data);
    }

    /**
     * Value in E164 format (no formatting)
     *
     * @return string
     */
    public function dataValue()
    {
        $value = $this->Value();
        if (strpos((string)$value, '+') === 0) {
            $util = PhoneHelper::getPhoneNumberUtil();
            try {
                $number = $util->parse($value);
                $formatted = $util->format($number, PhoneNumberFormat::E164);
            } catch (NumberParseException $ex) {
                $formatted = $value;
            }
            return $formatted;
        }
        return $value;
    }

    /**
     * Name of the country field
     *
     * @return string
     */
    public function getCountryField()
    {
        return $this->countryField;
    }

    /**
     * Name of the country field
     *
     * @param string $countryField
     * @return $this
     */
    public function setCountryField($countryField)
    {
        $this->countryField = $countryField;
        return $this;
    }
}
