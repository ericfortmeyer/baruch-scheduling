<?php

namespace BaruchScheduling\Models;

use PHPUnit\Framework\TestCase;

class WeekTest extends TestCase
{
    public function testExists()
    {
        $params = [
            date("Y-m-d"),
            new Hour(7),
            new Hour(10)
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
        foreach($this->generateDatesForYear("2018") as $date) {
            $params[0] = $date;
            assertEquals(
                "Sunday",
                $this->getFirstDay($params)
            );
        }
        foreach($this->generateDatesForYear("2001") as $date) {
            $params[0] = $date;
            assertEquals(
                "Sunday",
                $this->getFirstDay($params)
            );
        }
    }

    protected function generateDatesForYear(string $year = "", string $month = "")
    {
        $year = $year ?? date("Y");
        $months = $month
            ? [$month]
            : array_map(
                function ($month): string {
                    // return $month < 10 ? "0$month" : $month;
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
