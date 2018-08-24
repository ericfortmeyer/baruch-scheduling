<?php

namespace BaruchScheduling\Models;

use \DateTimeImmutable;
use \DateInterval;

final class Day
{
    protected const FORMAT_MONTH = "F";
    protected const FORMAT_DATE = "Y-m-d";
    public const FORMAT_DAY_OF_MONTH = "j";
    public const DAY_OF_WEEK_AS_TEXT = "l";
    public const DAY_OF_WEEK_AS_INT = "w";

    public const ONE_MONTH = "P1M";
    public const ONE_WEEK = "P1W";
    public const ONE_DAY = "P1D";

    /**
     * Is this a custom off day
     * @var bool
     */
    public $isOffDay = false;

    /**
     * Is this day in the past
     * @var bool
     */
    public $isPast = false;

    /**
     * Is 
     */

    /**
     * Is this the last day of the month
     * @var bool
     */
    public $isLastDayOfMonth = false;


    /**
     * ISO_8601 date in the format of yyyy-mm-dd
     * @var string
     */
    public $date = "";

    /**
     * String representation of this day of the week
     * example: "Sunday"
     * @var string
     */
    public $day_of_the_week = "";

    /**
     * Numerical representation of this day of the week
     * example: 0 (for Sunday)
     * @var string
     */
    public $day_of_the_week_as_int = "";

    /**
     * Numerical representation of the day of the month without leading zeros
     * example: 31
     * @var string
     */
    public $day_of_the_month = "";

    /**
     * String representation of the month to display for this particular day
     * The month that should be displayed is determined by the monthToDisplay method of this object
     * @var string
     */
    public $month = "";

    /**
     * A DateTimeImmutable object representing this day
     * @var DateTimeImmutable
     */
    protected $dt;

    /**
     * An array of Hour objects representing all hours open in an ordinary day
     * @var array<Hour>
     */
    public $hours = [];

    /**
     * An array of strings representing the regular off days of every week
     * @var array<string>
     */
    public $regular_off_days = [];

    /**
     * An array of objects representing days with a customized schedule
     * @var array<CustomHoursByDayOfWeek>
     */
    public $custom_hours_by_day = [];

    /**
     * An array of objects representing dates with a customized schedule
     * @var array<CustomHoursByDate>
     */
    public $custom_hours_by_date;

    /**
     * An array of date strings representing custom off days
     * @var array<string>
     */
    public $custom_off_days = [];

    /**
     * An array of events
     * @var array<Event>
     */
    public $events = [];

    /**
     * Construct a day
     * 
     * 1. Use an IS0 8601 date to determine which day it is.
     * 2. The 2nd and 3rd parameters are used as the start and end of the range of hours available
     * 3. Provide a list of off days which are days of the week to determine if this is an off day
     * 4. Provide events
     * 5. If this day of the week is contained in the provided custom hours by day of week,
     *    then use the open and closed hours provided as the start and end of the range of hours available
     * 6. If this date is contained in the provided custom hours by date,
     *    then use the open and closed hours provided as the start and end of the range of hours available
     * 7. Provide a list of off days which are dates to determine if this is an off day
     * 
     * @param string $date  ISO 8601 date spec. REQUIRED
     * @param Hour $open.  REQUIRED
     * @param Hour $closed. REQUIRED
     * @param array<string> $regular_off_days (ex. ["Sunday", "Saturday"]). Default empty array
     * @param array<Event> $events.  Default empty array
     * @param array<CustomHoursByDayOfWeek> $custom_hours_by_day.  Default empty array
     * @param array<CustomHoursByDate> $custom_hours_by_date. Default empty array
     * @param array<string> $custom_off_days.  Array with ISO 8601 date strings. Default empty array
     */
    public function __construct(
        string $date,
        Hour $open,
        Hour $closed,
        $regular_off_days = [],
        $events = [],
        $custom_hours_by_day = [],
        $custom_hours_by_date = [],
        $custom_off_days = []
    ){
        $this->date = $date;
        
        if ($open >= $closed) {
            throw new \RuntimeException("It looks like the first hour, $open, is not less than the last hour, $closed");
        }
        $this->dt = new DateTimeImmutable($date);
        $this->day_of_the_week = $this->dayOfWeekAsTextFormat();
        $this->day_of_the_week_as_int = $this->dayOfWeekAsIntFormat();
        $this->day_of_the_month = $this->dayOfMonthFormat();
        $this->month = $this->monthFormat();

        $this->isPast = $this->dt < $this->today();
        $this->isOffDay = $this->isOffDay($regular_off_days, $custom_off_days);
        $this->isLastDayOfMonth = $this->isLastDayOfMonth();
    
        /**
         * Set these for the add and sub methods
         */
        $this->regular_off_days = $regular_off_days;
        $this->events = $events;
        $this->custom_hours_by_day = $custom_hours_by_day;
        $this->custom_hours_by_date = $custom_hours_by_date;
        $this->custom_off_days = $custom_off_days;

        /**
         * Add events as a parameter so that this won't have to be last
         */
        $this->createHours(
            $open,
            $closed,
            $custom_hours_by_date,
            $custom_hours_by_day,
            $events,
            $date
        );        
    }

