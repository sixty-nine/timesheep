<?php

namespace SixtyNine\Timesheep\Model;

use Doctrine\Common\Collections\ArrayCollection;

class NonOverlappingPeriodList
{
    /** @var ArrayCollection<Period> */
    private $list;

    /**
     * PeriodList constructor.
     * @param array<Period> $periods
     */
    public function __construct(array $periods = [])
    {
        $this->list = new ArrayCollection();
        $this->addPeriods($periods);
    }

    public function getPeriods(): array
    {
        return $this->list->toArray();
    }

    public function addPeriods(array $periods): void
    {
        foreach ($periods as $period) {
            $this->addPeriod($period);
        }
    }

    public function addPeriod(Period $period): void
    {
        $overlappingKeys = $this->findOverlappingKeys($period);

        if (empty($overlappingKeys)) {
            $this->list->add($period);
            return;
        }

        $finalPeriod = $period->duplicate();

        foreach ($overlappingKeys as $key) {
            // remove the overlapping period from the list...
            $overlapping = $this->list->get($key);
            $this->list->remove($key);
            // ...and merge it with the one we want to insert
            $finalPeriod = $finalPeriod->merge($overlapping);
        }

        $this->addPeriod($finalPeriod);
    }

    public function split(string $splitTime = '12:00', int $splitDuration = 30): self
    {
        /** @var Period $lastPeriod */
        $lastPeriod = null;
        $splitPeriod = new self();

        /** @var Period $period */
        foreach ($this->list as $period) {
            if (null !== $lastPeriod
                && false !== $lastPeriod
                && $lastPeriod->overlaps($period)
            ) {
                $period = $period->moveAtEnd($lastPeriod);
            }

            $split = $period->split($splitTime, $splitDuration);
            $splitPeriod->addPeriods($split);
            $lastPeriod = end($split);
        }

        return $splitPeriod;
    }

    private function findOverlappingKeys(Period $period): array
    {
        $overlappingKeys = [];
        /** @var Period $value */
        foreach ($this->list as $key => $value) {
            if ($period->touches($value)) {
                $overlappingKeys[] = $key;
            }
        }
        return $overlappingKeys;
    }
}
