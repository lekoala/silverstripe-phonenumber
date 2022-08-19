<?php

namespace LeKoala\PhoneNumber;

use libphonenumber\PhoneNumberFormat;
use libphonenumber\NumberParseException;
use SilverStripe\ORM\FieldType\DBVarchar;

/**
 * Phone field type
 *
 * The internal value is stored in E164 (with country prefix and no space)
 *
 * @see https://github.com/giggsey/libphonenumber-for-php
 */
class DBPhone extends DBVarchar
{
    /**
     * @var array
     */
    protected static $valid_formats = [
        'E164',
        'INTERNATIONAL',
        'NATIONAL',
        'RFC3966'
    ];

    /**
     * @var string
     */
    protected $country = null;

    public function __construct($name = null, $options = [])
    {
        // E164 specify it should be smaller than 15 chars
        parent::__construct($name, 16, $options);
    }

    public function setValue($value, $record = null, $markChanged = true)
    {
        if ($record && strpos((string)$value, '+') !== 0) {
            if ($record->CountryCode) {
                $value = $this->parseNumber($value, $record->CountryCode);
            }
        }
        return parent::setValue($value, $record, $markChanged);
    }

    public function dataValue()
    {
        return $this->value;
    }

    /**
     * If the number is passed in an international format (e.g. +44 117 496 0123), then the region code is not needed, and can be null.
     * Failing that, the library will use the region code to work out the phone number based on rules loaded for that region.
     *
     * @param mixed $value
     * @param string $country
     * @return string|null|false Formatted number, null if empty but valid, or false if invalid
     */
    protected function parseNumber($value, $country = null)
    {
        // Skip empty values
        if (empty($value)) {
            return null;
        }

        // It's an international number, let the parser define the country
        if (strpos($value, '+') === 0) {
            $country = null;
        } else {
            // If no country and not international number, return value as is
            if (!$country) {
                return $value;
            }
            $country = strtoupper($country);
        }
        $util = PhoneHelper::getPhoneNumberUtil();
        try {
            $number = $util->parse($value, $country);
            $formattedValue = $util->format($number, PhoneNumberFormat::E164);
        } catch (NumberParseException $ex) {
            $formattedValue = $value;
        }
        return $formattedValue;
    }


    public function scaffoldFormField($title = null, $params = null)
    {
        $field = CountryPhoneField::create($this->name, $title);
        return $field;
    }

    /**
     * @return string The number in request format
     */
    public function Format($format = null)
    {
        if (!$this->value) {
            return null;
        }
        return PhoneHelper::formatPhoneNumber($this->value, $this->country, $format);
    }

    /**
     * Includes the country region code and start with a +
     * Eg: +441174960123
     *
     * @return string
     */
    public function E164()
    {
        return $this->Format(PhoneNumberFormat::E164);
    }

    /**
     * Same as E164 with spacing
     * Eg: +44 117 496 0123
     *
     * @return void
     */
    public function International()
    {
        return $this->Format(PhoneNumberFormat::INTERNATIONAL);
    }

    /**
     * With space and without country
     * Eg: 0117 496 0123
     *
     * @return void
     */
    public function National()
    {
        return $this->Format(PhoneNumberFormat::NATIONAL);
    }

    /**
     * Usable as URI
     * Eg: tel:+44-117-496-012
     *
     * @return string
     */
    public function Rfc3966()
    {
        return $this->Format(PhoneNumberFormat::RFC3966);
    }

    /**
     * @return boolean
     */
    public function isValid()
    {
        return PhoneHelper::validatePhoneNumber($this->value);
    }
}
