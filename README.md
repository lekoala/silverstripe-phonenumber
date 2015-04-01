Silverstripe Phonenumber module
==================
Provide some helper services to deal with phone numbers in Silverstripe

This module integrates libphonenumber as the utility to parse and validate
phonenumbers. The fork used is : https://github.com/giggsey/libphonenumber-for-php

Most of the time, it's a good idea to set the country of the phone number.
Otherwise, current locale will be used. If the country is dynamic, you can
set a country field thanks to the setCountryField method.

The value is dynamically formatted through ajax to avoid loading huge js libraries.
You can also easily validate values (for example, with a ZenValidator remote
constraint which targets /libphonenumber/validate url) if needed.

Sample code:

	$phone = new LibPhoneNumberField('phone', 'Phone number');
	$phone->setCountryCode('BE');
	
	$validator = ZenValidator::create();
	$validator->setConstraint('phone', Constraint_remote::create('/libphonenumber/validate',null,array('data' => array('country' => 'BE'))));

This module also provide an extension to apply to dataobject, for example to members

	Member:
      extensions:
        - LibPhoneNumberExtension

This will add to fields, one "CountryCode" and one "PhoneNumber" to the data object.

Twilio lookup
------------------

You can also make lookups with Twilio provided that you have defined the following constants:

	define('TWILIO_ACCOUNT_SID','');
	define('TWILIO_AUTH_TOKEN','');

Twilio lookups are accessible through the controller (/libphonenumber/lookup) or
through a static method PhoneNumberExtension::twilioLookup()

Compatibility
==================
Tested with Silverstripe 3.1

Maintainer
==================
LeKoala - thomas@lekoala.be