<?php

namespace BaruchScheduling\Models;

final class Week
{
    protected const DAY_OF_WEEK_AS_TEXT = "l";
    protected const DAY_OF_WEEK_AS_INT = "w";

    protected const DEFAULT_FORMAT = self::DAY_OF_WEEK_AS_TEXT;
    protected const DETERMINING_DAY_FOR_DISPLAYING_CURRENT_MONTH = "Wednesday";

    /**
     * Range of days of the week of a given Day object
     * @var array<Day>
     */
    public $days;

    /**
     * The earliest open hour of this week
     * @var int
     */
    public $minHour;

    /**
     * The latest closed hour of this week
     * @var int
     */
    public $maxHour;

    /**
     * Range of hours starting from the earliest available hour
     * and the latest available hour of all days of this week
     * @var array
     */
    public $rangeOfHours;

    /**
     * Month that should be displayed
     * @var string
     */
    public $month;

    public function __construct(Day $day)
    {
        $this->days = $this->rangeOfDays($day);

        $this->minHour = $this->earliestHourOfTheWeek($this->days);

        $this->maxHour = $this->latestHourOfTheWeek($this->days);

        $this->rangeOfHours = \BaruchScheduling\Functions\createRangeOfHours(
            $this->minHour,
            $this->maxHour,
            $this->days[0]->date
        );

        $this->month = $this->monthToDisplay($this->days);
    }

    /**
     * Determine the earliest open hour of all days of this week.
     * Find the min of all of the open hours of the days of this week.
     * @param array $days
     * @return int
     */
    protected function earliestHourOfTheWeek(array $days)
    {
        return min(
            array_map(
                function (Day $day): int {
                    return $day->hours[0]->hourOnlyFormat();
                },
                $days
            )
        );
    }

    /**
     * Determine the latest closed hour of all days of this week.
     * Find the max of all of the closed hours of the days of this week.
     * @param array $days
     * @return int
     */
    protected function latestHourOfTheWeek(array $days): int
    {
        return max(
            array_map(
                function (Day $day): int {
                    return end($day->hours)->hourOnlyFormat();
                },
                $days
            )
        );
    }

    

    /**
     * Create a week that has as one of it's "days" the given day
     * @param Day $day
     * @return self
     */
    public function having(Day $day): self
    {
        return new $this($day);
    }

    /**
     * Return an array of Day objects representing all days in the week
     * of the Day object given as an argument in the constructor
     * @return array<Day>
     */
    public function days(): array
    {
        return $this->days;
    }

    /**
     * Return an array of Day objects representing all days in the week
     * of the given Day object
     * @param Day $day
     * @return array<Day>
     */
    protected function rangeOfDays(Day $day): array
    {
        return array_map(
            function (int $day_as_int) use ($day) {
                return $this->firstDayOfWeek($day)
                    ->add(
                        $this->intervalSpec($day_as_int)
                    );
            },
            $this->daysOfWeekAsIntegers()
        );
    }

    /**
     * Find the first day of the week of the given Day object
     * Subtract the numerical representation of the given day of the week
     * to get the first day of the week
     * @param Day $day
     * @return Day
     */
    protected function firstDayOfWeek(Day $day): Day
    {
        return $day->sub(
            $this->intervalSpec(
                $this->dayOfWeekAsInt($day)
            )
        );
    }

    /**
     * String passed into the constructor of DateInterval
     * @param int $days
     * @return string
     */
    protected function intervalSpec(int $days): string
    {
        return "P${days}D";
    }

    /**
     * get the numerical representation of each day of the week
     * @return array<int>
     */
    protected function daysOfWeekAsIntegers(): array
    {
        return range(0, 6);
    }

    /**
     * get the numerical representation of the day of the week given day
     * @param Day $day
     * @return int
     */
    protected function dayOfWeekAsInt(Day $day): int
    {
        /**
         * Use this method to return a public property to get the value
         * Use this method to avoid making mistakes and introducing bugs
         * Avoid using a method in order to keep the api small
         */
        return (int) $day->day_of_the_week_as_int;
    }

    /**
     * Display the following month if this week contains the first day of the next month
     * and it is on or before the determining day of the week
     * @param $days
     * @return string
     */
    protected function monthToDisplay(array $days): string
    {
        return $this->shouldDisplayNextMonth($days)
            ? $this->nextMonth($days)
            : $this->currentMonth($days);
    }

    /**
     * @param $days
     * @return string
     */
    protected function nextMonth(array $days): string
    {
        /**
         * UNEXPECTED BEHAVIOR:
         * If you add one month to the 31st day of a month that is followed by a month
         * with 30 days, it will return the month after the following month.
         * Example: Adding one month to a date time object representing 2019-03-31
         * will return May
         *
         * A quick fix is to subtract a couple of days from the first day of the week
         * before adding one month
         */
        return $days[0]
            ->sub(Day::ONE_DAY)
            ->sub(Day::ONE_DAY)
            ->add(Day::ONE_MONTH)
            ->month;
    }

    /**
     * @param $days
     * @return string
     */
    protected function currentMonth(array $days): string
    {
        return $days[0]->month;
    }

    protected function shouldDisplayNextMonth(array $days): bool
    {
        return in_array(
            true,
            array_map(
                function (Day $day) {
                    return $day->isFirstDayOfMonth
                        && $this->isOnOrBeforeDeterminingDay($day);
                },
                $days
            )
        );
    }

    protected function isOnOrBeforeDeterminingDay(Day $day): bool
    {
        return $this->dayOfWeekAsInt($day)
            <= (new \DateTimeImmutable())
                ->createFromFormat(
                    Day::DAY_OF_WEEK_AS_TEXT,
                    self::DETERMINING_DAY_FOR_DISPLAYING_CURRENT_MONTH
                )
                ->format(Day::DAY_OF_WEEK_AS_INT);
    }
}
