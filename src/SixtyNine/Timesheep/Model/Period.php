<?php

namespace SixtyNine\Timesheep\Model;

use DateTimeImmutable;
use SixtyNine\Timesheep\Helper\DateTimeHelper;
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

    public static function fromDuration(DateTimeImmutable $start, int $minutes): self
    {
        $offsetString = sprintf('+%s minutes', $minutes);
        return new Period($start, $start->modify($offsetString));
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

    public function getDurationMinutes(): int
    {
        if (!$this->end || !$this->start) {
            return 0;
        }

        $diff = $this->end->diff($this->start);
        return $diff->h * 60 + $diff->i;
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

    public function touches(Period $period): bool
    {
        $startsInside = $period->getStart() >= $this->getStart()
                     && $period->getStart() <= $this->getEnd();

        $endsInside = $period->getEnd() >= $this->getStart()
            && $period->getEnd() <= $this->getEnd();

        return $startsInside || $endsInside;
    }

    public function overlaps(Period $period): bool
    {
        $startsInside = $period->getStart() > $this->getStart()
                     && $period->getStart() < $this->getEnd();

        $endsInside = $period->getEnd() > $this->getStart()
            && $period->getEnd() < $this->getEnd();

        return $startsInside || $endsInside;
    }

    public function merge(Period $period): Period
    {
        Assert::true($this->touches($period), 'Cannot merge non-touching periods');

        return new Period(
            $this->start <= $period->getStart() ? $this->start : $period->getStart(),
            $this->end >= $period->getEnd() ? $this->end : $period->getEnd()
        );
    }

    public function duplicate(): Period
    {
        return new Period($this->start, $this->end);
    }

    public function contains(\DateTimeInterface $date): bool
    {
        return $this->start <= $date && $date <= $this->end;
    }

    /**
     * @param string $splitTime
     * @param int $splitDuration
     * @return array<Period>
     * @throws \Exception
     */
    public function split(string $splitTime = '12:00', int $splitDuration = 30): array
    {
        Assert::true(DateTimeHelper::isValidDate($splitTime, 'H:i'), 'Invalid split time');
        Assert::greaterThan($splitDuration, 0, 'The split duration must be greater than zero');

        if (null === $this->start || null === $this->end) {
            return [$this->duplicate()];
        }

        $offsetString = sprintf('+%s minutes', $splitDuration);
        $splitStart = $this->start->modify($splitTime);
        $splitEnd = $splitStart->modify($offsetString);

        if (!$this->contains($splitStart) || !$this->contains($splitEnd)) {
            return [$this->duplicate()];
        }

        return [
            new self($this->start, $splitStart),
            new self($splitEnd, $this->end->modify($offsetString))
        ];
    }

    public function move(int $minutes): self
    {
        $offsetString = sprintf('+%s minutes', $minutes);
        return new self(
            $this->start->modify($offsetString),
            $this->end->modify($offsetString)
        );
    }

    public function moveAtEnd(Period $period): self
    {
        return self::fromDuration($period->getEnd(), $this->getDurationMinutes());
    }
}
