# SilverStripe PhoneNumber module

[![Build Status](https://travis-ci.com/lekoala/silverstripe-phonenumber.svg?branch=master)](https://travis-ci.com/lekoala/silverstripe-phonenumber/)
[![scrutinizer](https://scrutinizer-ci.com/g/lekoala/silverstripe-phonenumber/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/lekoala/silverstripe-phonenumber/)
[![Code coverage](https://codecov.io/gh/lekoala/silverstripe-phonenumber/branch/master/graph/badge.svg)](https://codecov.io/gh/lekoala/silverstripe-phonenumber)

## Intro

Provide some helper services to deal with phone numbers in SilverStripe

This module integrates libphonenumber as the utility to parse and validate
phonenumbers.

Most of the time, it's a good idea to set the country of the phone number.
Otherwise, current locale will be used.

Sample code:

	$phone = new PhoneField('phone', 'Phone number');
	$phone->setCountryField('CountryCode');

	$validator = ZenValidator::create();
	$validator->setConstraint('phone', Constraint_remote::create('/__phonenumber/validate',null,array('data' => array('country' => 'BE'))));

This module also provide an extension to apply to dataobject, for example to members

	SilverStripe\Security\Member:
      extensions:
        - LeKoala\PhoneNumber\PhoneNumberExtension

## Phone DBField

You can set your DataObject db field to Phone or DBPhone::class

This will automatically scaffold a CountryPhoneField which is a combo field with a country dropdown (with list of prefixes) and a space for the field itself

## Form fields

Two available fields:
- PhoneField : a plain phone field that supports national and international numbers
- CountryPhoneField : a combo field with a country dropdown + national phone number

## Ajax validation and formatting

Expose ´__phonenumber/validate´ and ´__phonenumber/format´ endpoints for validation and formatting of phone numbers

## Todo

None

## Compatibility

Tested with 4.6 but should work on any ^4 projects

## Maintainer

LeKoala - thomas@lekoala.be
