<?php

namespace LeKoala\PhoneNumber;

use Exception;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use libphonenumber\NumberParseException;

/**
 * LibPhoneNumberController
 *
 * @author lekoala
 */
class LibPhoneNumberController extends Controller
{
    /**
     * @var array<string>
     */
    private static $allowed_actions = [
        'validate',
        'format',
    ];

    /**
     * @param HTTPRequest $request
     * @return int
     */
    public function validate(HTTPRequest $request)
    {
        $fieldname = $request->getVar('field') ? $request->getVar('field') : 'number';
        $rawNumber = $request->getVar($fieldname);
        // as a fallback solution, we get the first $_GET parameter
        if (!$rawNumber) {
            $qs        = array_values($_GET);
            array_shift($qs); //remove first that is always url
            $rawNumber = array_shift($qs);
        }
        $country = $request->getVar('country');
        try {
            $result = PhoneHelper::validatePhoneNumber(
                $rawNumber,
                $country
            );
            if ($result) {
                return 1;
            }
        } catch (Exception $e) {
            // Not valid
        }
        $this->getResponse()->setStatusCode(400);
        return 0;
    }

    /**
     * @param HTTPRequest $request
     * @return string The formatted number
     */
    public function format(HTTPRequest $request)
    {
        $rawNumber = $request->getVar('number');
        $country   = $request->getVar('country');
        $format    = $request->getVar('format');
        try {
            return PhoneHelper::formatPhoneNumber(
                $rawNumber,
                $country,
                $format
            );
        } catch (Exception $e) {
            // Unable to parse, return rawNumber
            return $rawNumber;
        }
    }
}
