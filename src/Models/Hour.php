<?php

namespace BaruchScheduling\Models;

final class Hour
{
    protected const FORMAT_WITH_MERIDIEM_LOWER = "g a";
    protected const FORMAT_WITH_MERIDIEM_UPPER = "g A";
    protected const FORMAT_24_HOUR = "H:i";
    protected const FORMAT_24_HOUR_WITH_SECONDS = "H:i:s";
    protected const FORMAT_24_HOUR_WITH_T = "\TH:i";
    protected const FORMAT_24_HOUR_WITH_T_AND_SECONDS = "\TH:i:s";
    protected const FORMAT_24_HOUR_WITH_T_TZ_AND_SECONDS = "\TH:i:sO";
    protected const FORMAT_24_HOUR_WITH_TZ_DIFF = "\TH:iO";
    protected const FORMAT_24_HOUR_INT_ONLY = "G";
    protected const FORMAT_12_HOUR = "g:i a";

    protected const DEFAULT_FORMAT = self::FORMAT_WITH_MERIDIEM_LOWER;

    /**
     * @var int
     */
    protected $hour = 0;

    /**
     * @var bool
     */
    public $isBooked;

    /**
     * @var bool
     */
    public $isPast;
    

    /**
     * Create a representation of an hour
     * Use the methods provided to produce a formatted string
     * The hour string provided in the constructor can be either an int
     * or a properly formatted ISO 8601 time string with or without timezone
     *
     * @param mixed $hour
     * @param bool $isBooked
     */
    public function __construct($hour, bool $isBooked = false, bool $isPast = false)
    {
        $this->hour = $hour;
        $this->isBooked = $isBooked;
        $this->isPast = $isPast;
    }

    public function __toString()
    {
        return $this->withFormat(self::DEFAULT_FORMAT);
    }

    public function hourOnlyFormat(): int
    {
        return (int) $this->withFormat(self::FORMAT_24_HOUR_INT_ONLY);
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
        $hour = (string) $this->hour;
        return \date_create_immutable_from_format(
            $this->mapTimeStringToFormat($hour),
            $hour
        )->format($format);
    }

    /**
     * Use to prevent errors when creating a date time object using the
     * time/hour string provided in the constructor.
     * This is required since the create from format function must know the
     * format of the string provided.
     * Using this method allows for flexibility when creating hour objects.
     *
     * @param string $time_string
     * @return string
     */
    protected function mapTimeStringToFormat(string $time_string): string
    {
        /**
         * Map the expected patterns of the time string to their respective date time formats
         */
        $formatMap = [
            "/^[0-9]{1,2}$/" => self::FORMAT_24_HOUR_INT_ONLY,
            "/^[0-9]{2}:[0-9]{2}$/" => self::FORMAT_24_HOUR,
            "/^[0-9]{2}:[0-9]{2}:[0-9]{2}$/" => self::FORMAT_24_HOUR_WITH_SECONDS,
            "/^T[0-9]{2}:[0-9]{2}$/" => self::FORMAT_24_HOUR_WITH_T,
            "/^T[0-9]{2}:[0-9]{2}:[0-9]{2}$/" => self::FORMAT_24_HOUR_WITH_T_AND_SECONDS,
            "/^T[0-9]{2}:[0-9]{2}:[0-9]{2}((-|\+)[0-9]{2,4})$/" => self::FORMAT_24_HOUR_WITH_T_TZ_AND_SECONDS,
            "/^T[0-9]{2}:[0-9]{2}((-|\+)[0-9]{2,4})$/" => self::FORMAT_24_HOUR_WITH_TZ_DIFF,
        ];

        /**
         * Select the key of the format map.
         * Use the pattern that matches the time string as the key.
         */
        return $formatMap[
            array_reduce(
                [
                    "/^[0-9]{1,2}$/",
                    "/^[0-9]{2}:[0-9]{2}$/",
                    "/^[0-9]{2}:[0-9]{2}:[0-9]{2}$/",
                    "/^T[0-9]{2}:[0-9]{2}$/",
                    "/^T[0-9]{2}:[0-9]{2}:[0-9]{2}$/",
                    "/^T[0-9]{2}:[0-9]{2}:[0-9]{2}((-|\+)[0-9]{2,4})$/",
                    "/^T[0-9]{2}:[0-9]{2}((-|\+)[0-9]{2,4})$/"
                ],
                function ($carry, string $pattern) use ($time_string) {
                    return $carry ?? (
                        preg_match($pattern, $time_string) == 1
                            ? $pattern
                            : null
                        );
                }
            )
        ] ?? $this->throwException($time_string);
    }

    protected function throwException(string $time_string)
    {
        throw new \RuntimeException("The time string, $time_string is not a supported format");
    }
}
