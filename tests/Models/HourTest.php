<?php

namespace BaruchScheduling\Models;

use PHPUnit\Framework\TestCase;

class HourTest extends TestCase
{
    public function testToStringMethodReturnsDateWithMeridiem()
    {
        $test_class = new Hour(3);
        \assertStringMatchesFormat(
            "%d am",
            (string) $test_class
        );

        \assertStringMatchesFormat(
            "%d pm",
            (string) (new Hour(15))
        );
    }

    public function testTo24HourMethodReturnsExpectedString()
    {
        $_24_hour_format = "%d%d:%d%d";
        \assertStringMatchesFormat(
            $_24_hour_format,
            (new Hour(3))->to24HourFormat()
        );

        \assertStringMatchesFormat(
            $_24_hour_format,
            (new Hour(15))->to24HourFormat()
        );
    }

    public function testTo12HourMethodReturnsExpectedString()
    {
        $_12_hour_format = "%d:%d%d";
        $am = "am";
        $pm = "pm";
        \assertStringMatchesFormat(
            "$_12_hour_format $am",
            (new Hour(3))->to12HourFormat()
        );
        \assertStringMatchesFormat(
            "$_12_hour_format $pm",
            (new Hour(15))->to12HourFormat()
        );
    }
}
