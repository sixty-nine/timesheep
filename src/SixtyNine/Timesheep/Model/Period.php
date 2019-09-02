<?php

namespace SixtyNine\Timesheep\Model;

use DateTimeImmutable;

class Period
{
    /** @var ?DateTimeImmutable */
    protected $start;
    /** @var ?DateTimeImmutable */
    protected $end;

    /**
     * Period constructor.
     * @param DateTimeImmutable $start
     * @param DateTimeImmutable $end
     */
    public function __construct(DateTimeImmutable $start = null, DateTimeImmutable $end = null)
    {
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * @param DateTimeImmutable $date
     * @return Period
     */
    public static function getWeek(DateTimeImmutable $date): Period
    {
        $dow = $date->format('w');
        // FIXME week start
        $from = $dow !== '1' ? $date->modify('last monday') : $date;
        $to = $dow !== '0' ? $date->modify('next sunday')->modify('-1 day') : $date;
        return new self($from, $to);
    }

    public static function getMonth(DateTimeImmutable $date): Period
    {
        $dom = (int)$date->format('m');
        $nd = (int)$date->format('t');
        $from = $dom !== 1 ? $date->modify('first day of this month') : $date;
        $to = $dom !== $nd ? $date->modify('last day of this month')->modify('-1 day') : $date;
        return new self($from, $to);
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getStart(): ?DateTimeImmutable
    {
        return $this->start;
    }

    /**
     * @param DateTimeImmutable $start
     * @return Period
     */
    public function setStart(DateTimeImmutable $start): Period
    {
        $this->start = $start;
        return $this;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getEnd(): ?DateTimeImmutable
    {
        return $this->end;
    }

    /**
     * @param DateTimeImmutable $end
     * @return Period
     */
    public function setEnd(DateTimeImmutable $end): Period
    {
        $this->end = $end;
        return $this;
    }
}
