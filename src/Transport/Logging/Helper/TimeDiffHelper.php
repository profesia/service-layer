<?php declare(strict_types=1);

namespace Profesia\ServiceLayer\Transport\Logging\Helper;

use DateTimeImmutable;

class TimeDiffHelper
{
    public static function calculateDiffInMicroseconds(DateTimeImmutable $start, DateTimeImmutable $stop): float
    {
        return (float)$stop->format('U.u') - (float)$start->format('U.u');
    }
}
