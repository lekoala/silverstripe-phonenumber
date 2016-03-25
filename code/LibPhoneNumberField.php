<?php

/**
 * LibPhoneNumberField
 *
 * @author lekoala
 */
class LibPhoneNumberField extends TextField {

	/**
	 * @var string
	 */
	protected $countryCode = null;

	/**
	 *
	 * @var string
	 */
	protected $format = null;

	/**
	 *
	 * @var string
	 */
	protected $countryField = null;

	/**
	 *
	 * @var array
	 */
	protected static $valid_formats = array('E164', 'INTERNATIONAL', 'NATIONAL', 'RFC3966');

	public function __construct($name, $title = null, $value = '', $countryCode = null) {

		$this->countryCode = $countryCode;

		parent::__construct($name, $title, $value);
	}

	public function hasCountryCode() {
		return isset($this->countryCode);
	}

	public function getCountryCode() {
		return $this->countryCode;
	}

	public function setCountryCode($value) {
		$this->countryCode = $value;
		return $this;
	}

	public function clearCountryCode() {
		$this->countryCode = null;
		return $this;
	}

	public function hasCountryField() {
		return isset($this->countryField);
	}

	public function getCountryField() {
		return $this->countryField;
	}

	public function setCountryField($value) {
		$this->countryField = $value;
		return $this;
	}

	public function clearCountryField() {
		$this->countryField = null;
		return $this;
	}

	public function hasFormat() {
		return isset($this->format);
	}

	public function getFormat() {
		return $this->format;
	}

	public function setFormat($value) {
		$this->format = $value;
		return $this;
	}

	public function clearFormat() {
		$this->format = null;
		return $this;
	}

	public function setValue($value) {
		try {
			$formattedValue = self::formatPhoneNumber($value);
		} catch (\libphonenumber\NumberParseException $ex) {
			$formattedValue = $value;
		}
		$this->value = $formattedValue;
		return $this;
	}

	/**
	 * Format phone number. Error in formatting result in an NumberParseException
	 * that you must catch yourself.
	 *
	 * @throws libphonenumber\NumberParseException
	 * @param string $value
	 * @param string $country
	 * @param string $format
	 * @return string
	 */
	public static function formatPhoneNumber($value, $country = null, $format = 'NATIONAL') {
		if (empty($value)) {
			return '';
		}
		$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();

		if ($country === null) {
			$country = self::getDefaultCountryCode();
		}
		if (!(in_array($format, self::$valid_formats))) {
			$format = 'NATIONAL';
		}
		if (strpos($value, '+') === 0) {
			$country = null;
		}

		$number = $phoneUtil->parse($value, $country);
		if (!$phoneUtil->isValidNumber($number)) {
			return $value;
		}
		return $phoneUtil->format($number, constant("libphonenumber\PhoneNumberFormat::$format"));
	}

	/**
	 *
	 * @param string $value
	 * @param string $country
	 * @param string $format
	 * @return bool
	 */
	public static function validatePhoneNumber($value, $country = null) {
		$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
		if ($country === null) {
			$country = self::getDefaultCountryCode();
		}
		if (strpos($value, '+') === 0) {
			$country = null;
		}
		$number = $phoneUtil->parse($value, $country);
		return $phoneUtil->isValidNumber($number);
	}

	/**
	 * Get country code to use (default, custom or field based)
	 * @return string
	 */
	public function getTargetCountryCode() {
		$countryCode = $this->countryCode;
		if ($this->countryField && $this->form) {
			$field = $this->form->Fields()->dataFieldByName($this->countryField);
			if ($field) {
				$countryCode = $field->Value();
			}
		}
		if (empty($countryCode)) {
			$countryCode = self::getDefaultCountryCode();
		}
		return $countryCode;
	}

	public static function getDefaultCountryCode() {
		return substr(i18n::get_locale(), 3, 2);
	}

	public function getFormattedValue() {
		return self::formatPhoneNumber($this->value, $this->getTargetCountryCode(), $this->format);
	}

	public function saveInto(DataObjectInterface $record) {
		$fieldName = $this->name;
		$record->$fieldName = $this->getFormattedValue();
	}

	/**
	 * Please note that we do NOT validate an empty field. Add a required
	 * constraint if you want a value
	 *
	 * @param Validator $validator
	 * @return boolean
	 */
	public function validate($validator) {
		if (empty($this->value)) {
			return true;
		}

		try {
			$valid = self::validatePhoneNumber($this->value, $this->getTargetCountryCode());
		} catch (\libphonenumber\NumberParseException $e) {
			$valid = false; //if it wasn't parsed properly, it's probably not valid
		}
		if (!$valid) {
			$validator->validationError(
					$this->name, _t('PhoneNumberField.VALIDATION', "Please enter a valid phone number"), "validation", false
			);
			return false;
		}

		return true;
	}

	public static function isBackendController() {
		return is_subclass_of(Controller::curr(), "LeftAndMain");
	}

	public function Field($properties = array()) {
		Requirements::javascript('phonenumber/javascript/LibPhoneNumberField.js');
		$this->setAttribute('data-remote', '/libphonenumber/format');
		$this->setAttribute('data-country', $this->countryCode);
		$this->setAttribute('data-format', $this->format);
		if ($this->countryField) {
			$this->setAttribute('data-countryfield', $this->countryField);
		}
		return parent::Field($properties);
	}

	public function extraClass() {
		$class = parent::extraClass();
		return 'text ' . $class;
	}

}
