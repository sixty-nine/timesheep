<?php


trait CastingHoursToIntTrait
{
    /**
     * @Transform /^(-?\d+)h$/
     */
    public function castHoursToInt(string $hours): int
    {
        return (int)$hours;
    }

}
