<?php


namespace SixtyNine\Timesheep\Model;

use DateTimeImmutable;

class Schedule
{
    /** @var Calendar */
    private $cal;
    /** @var int */
    private $hoursPerDay;
    /** @var float */
    private $occupationRate;

    public function __construct(int $hoursPerDay = 8, float $occupationRate = 1)
    {
        $this->cal = new Calendar();
        $this->hoursPerDay = $hoursPerDay;
        $this->occupationRate = $occupationRate;
    }

    public function dueHoursPerWeek(): float
    {
        return $this->hoursPerDay * 5 * $this->occupationRate;
    }

    public function dueHoursPerMonth(DateTimeImmutable $date = null): float
    {
        return $this->cal->getWorkingDays($date ?? new DateTimeImmutable())
            * $this->hoursPerDay
            * $this->occupationRate;
    }
}
