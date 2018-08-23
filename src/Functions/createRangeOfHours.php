<?php

namespace BaruchScheduling\Functions;

use BaruchScheduling\Models\Hour;
use BaruchScheduling\Models\Event;

/**
 * Create an array of Hour objects
 * 
 * @param string $first_hour
 * @param string $last_hour
 * @param string $date yyyy-mm-dd
 * @param array $events
 * 
 * @return array<Hour>
 */
function createRangeOfHours(string $first_hour, string $last_hour, string $date, array $events = []): array
{
    /**
     * Create an array of Hour objects
     * The end parameter of the range is the last hour
     */
    return array_map(
        function (string $hour) use ($date, $events): Hour
        {
            $date_time = (new \DateTimeImmutable("${date}T${hour}:00"))->format("c");
            $isBooked = !empty(
                current(
                    array_filter(
                        $events,
                        function (Event $event) use ($date_time) {
                            return $event->date_time === $date_time;
                        }
                    )
                )
            );
            return new Hour($hour, $isBooked);
        },
        range($first_hour, $last_hour)
    );
}
