<?php

namespace BaruchScheduling\Models;

final class CustomHoursByDayOfWeek
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
}
