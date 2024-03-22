<?php

namespace LeKoala\PhoneNumber;

use SilverStripe\i18n\i18n;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;
use SilverStripe\i18n\Data\Intl\IntlLocales;

/**
 * Helps dealing with phone and countries
 */
class PhoneHelper
{
    const DEFAULT_FORMAT = 'NATIONAL';

    /**
     *
     * @var array<string>
     */
    protected static $valid_formats = [
        'E164',
        'INTERNATIONAL',
        'NATIONAL',
        'RFC3966'
    ];

    /**
     * Alias of PhoneNumberUtil::getInstance
     * @return PhoneNumberUtil
     */
    public static function getPhoneNumberUtil()
    {
        return PhoneNumberUtil::getInstance();
    }

    /**
     * @return string
     */
    public static function getDefaultCountryCode()
    {
        return substr(i18n::get_locale(), 3, 2);
    }

    /**
     * @param string $code
     * @return int|null
     */
    public static function convertCountryCodeToPrefix($code)
    {
        $code = strtoupper($code);
        $list  = self::listCountryPrefixes();
        return $list[$code] ?? null;
    }

    /**
     * @return array<string,int>
     */
    public static function listCountryPrefixes()
    {
        $countries = self::getCountriesList();
        $util = self::getPhoneNumberUtil();
        $map = [];
        foreach ($countries as $countryCode => $countryName) {
            $map[$countryCode] = $util->getCountryCodeForRegion($countryCode);
        }
        return $map;
    }

    /**
     * Get the country list, using IntlLocales
     *
     * Keys are set to UPPERCASE to match ISO standards
     *
     * @return array<string,string>
     */
    public static function getCountriesList()
    {
        $intl = new IntlLocales;
        $countries = $intl->getCountries();
        $countries = array_change_key_case($countries, CASE_UPPER);
        return $countries;
    }

    /**
     * Validate phone number. Error in formatting result in an NumberParseException
     * that you must catch yourself.
     *
     * @throws NumberParseException
     * @param string $value
     * @param string $country An ISO 3166-1 two letter country code (=> UPPERCASE).
     * @return bool
     */
    public static function validatePhoneNumber($value, $country = null)
    {
        $util = self::getPhoneNumberUtil();

        // Default country
        if ($country === null) {
            $country = self::getDefaultCountryCode();
        }

        // It's an international number, let the parser define the country
        if (strpos($value, '+') === 0) {
            $country = null;
        }

        $number = $util->parse($value, $country);
        return $util->isValidNumber($number);
    }

    /**
     * Format phone number. Error in formatting result in an NumberParseException
     * that you must catch yourself.
     *
     * @param string $value
     * @param string $country An ISO 3166-1 two letter country code.
     * @param string|int|null $format NATIONAL by default (=> UPPERCASE)
     * @return string
     */
    public static function formatPhoneNumber($value, $country = null, $format = null)
    {
        if (empty($value)) {
            return '';
        }
        $util = self::getPhoneNumberUtil();

        // Default country
        if ($country === null) {
            $country = self::getDefaultCountryCode();
        }

        // Format
        if ($format === null) {
            $format = self::DEFAULT_FORMAT;
        }
        if (is_string($format)) {
            $format = strtoupper($format);
            if (!(in_array($format, self::$valid_formats))) {
                $format = self::DEFAULT_FORMAT;
            }
            $format = constant("libphonenumber\PhoneNumberFormat::$format");
        }

        // It's an international number, let the parser define the country
        if (strpos($value, '+') === 0) {
            $country = null;
        }

        // Don't fail
        try {
            $number = $util->parse($value, $country);
        } catch (NumberParseException $e) {
            return $value;
        }

        // It's not valid, simply return the given value
        if (!$util->isValidNumber($number)) {
            return $value;
        }

        return $util->format($number, $format);
    }
}
