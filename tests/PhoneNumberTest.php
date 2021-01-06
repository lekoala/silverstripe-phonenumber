<?php

namespace LeKoala\PhoneNumber\Test;

use LeKoala\PhoneNumber\DBPhone;
use SilverStripe\Dev\SapphireTest;
use LeKoala\PhoneNumber\PhoneHelper;

class PhoneNumberTest extends SapphireTest
{
    /**
     * Defines the fixture file to use for this test class
     * @var string
     */
    protected static $fixture_file = 'PhoneNumberTest.yml';

    protected static $extra_dataobjects = array(
        Test_PhoneNumberModel::class,
    );

    /**
     * @return Test_PhoneNumberModel
     */
    public function getDefaultObj()
    {
        return $this->objFromFixture(Test_PhoneNumberModel::class, 'default');
    }

    public function testPhoneField()
    {
        $model = $this->getDefaultObj();

        $field = new DBPhone('Phone');

        $nationalNumber = '0473 12 34 56';
        $internationalNumber = '+32 473 12 34 56';
        $internationalNumberNoSpace = str_replace(' ', '', $internationalNumber);

        // When provided with a national number, region must be provided through the model (field CountryCode is assumed)
        $field->setValue($nationalNumber, $model);
        $this->assertEquals($internationalNumber, $field->International());
        $this->assertEquals($internationalNumberNoSpace, $field->E164());
        $this->assertEquals($nationalNumber, $field->National());

        // When provided with international number only, region can be computed
        $field->setValue($internationalNumber);
        $this->assertEquals($internationalNumber, $field->International());
        $this->assertEquals($internationalNumberNoSpace, $field->E164());
        $this->assertEquals($nationalNumber, $field->National());
    }

    public function testHelper()
    {
        $list = PhoneHelper::getCountriesList();
        $this->assertContains('BE', array_keys($list));

        $list = PhoneHelper::listCountryPrefixes();
        $this->assertContains('BE', array_keys($list));

        $validNumbers = [
            '+32473123456' => 'BE',
            '0473123456' => 'BE',
        ];
        foreach ($validNumbers as $num => $region) {
            $this->assertTrue(PhoneHelper::validatePhoneNumber($num, $region), "Could not validate $num for $region");
            if (strpos($num, '+') === 0) {
                $this->assertTrue(PhoneHelper::validatePhoneNumber($num), "Could not validate $num without $region set");
            }
        }
        $invalidNumbers = [
            '+3247312345' => 'BE',
            '047312345' => 'BE',
        ];
        foreach ($invalidNumbers as $num => $region) {
            $this->assertFalse(PhoneHelper::validatePhoneNumber($num, $region), "Could not invalidate $num for $region");
        }
    }
}
