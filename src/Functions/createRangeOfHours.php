<?php

namespace BaruchScheduling\Functions;

use BaruchScheduling\Models\Hour;
use BaruchScheduling\Models\Comparable;

/**
 * Create an array of Hour objects
 * 
 * Each hour can be flagged as booked.  An array of objects that represents "events", "appointments",
 * etc. should have a method that takes an ISO 8601 date time string as an argument to determine
 * if the "event" is on the date and time represented by the date time string.  The array of objects
 * should also be provided as an argument to this function.  Since this function and the Hour
 * objects that it creates are not concerned with what "events" are provided, the library is
 * kept free from complicated logic coordinating events with date time abstractions.  A trade-off
 * for this simplified approach is that if only a portion of all "events" are provided, there is
 * a risk that this part of the application will not receive the information necessary to ensure
 * that all "Hours" that should be flagged as booked will be.
 * 
 * 1. The first hour is the start of the range of hours
 * 2. The last hour is the end of the range of hours.
 *    For example, it can be the hour the business is closed minus one
 * 3. An ISO 8601 date without the time is concatenated with each hour.  The resulting string
 *    is used as a parameter in the constructor of a DateTimeImmutable object.
 *    An ISO date time string is then produced to filter events to determine which hours
 *    in the range need to be flagged as booked.
 * 4. An array of objects that have a method that takes an ISO 8601 date time string as an
 *    argument to determine if the event that the object represents is on the date and time
 *    represented by the date time string.
 * 
 * @param string $first_hour
 * @param string $last_hour
 * @param string $date yyyy-mm-dd
 * @param array<Comparable> $events
 * 
 * @return array<Hour>
 */
function createRangeOfHours(string $first_hour, string $last_hour, string $date, array $events = []): array
{
    return array_map(
        function (string $hour) use ($date, $events): Hour
        {
            $date_time = (new \DateTimeImmutable("${date}T${hour}:00"))->format("c");
            $isBooked = !empty(
                current(
                    array_filter(
                        $events,
                        function (Comparable $event) use ($date_time) {
                            return $event->compareDatetimeString($date_time);
                        }
                    )
                )
            );
            return new Hour($hour, $isBooked);
        },
        range($first_hour, $last_hour)
    );
}
