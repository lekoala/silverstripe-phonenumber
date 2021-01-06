<?php

namespace LeKoala\PhoneNumber;

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
    private static $allowed_actions = [
        'validate',
        'format',
    ];

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
            return $this->httpError(400, 0);
        } catch (NumberParseException $e) {
            return $this->httpError(400, $e->getMessage());
        }
    }

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
        } catch (NumberParseException $e) {
            return $this->httpError(400, $e->getMessage());
        }
    }
}
