<?php

namespace SixtyNine\Timesheep\Model;

use DateTimeImmutable;

class Calendar
{
    /**
     * @return string[]
     */
    public function getDayNames(): array
    {
        return ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    }

    public function firstDayOfMonth(DateTimeImmutable $date): DateTimeImmutable
    {
        return $date->modify('first day of this month');
    }

    public function lastDayOfMonth(DateTimeImmutable $date): DateTimeImmutable
    {
        return $date->modify('last day of this month');
    }

    public function getDayOfWeek(DateTimeImmutable $date): int
    {
        return getdate($date->getTimestamp())['wday'];
    }

    public function isWorkingDay(DateTimeImmutable $date): bool
    {
        $dayOfWeek = $this->getDayOfWeek($date);
        return $dayOfWeek > 0 && $dayOfWeek < 6;
    }

    public function getWorkingDays(DateTimeImmutable $date): int
    {
        $firstDate = $this->firstDayOfMonth($date);
        $lastDay = getdate($this->lastDayOfMonth($date)->getTimestamp());
        $count = 0;

        for ($i = 1; $i <= $lastDay['mday']; $i++) {
            if ($this->isWorkingDay($firstDate->modify(($i - 1) . ' day'))) {
                $count++;
            }
        }

        return $count;
    }

    public function dueHoursPerWeek(int $duePerDay, float $occupation = 100): int
    {
        return $duePerDay * 5 * $occupation;
    }

    public function dueHoursPerMonth(int $duePerDay, float $occupation = 100, DateTimeImmutable $date = null): int
    {
        return $this->getWorkingDays($date ?? new DateTimeImmutable()) * $duePerDay * $occupation;
    }
}