    public function add(string $interval_spec, array $target_days_events = []): self
    {
        // must use the constuctor or there will be bugs
        return new $this(
            $this->dateAfterAdding($interval_spec),
            $this->open($this->hours),
            $this->closed($this->hours),
            $this->regular_off_days,
            $target_days_events ?? $this->events,
            $this->custom_hours_by_day,
            $this->custom_hours_by_date,
            $this->custom_off_days
        );
    }

    public function sub(string $interval_spec, array $target_days_events = []): self
    {
        // must use the constructor or there will be bugs
        return new $this(
            $this->dateAfterSubtracting($interval_spec),
            $this->open($this->hours),
            $this->closed($this->hours),
            $this->regular_off_days,
            $target_days_events ?? $this->events,
            $this->custom_hours_by_day,
            $this->custom_hours_by_date,
            $this->custom_off_days
        );
    }

    public function withEvents(array $events): self
    {
        return new $this(
            $this->date,
            $this->open($this->hours),
            $this->closed($this->hours),
            $this->regular_off_days,
            $events,
            $this->custom_hours_by_day,
            $this->custom_hours_by_date,
            $this->custom_off_days
        );
    }

    public function withCustomHoursByDayOfWeek(array $custom_hours): self
    {
        return new $this(
            $this->date,
            $this->open($this->hours),
            $this->closed($this->hours),
            $this->regular_off_days,
            $this->events,
            $custom_hours,
            $this->custom_hours_by_date,
            $this->custom_off_days
        );
    }

    public function withCustomHoursByDate(array $custom_hours_by_date): self
    {
        return new $this(
            $this->date,
            $this->open($this->hours),
            $this->closed($this->hours),
            $this->regular_off_days,
            $this->events,
            $this->custom_hours_by_day,
            $custom_hours_by_date,
            $this->custom_off_days
        );
    }

    public function withCustomOffDays(array $custom_off_days): self
    {
        return new $this(
            $this->date,
            $this->open($this->hours),
            $this->closed($this->hours),
            $this->regular_off_days,
            $this->events,
            $this->custom_hours_by_day,
            $this->custom_hours_by_date,
            $custom_off_days
        );
    }

    protected function createHours(
        Hour $open,
        Hour $closed,
        array $custom_hours_by_date,
        array $custom_hours_by_day,
        array $events,
        string $date
    ) {

        $this->hours = $this->createRangeOfHoursFromCustomHoursByDate(
            $custom_hours_by_date,
            $open,
            $closed,
            $events,
            $date
        ) ?? $this->createRangeOfHoursFromCustomHoursByDayOfWeek(
            $custom_hours_by_day,
            $open,
            $closed,
            $events,
            $date
        ) ?? $this->createRangeOfHours(
            $open,
            $closed,
            $events,
            $date
        );
    }

