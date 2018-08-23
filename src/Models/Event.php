<?php

namespace BaruchScheduling\Models;

/**
 * An extendable class for schedulable events
 * 
 * id
 * creator
 * owner
 * participants
 * date_time
 * date
 * time
 * duration_in_hours
 * duration_in_minutes
 * type
 * description
 * notes
 */
class Event
{
    /**
     * @var string|int
     */
    public $id = "";

    /**
     * @var string|int
     */
    public $creator_id = "";

    /**
     * @var string|int
     */
    public $owner_id = "";

    
    /**
     * @var string
     */
    public $date_time = "";
    
    /**
     * @var string
     */
    public $date = "";
    
    /**
     * @var string
     */
    public $time = "";
    
    /**
     * @var float
     */
    public $duration_in_hours = 0;
    
    /**
     * @var int
     */
    public $duration_in_minutes = 0;
    
    /**
     * @var string
     */
    public $type = "";
    
    /**
     * @var string
     */
    public $description = "";
    
    /**
     * @var array<string|int>
     */

     public $participants = [];

    /**
     * @var string
     */
    public $notes = "";

    /**
     * Construct an Event
     * 
     * @param string|int $id
     * @param string|int $creator_id
     * @param string|int $owner_id
     * @param string $date_time
     * @param int $duration_in_minutes
     * @param string $type
     */
    public function __construct(
        $id,
        $creator_id,
        $owner_id,
        $date_time,
        $duration_in_minutes,
        $type,
        $description = "",
        $participants = [],
        $notes = ""
    ) {
        $this->id = $id;
        $this->creator_id = $creator_id;
        $this->owner_id = $owner_id;
        $this->date_time = $date_time;
        $this->date = $this->extractDate($date_time);
        $this->time = $this->extractTime($date_time);
        $this->duration_in_minutes = $duration_in_minutes;
        $this->duration_in_hours = $this->minutesToHours($duration_in_minutes);
        $this->type = $type;
        $this->description = $description;
        $this->participants = $participants;
        $this->notes = $notes;
    }

    /**
     * Get the date from the ISO 8601 provided date
     * 
     * @param string $iso_8601_date_time
     * @return string
     */
    protected function extractDate(string $iso_8601_date_time): string
    {
        return current(
            explode(
                "T",
                $iso_8601_date_time
            )
        );
    }

    /**
     * Get the time in 24 hour format from the ISO 8601 provided date
     * 
     * @param string $iso_8601_date_time
     * @return string
     */
    protected function extractTime(string $iso_8601_date_time): string
    {
        return current(
            explode(
                "+",
                current(
                    explode(
                        "-",
                        explode("T", $iso_8601_date_time)[1]
                    )
                )
            )
        );
    }

    /**
     * @param int $minutes
     * @return float
     */
    protected function minutesToHours(int $minutes): float
    {
        return round($minutes / 60, 2);
    }
}
