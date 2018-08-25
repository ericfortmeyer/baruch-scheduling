<?php

namespace BaruchScheduling\Models;

interface CompareByDayOfWeekInterface
{
    public function compareByDayOfWeek(string $day_of_the_week): bool;
}
