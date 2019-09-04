<?php

use SixtyNine\Timesheep\Model\Period;

trait CastingTimesToPeriodTrait
{
    /**
     * @transform /^from ([^ ]*) to ([^ ]*)(?: on ([^ ]*))?$/
     */
    public function castTimesToPeriod(string $start, string $end, string $date = null): Period
    {
        $startDate = $this->castTimeToDate($start, new DateTimeImmutable($date));
        $endDate = $this->castTimeToDate($end, new DateTimeImmutable($date));
        if ($endDate < $startDate) {
            $endDate = $endDate->modify('+1 day');
        }

        return new Period($startDate, $endDate);
    }
}
