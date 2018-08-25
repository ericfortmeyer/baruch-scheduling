<?php

namespace BaruchScheduling\Models;

interface CompareByDateInterface
{
    public function compareByDate(string $date): bool;
}
