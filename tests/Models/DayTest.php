<?php

namespace BaruchScheduling\Models;

use PHPUnit\Framework\TestCase;

class DayTest extends TestCase
{
    protected function setup()
    {
        $this->params = [
            "1981-06-11",
            new Hour(8),
            new Hour(10),
            [
                "Saturday",
                "Sunday"
            ]
        ];
        $this->test_class = new Day(...$this->params);
    }

    public function testExists()
    {
        assertInstanceOf(
            Day::class,
            $this->test_class
        );
    }

    public function testHasExpectedDate()
    {
        assertEquals(
            "1981-06-11",
            $this->test_class->date
        );
    }

    public function testWillDisplayExpectedDayOfMonth()
    {
        assertEquals(
            11,
            $this->test_class->day_of_the_month
        );
    }

    public function testWillDisplayExpectedDayOfTheWeek()
    {
        assertEquals(
            "Thursday",    
            $this->test_class->day_of_the_week
        );
    }

    public function testOffDayFlagWorks()
    {
        $try_params = $this->params;
        $try_params[0] = "2018-08-20";
        assertFalse(
            (new Day(...$try_params))->isOffDay
        );

        $test_params = $this->params;
        $test_params[0] = "2018-08-25";

        assertTrue(
            (new Day(...$test_params))->isOffDay
        );
    }

    public function testIsPastFlagWorks()
    {
        assertTrue(
            $this->test_class->isPast
        );
    }

    public function testHasExpectedOpenHour()
    {
        assertTrue(
            $this->test_class->hours[0]->hourOnlyFormat() === "8"
        );
    }

    public function testHasExpectedLastHourAvailable()
    {
        assertTrue(
            end($this->test_class->hours)->hourOnlyFormat() === "9"
        );
    }

    public function testHasExpectedRangeOfHours()
    {
        $day = new Day(
            "2018-01-01",
            new Hour(8),
            new Hour(18)
        );
        $result = array_map(
            function ($hour) {
                return $hour->hourOnlyFormat();
            },
            $day->hours
        );

        assertContains(
            9,
            $result
        );

        assertFalse(
            in_array(7, $result)
        );

        assertFalse(
            in_array(19, $result)
        );

        assertEquals(
            range(8,17),
            $result
        );
    }

    public function testAddDayWorks()
    {
        assertEquals(
            "Friday",
            $this->test_class->add(Day::ONE_DAY)->day_of_the_week
        );

        assertEquals(
            13,
            $this->test_class->add(Day::ONE_DAY)->add(Day::ONE_DAY)->day_of_the_month
        );
    }

    public function testSubDayWorks()
    {
        assertEquals(
            "Wednesday",
            $this->test_class->sub(Day::ONE_DAY)->day_of_the_week
        );

        assertEquals(
            8,
            $this->test_class->sub(Day::ONE_DAY)->sub(Day::ONE_DAY)->sub(Day::ONE_DAY)->day_of_the_month
        );
    }

    public function testCustomHoursByDayOfWeekTheWorks()
    {
        $day = new Day(
            "2018-08-25",
            new Hour(7),
            new Hour(12)
        );

        $test_1 = $day->withCustomHoursByDayOfWeek(
            [new CustomHoursByDayOfWeek(
                "Saturday",
                new Hour(9)
            )]
        );

        assertEquals(
            "09:00",
            $test_1->hours[0]->to24HourFormat()
        );

        assertEquals(
            "11:00",
            end($test_1->hours)->to24HourFormat()
        );

        $test_2 = $day->withCustomHoursByDayOfWeek(
            [new CustomHoursByDayOfWeek(
                "Saturday",
                new Hour(9),
                new Hour(11)
            )]
        );

        assertEquals(
            "10:00",
            end($test_2->hours)->to24HourFormat()
        );

        $test_3 = $day->withCustomHoursByDayOfWeek(
            [new CustomHoursByDayOfWeek(
                "Saturday",
                null,
                new Hour(16)
            )]
        );

        assertEquals(
            "07:00",
            $test_3->hours[0]->to24HourFormat()
        );

        assertEquals(
            "15:00",
            end($test_3->hours)->to24HourFormat()
        );
    }

    public function testCustomHoursByDateWorks()
    {
        $day = new Day(
            "2018-08-25",
            new Hour(9),
            new Hour(18)
        );

        $id = hash(
            "sha256",
            bin2Hex(random_bytes(64))
        );
        $creator_id = $owner_id = hash(
            "sha256",
            bin2Hex(random_bytes(64))
        );
        $duration = 90;
        $date_time = "2018-08-25T17:00:00-05:00";
        $type = "appointment";
        
        $event_params = [
            $id,
            $creator_id,
            $owner_id,
            $date_time,
            $duration,
            $type
        ];

        assertEquals(
            "09:00",
            $day->hours[0]->to24HourFormat()
        );

        $test_1 = $day->withCustomHoursByDate(
            [new CustomHoursByDate(
                "2018-08-25",
                new Hour(12)
            )]
        );
        assertEquals(
            "12:00",
            $test_1->hours[0]->to24HourFormat()
        );
        $test_2 = $day
            ->withEvents([new Event(...$event_params)])
            ->withCustomHoursByDate(
            [
                new CustomHoursByDate(
                    "2018-08-25",
                    new Hour(15),
                    new Hour(20)
                ),
                // new CustomHoursByDate(
                //     "2019-01-01",
                //     new Hour(2),
                //     new Hour(5)
                // )
            ]
        );
        assertEquals(
            "15:00",
            $test_2->hours[0]->to24HourFormat()
        );

        assertEquals(
            "19:00",
            end($test_2->hours)->to24HourFormat()
        );

        $result = current(
            array_filter(
                $test_2->hours,
                function ($hour) {
                    return $hour->isBooked;
                }
            )
        );

        assertInstanceOf(
            Hour::class,
            $result
        );

    }
}
