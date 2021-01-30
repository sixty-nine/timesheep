<?php

namespace SixtyNine\Timesheep\Model;

use Doctrine\Common\Collections\ArrayCollection;

class NonOverlappingPeriodList
{
    /** @var ArrayCollection */
    private $list;

    /**
     * PeriodList constructor.
     * @param array[Period] $periods
     */
    public function __construct(array $periods = [])
    {
        $this->list = new ArrayCollection();
        $this->addPeriods($periods);
    }

    public function duplicate(): NonOverlappingPeriodList
    {
        return new NonOverlappingPeriodList($this->getPeriods());
    }

    public function merge(NonOverlappingPeriodList $list): NonOverlappingPeriodList
    {
        $newList = $this->duplicate();
        $newList->addPeriods($list->getPeriods());
        return $newList;
    }

    public function getPeriods(): array
    {
        $arr = $this->list->toArray();

        usort($arr, static function (Period $a, Period $b) {
            $start1 = $a->getStart();
            $start2 = $b->getStart();
            return strcmp(
                $start1 ? $start1->getTimestamp() : 0,
                $start2 ? $start2->getTimestamp() : 0
            );
        });

        return $arr;
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

    public function splitPeriodsLongerThan(float $minDuration, float $splitDuration = 0.5): NonOverlappingPeriodList
    {
        $longKeys = $this->findKeysLongerThan($minDuration);

        if (empty($longKeys)) {
            return $this->duplicate();
        }

        $newList = new self();
        $split = [];

        /** @var Period $value */
        foreach ($this->list as $key => $value) {
            if (!in_array($key, $longKeys, true)) {
                $newList->addPeriod($value);
                continue;
            }

            $split[] = $value->split($splitDuration);
        }

        /** @var NonOverlappingPeriodList $list */
        foreach ($split as $list) {
            /** @var Period $period */
            foreach ($list->getPeriods() as $period) {
                $overlappingKeys = $newList->findOverlappingKeys($period);
                if (empty($overlappingKeys)) {
                    $newList->addPeriod($period);
                    continue;
                }


                $strictOverlappingKeys = $newList->findOverlappingKeys($period, true);
//bin/ts e:ls --from "2021-01-22" --day --presence --split 4 --split-duration 0.75
                var_dump(
                    $period,
                    $strictOverlappingKeys
                );
                if (empty($strictOverlappingKeys)) {
                    $newList->addPeriod($period);
                    continue;
                }

                die('no');
                // TODO
            }
        }

        return $newList->splitPeriodsLongerThan($minDuration, $splitDuration);
    }

    private function findOverlappingKeys(Period $period, $strict = false): array
    {
        $overlappingKeys = [];
        /** @var Period $value */
        foreach ($this->list as $key => $value) {
            $strictOverlap = $strict && $period->strictOverlaps($period);
            $nonStrictOverlap = !$strict && $period->overlaps($value);
            if ($strictOverlap || $nonStrictOverlap) {
                $overlappingKeys[] = $key;
            }
        }
        return $overlappingKeys;
    }

    private function findKeysLongerThan(float $duration): array
    {
        $longKeys = [];
        /** @var Period $value */
        foreach ($this->list as $key => $value) {
            if ($value->getDuration() > $duration) {
                $longKeys[] = $key;
            }
        }
        return $longKeys;
    }
}
