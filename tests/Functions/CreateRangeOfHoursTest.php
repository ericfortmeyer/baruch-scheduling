<?php

namespace BaruchScheduling\Functions;

use \PHPUnit\Framework\TestCase;
use \BaruchScheduling\Models\Event;
use \BaruchScheduling\Models\Hour;

class CreateRangeOfHoursTest extends TestCase
{
    protected function setup()
    {
        $id = $this->getHash();
        $creator_id = $owner_id = $this->getHash();
        $duration = 90;
        $date_time = date("c");
        $type = "appointment";
        
        $this->test_params = [
            $id,
            $creator_id,
            $owner_id,
            $date_time,
            $duration,
            $type
        ];
    }

    protected function getHash(): string
    {
        return hash(
            "sha256",
            bin2Hex(random_bytes(64))
        );
    }

    public function testCreatesRangeOfInstancesOfHour()
    {
        \assertContainsOnlyInstancesOf(
            Hour::class,
            createRangeOfHours(7, 19, date("Y-m-d"))
        );

        \assertContainsOnlyInstancesOf(
            Hour::class,
            createRangeOfHours(7, 19, date("Y-m-d"), [new Event(...$this->test_params)])
        );
    }

    public function testHourInRangeOfHoursIsBooked()
    {
        $params = $this->test_params;
        $date = "2018-08-25";
        $start = 8;
        $params[3] = "${date}T0${start}:00:00-05:00";
        $result = current(
            array_filter(
                createRangeOfHours($start, 19, $date, [new Event(...$params)]),
                function ($hour) {
                        return $hour->isBooked;
                }
            )
        );
        assertInstanceOf(
            Hour::class,
            $result
        );

        $params_2 = $this->test_params;

        $test_hour = "20";

        $params_2[3] = "${date}T${test_hour}:00:00-05:00";

        $result_2 = current(
            array_filter(
                createRangeOfHours(
                    $start,
                    21,
                    $date,
                    [new Event(...$params_2)]
                ),
                function ($hour) {
                    return $hour->isBooked;
                }
            )
        );

        assertTrue(
            !empty($result_2) && $result_2->isBooked && $result_2->hourOnlyFormat() == $test_hour
        );
    }
}
