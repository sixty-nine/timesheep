<?php

trait CastingTimesToSpanTrait
{
    /**
     * @transform /^from (.*) to (.*)$/
     */
    public function castTimesToSpan(string $start, string $end)
    {
        $startDate = $this->castTimeToDate($start);
        $endDate = $this->castTimeToDate($end);
        if ($endDate < $startDate) {
            $endDate = $endDate->modify('+1 day');
        }

        return [$startDate, $endDate];
    }
}
