<?php

trait CastingDateTrait
{
    /**
     * @Transform /^(\d?\d-\d?\d-\d\d\d\d)$/
     */
    public function castDate(string $date)
    {
        return new DateTimeImmutable($date);
    }
}
