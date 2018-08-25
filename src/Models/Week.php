<?php

namespace BaruchScheduling\Models;

final class Week
{
    protected const DAY_OF_WEEK_AS_TEXT = "l";
    protected const DAY_OF_WEEK_AS_INT = "w";

    protected const DEFAULT_FORMAT = self::DAY_OF_WEEK_AS_TEXT;
    protected const CUTOFF_DAY_FOR_DISPLAYING_CURRENT_MONTH = "Wednesday";

    /**
     * Range of days of the week of a given Day object
     * @var array<Day>
     */
    public $days;

    /**
     * Month that should be displayed
     * @var string
     */
    public $month;

    public function __construct(Day $day)
    {
        $this->days = $this->rangeOfDays($day);
        $this->month = $this->monthToDisplay($this->days);
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
        return range(0,6);
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

    protected function monthToDisplay(array $days): string
    {
        /**
         * Display the following month if the last day of the month is on or before
         * this day of the week
         */
        return $this->shouldDisplayNextMonth($days)
            ? $this->nextMonth()
            : $this->currentMonth();
    }

    protected function nextMonth(): string
    {
        return $this->days[0]->add(Day::ONE_MONTH)->month;
    }

    protected function currentMonth(): string
    {
        return $this->days[0]->month;
    }

    protected function shouldDisplayNextMonth(array $days): bool
    {
        return in_array(
            true,
            array_map(
                function (Day $day) {
                    /**
                     * If both are true, this particular week has the last day of the month
                     */
                    return $day->isLastDayOfMonth
                        && $this->isOnOrAfterCutoffDay($day);
                },
                $days
            )
        );
    }

    protected function isOnOrAfterCutoffDay(Day $day): bool
    {
        return $this->dayOfWeekAsInt($day)
            >= (new \DateTimeImmutable())
                ->createFromFormat(
                    Day::DAY_OF_WEEK_AS_TEXT,
                    self::CUTOFF_DAY_FOR_DISPLAYING_CURRENT_MONTH
                )
                ->format(Day::DAY_OF_WEEK_AS_INT) == "1";
    }
}
