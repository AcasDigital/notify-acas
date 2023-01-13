<?php

namespace App\Tests;

use App\EmploymentDispute\Submission\PayloadNormaliser;
use PHPUnit\Framework\TestCase;

class PayloadNormaliserTest extends TestCase
{
    /**
     * @dataProvider provideFirstNameData
     */
    public function testNormaliseFirstName(?string $input, $expectedOutput): void
    {
        $payloadNormaliser = new PayloadNormaliser();

        $output = $payloadNormaliser->normaliseFirstName($input);
        $this->assertEquals($expectedOutput, $output, 'Data does not match expected output.');
    }

    /**
     * @dataProvider provideTelephoneData
     */
    public function testNormaliseTelephone(?string $input, $expectedOutput): void
    {
        $payloadNormaliser = new PayloadNormaliser();

        $output = $payloadNormaliser->normalisePhone($input);
        $this->assertEquals($expectedOutput, $output, 'Data does not match expected output.');
    }

    /**
     * @dataProvider providePostcodeData
     */
    public function testNormalisePostcode(?string $input, $expectedOutput): void
    {
        $payloadNormaliser = new PayloadNormaliser();

        $output = $payloadNormaliser->normalisePostCode($input);
        $this->assertEquals($expectedOutput, $output, 'Data does not match expected output.');
    }

    /**
     * @dataProvider provideOrgData
     */
    public function testNormaliseOrg(?string $input, $expectedOutput): void
    {
        $payloadNormaliser = new PayloadNormaliser();

        $output = $payloadNormaliser->normaliseOrganisation($input);
        $this->assertEquals($expectedOutput, $output, 'Data does not match expected output.');
    }

    public function provideFirstNameData()
    {
        return [
            [null, ''],
            ['', ''],
            ['MR EDWARD', 'edward'],
            ['mrs EDWARD', 'edward'],
            ['missus EDWARD', 'edward'],
            ['ms sally', 'sally'],
            ['sally', 'sally'],
            ['MRS MS Sally', 'sally'],
            ['O\'Neill', 'oneill'],
        ];
    }

    public function provideTelephoneData()
    {
        return [
            [null, ''],
            ['', ''],
            ['0123\'456', '0123456'],
            ['+44123123123', '44123123123'],
            ['(01322) 666666', '01322666666'],
            ['123456789', '123456789'],
            ['Ask for Bob +4411111111 ', '4411111111'],
        ];
    }

    public function providePostcodeData()
    {
        return [
            [null, ''],
            ['', ''],
            ['YO1\'8AZ', 'yo18az'],
            ['YO1 8AZ', 'yo18az'],
            ['YO1    8AZ', 'yo18az'],
            ['  YO1    8AZ   ', 'yo18az'],
            ['GU13DS', 'gu13ds'],
        ];
    }

    public function provideOrgData()
    {
        return [
            [null, ''],
            ['', ''],
            ['[test]\'punct(uati"on).', 'testpunctuation'],
            ['Axis12 Ltd.', 'axis12'],
            ['Steptoe and Son\'s', 'steptoe and sons'],
            ['Steptoe and Sons', 'steptoe and sons'],
            ['Steptoe & Sons', 'steptoe and sons'],
            ['Steptoe and co', 'steptoe'],
            ['One / Two   / Three', 'one two three'],
            ['Axis12 Limited', 'axis12'],
            ['Axis12 a.k.a Axistwelve', 'axis12 axistwelve'],
            [' .  Foobar t/a and co. ', 'foobar'],
        ];
    }
}
