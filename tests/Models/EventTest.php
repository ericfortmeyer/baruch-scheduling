<?php

namespace BaruchScheduling\Models;

use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    protected $test_params = [];

    protected function setup()
    {
        $id = $this->getHash();
        $creator_id = $owner_id = $this->getHash();
        $duration = 90;
        $date_time = date("c");
        $type = "appointment";
        
        $this->test_params = [
            $id,
            $creator_id,
            $owner_id,
            $date_time,
            $duration,
            $type
        ];
    }

    protected function getHash(): string
    {
        return hash(
            "sha256",
            bin2Hex(random_bytes(64))
        );
    }

    public function testExists()
    {
        assertInstanceOf(
            Event::class,
            new Event(...$this->test_params)
        );
    }

    public function testHasExpectedDate()
    {
        assertEquals(
            date("Y-m-d"),
            (new Event(...$this->test_params))->date
        );
    }

    public function testHasExpectedTime()
    {
        assertEquals(
            date("H:i:s"),
            (new Event(...$this->test_params))->time
        );
    }

    public function testHasExpectedDurationInMinutes()
    {
        $minutes_test_case = range(10, 240, 30);
        $params = $this->test_params;

        foreach ($minutes_test_case as $minutes) {
            $params[4] = $minutes;
            assertEquals(
                $minutes,
                (new Event(...$params))->duration_in_minutes
            );
        }
    }

    public function testHasExpectedDurationInHours()
    {
        $minutes_test_case = range(10, 240, 30);
        $params = $this->test_params;

        $expected_hours = array_map(
            function ($hour) {
                return round($hour / 60, 2);
            },
            $minutes_test_case
        );

        array_map(
            function ($minutes, $expected_hour) use ($params) {
                $params[4] = $minutes;
                assertEquals(
                    $expected_hour,
                    (new Event(...$params))->duration_in_hours
                );
            },
            $minutes_test_case,
            $expected_hours
        );
    }
}
