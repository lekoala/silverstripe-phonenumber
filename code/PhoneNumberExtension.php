<?php

/**
 * LibPhoneNumberExtension
 *
 * @author lekoala
 */
class LibPhoneNumberExtension extends DataExtension {

	private static $db = array(
		'CountryCode' => 'Varchar(2)',
		'PhoneNumber' => 'Varchar(255)',
	);

	public function updateCMSFields(FieldList $fields) {

		$fields->AddFieldToTab('Root.Main', new CountryDropdownField('CountryCode', _t('LibPhoneNumberExtension.CountryCode','Country')));
		$fields->AddFieldToTab('Root.Main', $phonefield = new LibPhoneNumberField('PhoneNumber', _t('LibPhoneNumberExtension.PhoneNumber','Phone Number')));
		$phonefield->setCountryField('CountryCode');
		
		return $fields;
	}

}
