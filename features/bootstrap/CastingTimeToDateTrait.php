<?php

trait CastingTimeToDateTrait
{
    /**
     * @Transform /^(\d?\d:\d\d)$/
     */
    public function castTimeToDate(string $time, $curDate = null): DateTimeImmutable
    {
        $curDate = $curDate ?: new DateTime();
        return new DateTimeImmutable(
            sprintf('%s %s:00', $curDate->format('Y-m-d'), $time)
        );
    }
}
