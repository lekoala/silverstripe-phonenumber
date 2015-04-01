<?php

/**
 * LibPhoneNumberController
 *
 * @author lekoala
 */
class LibPhoneNumberController extends Controller
{
    private static $allowed_actions = array('validate', 'format', 'lookup');

    /**
     * Make a lookup using twilio
     *
     * Full response (paid):
     * {"country_code": "US", "phone_number": "+15108675309", "national_format": "(510) 867-5309", "carrier": {"mobile_country_code": "310", "mobile_network_code": "120", "name": "Sprint Spectrum, L.P.", "type": "mobile", "error_code": null}, "url": "https://lookups.twilio.com/v1/PhoneNumbers/+15108675309?Type=carrier"}
     *
     * Short response (free):
     * {"country_code": "US", "phone_number": "+15108675309", "national_format": "(510) 867-5309", "carrier": null, "url": "https://lookups.twilio.com/v1/PhoneNumbers/+15108675309"}
     */
    public function lookup(SS_HTTPRequest $request)
    {
        $fieldname = $request->getVar('field') ? $request->getVar('field') : 'number';
        $rawNumber = $request->getVar($fieldname);
        //as a fallback solution, we get the first $_GET parameter
        if (!$rawNumber) {
            $qs        = array_values($_GET);
            array_shift($qs); //remove first that is always url
            $rawNumber = array_shift($qs);
        }
        $country = $request->getVar('country');

        try {
            $data = PhoneNumberExtension::twilioLookup($rawNumber, $country);
        } catch (Exception $e) {
            SS_Log::log($e->getMessage(), SS_Log::DEBUG);
            return $this->httpError(400, $e->getMessage());
        }
        if (!$data) {
            return $this->httpError(400, 0);
        }

        return $data;
    }

    public function validate(SS_HTTPRequest $request)
    {
        $fieldname = $request->getVar('field') ? $request->getVar('field') : 'number';
        $rawNumber = $request->getVar($fieldname);
        //as a fallback solution, we get the first $_GET parameter
        if (!$rawNumber) {
            $qs        = array_values($_GET);
            array_shift($qs); //remove first that is always url
            $rawNumber = array_shift($qs);
        }
        $country = $request->getVar('country');
        try {
            $result = LibPhoneNumberField::validatePhoneNumber($rawNumber,
                    $country);
            if ($result) {
                return 1;
            }
            return $this->httpError(400, 0);
        } catch (\libphonenumber\NumberParseException $e) {
            SS_Log::log($e->getMessage(), SS_Log::DEBUG);
            return $this->httpError(400, $e->getMessage());
        }
    }

    public function format(SS_HTTPRequest $request)
    {
        $rawNumber = $request->getVar('number');
        $country   = $request->getVar('country');
        $format    = $request->getVar('format');
        try {
            return LibPhoneNumberField::formatPhoneNumber($rawNumber, $country,
                    $format);
        } catch (\libphonenumber\NumberParseException $e) {
            SS_Log::log($e->getMessage(), SS_Log::DEBUG);
            return $this->httpError(400, $e->getMessage());
        }
    }
}