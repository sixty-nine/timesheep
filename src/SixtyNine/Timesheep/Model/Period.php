<?php

namespace SixtyNine\Timesheep\Model;

use DateTimeImmutable;
use Webmozart\Assert\Assert;

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

    public static function fromString(?string $start, ?string $end): Period
    {
        /** @var int $startTs */
        $startTs = strtotime($start ?? '');
        /** @var int $endTs */
        $endTs = strtotime($end ?? '');

        Assert::true(false !== $startTs, 'Invalid start time');
        Assert::true(false !== $endTs, 'Invalid end time');

        $startDate = (new DateTimeImmutable())->setTimestamp($startTs);
        $endDate = (new DateTimeImmutable())->setTimestamp($endTs);

        if ($endDate < $startDate) {
            $endDate = $endDate->modify('+1 day');
        }

        return new self($startDate, $endDate);
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getStart(): ?DateTimeImmutable
    {
        return $this->start;
    }

    /**
     * @param string $format
     * @return string
     */
    public function getStartFormatted(string $format): string
    {
        return $this->start ? $this->start->format($format) : '-';
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getEnd(): ?DateTimeImmutable
    {
        return $this->end;
    }

    /**
     * @param string $format
     * @return string
     */
    public function getEndFormatted(string $format): string
    {
        return $this->end ? $this->end->format($format) : '-';
    }

    public function getDurationString(): string
    {
        if (!$this->end || !$this->start) {
            return '-';
        }

        $diff = $this->end->diff($this->start);
        return $diff->format('%H:%I');
    }

    public function getDuration(): float
    {
        if (!$this->end || !$this->start) {
            return 0;
        }

        $diff = $this->end->diff($this->start);
        return $diff->h + round($diff->i / 60, 2);
    }

    public function getFirstDateOrToday(): DateTimeImmutable
    {
        if ($this->start) {
            return $this->start;
        }
        if ($this->end) {
            return $this->end;
        }
        return new DateTimeImmutable();
    }
}
