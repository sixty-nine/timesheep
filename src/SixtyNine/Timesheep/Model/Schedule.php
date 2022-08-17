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

    public function dueHoursPerWeek(int $duePerDay, float $occupation = 100): int
    {
        return $this->hoursPerDay * 5 * $this->occupationRate;
    }

    public function dueHoursPerMonth(int $duePerDay, float $occupation = 100, DateTimeImmutable $date = null): int
    {
        return $this->cal->getWorkingDays($date ?? new DateTimeImmutable()) * $this->hoursPerDay * $this->occupationRate;
    }
}
