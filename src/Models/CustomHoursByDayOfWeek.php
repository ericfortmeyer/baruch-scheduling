<?php

namespace BaruchScheduling\Models;

final class CustomHoursByDayOfWeek implements CompareByDayOfWeekInterface
{
    /**
     * Day of the week (ex. "Sunday")
     * @var string
     */
    public $day_of_week;

    /**
     * Open hour
     * @var Hour|null
     */
    public $open;

    /**
     * Closed hour
     * @var Hour|null
     */
    public $closed;

    public function __construct(string $day_of_week, ?Hour $open = null, ?Hour $closed = null)
    {
        if ($closed && $open >= $closed) {
            throw new \RuntimeException("It looks like the first hour, $open, is not less than the last hour, $closed");
        }

        $this->day_of_week = $day_of_week;
        $this->open = $open;
        $this->closed = $closed;
    }

    /**
     * Is the custom schedule on the given day of the week?
     * 
     * @param string $day_of_week
     * @return bool
     */
    public function compareByDayOfWeek(string $day_of_week): bool
    {
        return $this->day_of_week === $day_of_week;
    }
}
