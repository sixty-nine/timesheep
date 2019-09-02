<?php

use SixtyNine\Timesheep\Model\Period;

trait CastingTimesToPeriodTrait
{
    /**
     * @transform /^from (.*) to (.*)$/
     */
    public function castTimesToPeriod(string $start, string $end): Period
    {
        $startDate = $this->castTimeToDate($start);
        $endDate = $this->castTimeToDate($end);
        if ($endDate < $startDate) {
            $endDate = $endDate->modify('+1 day');
        }

        return new Period($startDate, $endDate);
    }
}
