<?php

namespace LeKoala\PhoneNumber;

use LeKoala\PhoneNumber\Test\PhoneNumberTest;
use SilverStripe\Forms\TextField;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberType;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\Validator;

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
     * @var string|null
     */
    protected $countryCode = null;

    /**
     * @var bool
     */
    protected $isMobile = false;

    /**
     * @var int
     */
    protected $displayFormat = 1; // INTERNATIONAL

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
     * @param string $name
     * @param string|null $title
     * @param mixed $value
     */
    public function __construct($name, $title = null, $value = null)
    {
        // Autodetect mobile fields
        if (strpos(strtolower($name), 'mobile') !== false) {
            $this->isMobile = true;
        }
        parent::__construct($name, $title, $value);
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
                $this->countryCode = $countryValue;

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

        // We have an international number that we can format easily without knowing the country
        if ($isInternational) {
            $util = PhoneHelper::getPhoneNumberUtil();
            try {
                $number = $util->parse($value);
                $newValue = $util->format($number, $this->displayFormat);
            } catch (NumberParseException $ex) {
                $newValue = $value;
            }
            $value = $newValue;
        }
        return parent::setValue($value, $data);
    }

    /**
     * Validate this field
     *
     * @param Validator $validator
     * @return bool
     */
    public function validate($validator)
    {
        if ($this->isMobile && $this->value) {
            $util = PhoneHelper::getPhoneNumberUtil();
            $number = $util->parse($this->value, $this->countryCode);
            $type = $util->getNumberType($number);
            if (!in_array($type, [PhoneNumberType::FIXED_LINE_OR_MOBILE, PhoneNumberType::MOBILE])) {
                $validator->validationError(
                    $this->name,
                    _t('PhoneField.IsNotAMobileNumber', 'This is not a valid mobile number')
                );
            }
        }
        return parent::validate($validator);
    }

    /**
     * Value in E164 format (no formatting)
     *
     * @return string
     */
    public function dataValue()
    {
        $value = $this->Value();

        // It's an international number or we have a country set, format without spaces
        if (strpos((string)$value, '+') === 0 || $this->countryCode) {
            $util = PhoneHelper::getPhoneNumberUtil();
            try {
                $number = $util->parse($value, $this->countryCode);
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

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     * @return $this
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    /**
     * Get the value of isMobile
     * @return bool
     */
    public function getIsMobile()
    {
        return $this->isMobile;
    }

    /**
     * Set the value of isMobile
     *
     * @param bool $isMobile
     * @return $this
     */
    public function setIsMobile(bool $isMobile)
    {
        $this->isMobile = $isMobile;
        return $this;
    }

    /**
     * Get the value of displayFormat
     *
     * @return int
     */
    public function getDisplayFormat()
    {
        return $this->displayFormat;
    }

    /**
     * Set the value of displayFormat
     *
     * @param int $displayFormat
     * @return $this
     */
    public function setDisplayFormat($displayFormat)
    {
        $this->displayFormat = $displayFormat;
        return $this;
    }
}
