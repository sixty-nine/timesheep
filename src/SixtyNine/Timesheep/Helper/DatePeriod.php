<?php

namespace SixtyNine\Timesheep\Helper;

use DateInterval;
use DatePeriod as BaseDatePeriod;
use DateTimeImmutable;
use DateTimeInterface;

class DatePeriod
{
    public static function getDatePeriod(
        DateTimeInterface $start,
        DateTimeInterface $end
    ): BaseDatePeriod {
        return new BaseDatePeriod($start, new DateInterval('P1D'), $end);
    }

    public static function getWeek(DateTimeImmutable $date): BaseDatePeriod
    {
        $dow = $date->format('w');
        // FIXME week start
        $from = $dow !== '1' ? $date->modify('last monday') : $date;
        $to = $dow !== '0' ? $date->modify('next sunday')->modify('-1 day') : $date;
        return self::getDatePeriod($from, $to);
    }

    public static function getMonth(DateTimeImmutable $date): BaseDatePeriod
    {
        $dom = (int)$date->format('m');
        $nd = (int)$date->format('t');
        $from = $dom !== 1 ? $date->modify('first day of this month') : $date;
        $to = $dom !== $nd ? $date->modify('last day of this month')->modify('-1 day') : $date;
        return self::getDatePeriod($from, $to);
    }
}
