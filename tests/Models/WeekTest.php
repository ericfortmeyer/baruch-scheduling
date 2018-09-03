<?php

namespace BaruchScheduling\Models;

use PHPUnit\Framework\TestCase;

class WeekTest extends TestCase
{
    public function testExists()
    {
        $params = [
            date("Y-m-d"),
            new Hour("T07:00:00-05"),
            new Hour("T10:00-05")
        ];
        assertInstanceOf(
            Week::class,
            new Week(new Day(...$params))
        );
    }

    public function testFirstDayIsFirstDayOfWeekOfGivenDay()
    {
        $params = [
            "2018-08-21",
            new Hour(7),
            new Hour(10)
        ];
        foreach ($this->generateDatesForYear("2018") as $date) {
            $params[0] = $date;
            assertEquals(
                "Sunday",
                $this->getFirstDay($params)
            );
        }
        foreach ($this->generateDatesForYear("2001") as $date) {
            $params[0] = $date;
            assertEquals(
                "Sunday",
                $this->getFirstDay($params)
            );
        }
    }

    public function testDisplaysExpectedMonth()
    {
        assertEquals(
            "October",
            $this->createWeekWithDate("2018-09-30")->month
        );

        assertEquals(
            "January",
            $this->createWeekWithDate("2019-12-31")->month
        );

        assertEquals(
            "January",
            $this->createWeekWithDate("2019-02-01")->month
        );

        assertEquals(
            "April",
            $this->createWeekWithDate("2019-03-31")->month
        );
    }

    protected function createWeekWithDate(string $date): Week
    {
        return new Week(
            new Day(
                $date,
                new Hour(9),
                new Hour(15)
            )
        );
    }

    public function testDaysOfWeekAreAsExpectedWhenParamsAreDeserializedJson()
    {
        $daysoff = $this->loadAndDecodeJson("daysoff");
        $customSchedule = $this->loadAndDecodeJson("customSchedule");
        $regularSchedule = $this->loadAndDecodeJson("regularSchedule");

        $custom_hours_by_day_of_week = $this->loadCustomHours($customSchedule);

        $params = [
            "2018-08-25",
            new Hour($regularSchedule->open),
            new Hour($regularSchedule->closed),
            $regularSchedule->off_days,
            //events
            [],
            $custom_hours_by_day_of_week,
            //custom hours by date,
            [],
            $daysoff
        ];

        $week = new Week(new Day(...$params));

        assertInstanceOf(
            Week::class,
            $week
        );

        assertContainsOnlyInstancesOf(
            Day::class,
            $week->days()
        );

        assertEquals(
            "2018-08-19",
            $week->days()[0]->date
        );

        assertEquals(
            "2018-08-20",
            $week->days()[1]->date
        );

        assertTrue(
            $week->days()[0]->isPast
        );

        assertTrue(
            $week->days()[6]->isOffDay
        );

        array_map(
            function ($day) {
                assertTrue(
                    $day->month === "August"
                );
            },
            $week->days()
        );

        assertTrue(
            $week->days()[1]->day_of_the_week === "Monday"
        );

        assertEquals(
            "08:00",
            $week->days()[1]->hours[0]->to24HourFormat()
        );

        $tuesday_hours = $week->days()[2]->hours;
        assertEquals(
            "16:00",
            end($tuesday_hours)->to24HourFormat()
        );

        assertEquals(
            "07:00",
            $week->days()[2]->hours[0]->to24HourFormat()
        );

        $wednesday_hours = $week->days()[3]->hours;
        assertEquals(
            "07:00",
            $wednesday_hours[0]->to24HourFormat()
        );

        assertEquals(
            "21:00",
            end($wednesday_hours)->to24HourFormat()
        );

        $friday_hours = $week->days()[5]->hours;
        assertEquals(
            "19:00",
            end($friday_hours)->to24HourFormat()
        );
    }

    protected function generateDatesForYear(string $year = "", string $month = "")
    {
        $year = $year ?? date("Y");
        $months = $month
            ? [$month]
            : array_map(
                function ($month): string {
                    return $month;
                },
                range(1, 12)
            );

        foreach ($months as $month) {
            $days = (function () use ($year, $month): array {
                return array_map(
                    function (int $day) use ($year, $month): string {
                        try {
                            return (new \DateTimeImmutable("$year-$month-$day"))->format("d");
                        } catch (\Exception $e) {
                            return "";
                        }
                    },
                    range(1, 31)
                );
            })();
            foreach ($days as $day) {
                yield "$year-$month-$day";
            }
        }
    }

    protected function loadAndDecodeJson(string $file_name)
    {
        $path_to_json = realpath(__DIR__ . "/../../mock-scheduling/json");
        return json_decode(
            file_get_contents(
                "$path_to_json/$file_name.json"
            )
        );
    }

    protected function loadCustomHours(array $custom_schedule): array
    {
        return array_map(
            function (object $obj) {
                return new CustomHoursByDayOfWeek(
                    $obj->day,
                    $obj->open ? new Hour($obj->open) : null,
                    $obj->closed ? new Hour($obj->closed) : null
                );
            },
            $custom_schedule
        );
    }

    public function testContainsOnlyInstancesOfDays()
    {
        $params = [
            "2018-08-21",
            new Hour(7),
            new Hour(10)
        ];

        $week = new Week(new Day(...$params));
        assertContainsOnlyInstancesOf(
            Day::class,
            $week->days()
        );
    }

    protected function getFirstDay(array $params): string
    {
        return (new Week(new Day(...$params)))->days()[0]->day_of_the_week;
    }
}