    protected function createRangeOfHoursFromCustomHoursByDayOfWeek(
        array $custom_hours,
        Hour $open,
        Hour $closed,
        array $events,
        string $date
    ) {
        foreach ($custom_hours as $obj) {
            if ($obj->day_of_week === $this->day_of_the_week) {
                return $this->createRangeOfHours(
                    $obj->open ?? $open,
                    $obj->closed ?? $closed,
                    $events,
                    $date
                );
            }
        }
    }

    protected function createRangeOfHoursFromCustomHoursByDate(
        array $custom_hours_by_date,
        Hour $open,
        Hour $closed,
        array $events,
        string $date
    ) {
        foreach ($custom_hours_by_date as $obj) {
            if ($obj->date === $this->date) {
                return $this->createRangeOfHours(
                    $obj->open ?? $open,
                    $obj->closed ?? $closed,
                    $events,
                    $date
                );
            }
        }
    }

    protected function createRangeOfHours(Hour $open, Hour $closed, array $events, string $date): array
    {
        return \BaruchScheduling\Functions\createRangeOfHours(
            $open->hourOnlyFormat(),
            $this->lastHourAvailable((int) $closed->hourOnlyFormat()),
            $date,
            $events
        );
    }

    /**
     * Use an array of days of the week and an array of dates
     * to determine if this is an off day
     * 
     * @param array<string> $regular_off_days
     * @param array<string> $custom_off_days
     * @return bool
     */
    protected function isOffDay(array $regular_off_days, array $custom_off_days): bool
    {
        return in_array($this->day_of_the_week, $regular_off_days)
            || in_array($this->date, $custom_off_days);
    }

    protected function dateAfterAdding(string $interval_spec): string
    {
        return $this->dt->add(new DateInterval($interval_spec))->format(self::FORMAT_DATE);
    }

    protected function dateAfterSubtracting(string $interval_spec): string
    {
        return $this->dt->sub(new DateInterval($interval_spec))->format(self::FORMAT_DATE);
    }

    /**
     * Use to determine the last hour available in the range of hours.
     * Creates an Hour object which represents an hour before closing.
     * Use so that the constructor of the Day class can follow normal semantics.
     * For example you would normally say "Our business is open at 7 am and closed at 5 pm"
     * instead of "Our business is available during the hours of 7 am to 4 pm"
     * 
     * @param int $hour_closed
     * @return string
     */
    protected function lastHourAvailable(int $hour_closed): string
    {
        return (new Hour((string) ($hour_closed - 1)))->hourOnlyFormat();
    }

    /**
     * First hour available for making appointments.
     * Based on when the schedule is open for business on the particular day
     * Uses the hours property which is an array of Hour objects
     * 
     * @param array $hours
     * @return Hour
     */
    protected function open(array $hours): Hour
    {
        return $hours[0];
    }

    /**
     * When the schedule is no longer available for making appointments.
     * Based on when the schedule is closed for business on the particular day.
     * Uses the hours property which is an array of Hour Objects
     * 
     * @param array $hours
     * @return Hour
     */
    protected function closed(array $hours): Hour
    {
        /**
         * Must add one to the last hour of the range of regular hours
         * in order to find out when the schedule is closed for making appointments.
         * This is because the last hour of the range of regular hours is
         * the last hour available for making appointments
         */
        return new Hour((string) (end($hours)->hourOnlyFormat() + 1));
    }

    protected function dateFormat(): string
    {
        return $this->dt->format(static::FORMAT_DATE);
    }

    protected function today(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    protected function isLastDayOfMonth(): bool
    {
        return $this->dt->add(new DateInterval(static::ONE_DAY))->format("j") == "1";
    }

    protected function dayOfWeekAsTextFormat(): string
    {
        return $this->dt->format(static::DAY_OF_WEEK_AS_TEXT);
    }

    protected function dayOfWeekAsIntFormat(): string
    {
        return $this->dt->format(static::DAY_OF_WEEK_AS_INT);
    }

    protected function dayOfMonthFormat(): string
    {
        return $this->dt->format(static::FORMAT_DAY_OF_MONTH);
    }

    protected function monthFormat(): string
    {
        return $this->dt->format(self::FORMAT_MONTH);
    }
}
