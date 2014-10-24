<?php

/**
 * LibPhoneNumberController
 *
 * @author lekoala
 */
class LibPhoneNumberController extends Controller {

	private static $allowed_actions = array('validate', 'format');

	public function validate(SS_HTTPRequest $request) {
		$rawNumber = $request->getVar('number');
		$country = $request->getVar('country');
		try {
			$result = LibPhoneNumberField::validatePhoneNumber($rawNumber, $country);
			if($result) {
				return 1;
			}
			$this->httpError(400,0);
		} catch (\libphonenumber\NumberParseException $e) {
			SS_Log::log($e->getMessage(), SS_Log::DEBUG);
			return $this->httpError(400, $e->getMessage());
		}
	}

	public function format(SS_HTTPRequest $request) {
		$rawNumber = $request->getVar('number');
		$country = $request->getVar('country');
		$format = $request->getVar('format');
		try {
			return LibPhoneNumberField::formatPhoneNumber($rawNumber, $country, $format);
		} catch (\libphonenumber\NumberParseException $e) {
			SS_Log::log($e->getMessage(), SS_Log::DEBUG);
			return $this->httpError(400, $e->getMessage());
		}
	}

}
