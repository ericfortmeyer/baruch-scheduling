<?php

namespace BaruchScheduling\Models;

interface Comparable
{
    public function compareDatetimeString(string $date_time): bool;
}
