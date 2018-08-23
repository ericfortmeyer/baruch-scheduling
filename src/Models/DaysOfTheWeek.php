<?php

namespace BaruchScheduling\Models;

use \DateTimeImmutable;
use \DateInterval;

final class DaysOfTheWeek
{
    protected const DAY_OF_WEEK_AS_TEXT = "l";
    protected const DAY_OF_WEEK_AS_INT = "w";

    protected const DEFAULT_FORMAT = self::DAY_OF_WEEK_AS_TEXT;

    /**
     * @var array<DateTimeImmutable>
     */
    protected $days;

    public function __construct()
    {
        $this->days = $this->rangeOfDTObjects();
    }

    /**
     * return an array of string representations of each day of the week
     * @return array<string>
     */
    public function toArray(): array
    {
        return array_map(
            function (DateTimeImmutable $dt) {
                return $dt->format(self::DEFAULT_FORMAT);
            },
            $this->days
        );
    }

    /**
     * Use to map string representations of each day of the week to their numerical counterpart
     * @return array
     */
    public function map(): array
    {
        return array_map(
            function ($day_as_string, $day_as_int) {
                return [
                    $day_as_string => $day_as_int
                ];
            },
            $this->toArray(),
            $this->daysOfWeekAsIntegers()
        );
    }

    protected function rangeOfDTObjects(): array
    {
        return array_map(
            function (string $day_as_int) {
                return $this->firstDayOfWeek()->add($this->interval($day_as_int));
            },
            $this->daysOfWeekAsIntegers()
        );
    }

    /**
     * subtract the numerical representation of the current day of the week
     * to get the first day of the week
     * @return DateTimeImmutable
     */
    protected function firstDayOfWeek(): DateTimeImmutable
    {
        return (new DateTimeImmutable())
            ->sub(
                $this->interval(
                    $this->currentDayOfWeekAsInt()
                )
        );
    }

    /**
     * get the numerical representation of the current day of the week
     * @return string
     */
    protected function currentDayOfWeekAsInt(): string
    {
        return (new DateTimeImmutable())
            ->format(self::DAY_OF_WEEK_AS_INT);
    }

    protected function interval(string $days): DateInterval
    {
        return new DateInterval("P${days}D");
    }

    /**
     * get the numerical representation of each day of the week
     * @return array<int>
     */
    protected function daysOfWeekAsIntegers(): array
    {
        return range(0,6);
    }

}
