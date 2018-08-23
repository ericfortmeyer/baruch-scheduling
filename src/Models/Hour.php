<?php

namespace BaruchScheduling\Models;

final class Hour
{
    protected const FORMAT_WITH_MERIDIEM_LOWER = "g a";
    protected const FORMAT_WITH_MERIDIEM_UPPER = "g A";
    protected const FORMAT_24_HOUR = "H:i";
    protected const FORMAT_24_HOUR_INT_ONLY = "G";
    protected const FORMAT_12_HOUR = "g:i a";

    protected const DEFAULT_FORMAT = self::FORMAT_WITH_MERIDIEM_LOWER;

    /**
     * @var string
     */
    protected $hour = "";

    /**
     * @var bool
     */
    public $isBooked;
    

    public function __construct(string $hour, bool $isBooked = false)
    {
        $this->hour = $hour;
        $this->isBooked = $isBooked;
    }

    public function __toString()
    {
        return $this->withFormat(self::DEFAULT_FORMAT);
    }

    public function hourOnlyFormat(): string
    {
        return $this->withFormat(self::FORMAT_24_HOUR_INT_ONLY);
    }

    public function to24HourFormat(): string
    {
        return $this->withFormat(self::FORMAT_24_HOUR);
    }

    public function to12HourFormat(): string
    {
        return $this->withFormat(self::FORMAT_12_HOUR);
    }

    protected function withFormat(string $format): string
    {
        return \date_create_immutable_from_format(
            self::FORMAT_24_HOUR_INT_ONLY, 
            $this->hour
            )->format($format);
    }
}
