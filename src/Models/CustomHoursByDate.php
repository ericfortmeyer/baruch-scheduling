<?php

namespace BaruchScheduling\Models;

final class CustomHoursByDate implements CompareByDateInterface
{
    /**
     * Date.  ISO 8601
     * @var string
     */
    public $date;

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

    public function __construct(string $date, ?Hour $open = null, ?Hour $closed = null)
    {
        if ($closed && $open >= $closed) {
            throw new \RuntimeException("It looks like the first hour, $open, is not less than the last hour, $closed");
        }

        $this->date = $date;
        $this->open = $open;
        $this->closed = $closed;
    }

    /**
     * Is this custom schedule on the given date?
     * 
     * @param string $date
     * @return bool
     */
    public function compareByDate(string $date): bool
    {
        return $this->date === $date;
    }
}
