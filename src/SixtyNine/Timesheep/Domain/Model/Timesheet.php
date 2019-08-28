<?php

namespace SixtyNine\Timesheep\Domain\Model;

use DateTimeImmutable;
use SixtyNine\Timesheep\Helper\DateTime as DateTimeHelper;

class Timesheet
{
    /** @var Entry[] */
    protected $entries = [];
    /** @var Entry|null */
    protected $curEntry;
    /** @var DateTimeImmutable */
    protected $curDate;

    public function addEntry(Entry $entry): Timesheet
    {
        $this->entries[] = $entry;
        $this->curEntry = $entry;
        return $this;
    }

    /**
     * @return Entry[]
     */
    public function getEntries(): array
    {
        return $this->entries;
    }

    /**
     * @return Entry
     */
    public function getCurEntry(): ?Entry
    {
        return $this->curEntry;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getCurDate(): DateTimeImmutable
    {
        return $this->curDate;
    }

    /**
     * @param DateTimeImmutable $curDate
     * @return Timesheet
     */
    public function setCurDate(DateTimeImmutable $curDate): Timesheet
    {
        $this->curDate = $curDate;
        return $this;
    }

    /**
     * Get the number of hours worked.
     */
    public function getWorkedTime(): int
    {
        return array_reduce($this->entries, static function (int $carry, Entry $item) {
            return $carry + $item->getDuration();
        }, 0);
    }

    public function checkIn(Project $project): void
    {
        $now = new DateTimeImmutable();

        // TODO: round start time
        // TODO: wrap over midnight

        $this->checkInAt($project, $now);
    }

    public function checkInAt(Project $project, DateTimeImmutable $start): void
    {
        // TODO: round start time
        // TODO: wrap over midnight

        $start = DateTimeHelper::getDateFromTime($this->curDate, $start);

        // If the previous period is not finished, finish it.
        if ($this->curEntry && !$this->curEntry->getEnd()) {
            if ($start < $this->curEntry->getStart()) {
                // It's the day after
                $start = $start->modify('+1 day');
                $this->curDate->modify('+1 day');
            }

            $this->curEntry->setEnd($start);
        }

        $this->curEntry = new Entry($project, $start);
        $this->entries[] = $this->curEntry;
    }
}
