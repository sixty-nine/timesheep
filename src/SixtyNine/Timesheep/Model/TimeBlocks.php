<?php

namespace SixtyNine\Timesheep\Model;

use DateTimeImmutable;

/**
 * An array of non-contiguous periods of time.
 */
class TimeBlocks
{
    /** @var array[int => array[start, end]] */
    private $blocksByStart;
    /** @var array[int => array[start, end]] */
    private $blocksByEnd;

    public function __construct()
    {
        $this->blocksByStart = [];
        $this->blocksByEnd = [];
    }

    /**
     * This function assumes you don't add overlapping blocks.
     * I.e. if you add 10:00-12:00 and 11:00-13:00 this will not work.
     * @param Period $period
     */
    public function addPeriod(Period $period): void
    {
        $start = $period->getStart();
        $end = $period->getEnd();

        if (!$start || !$end) {
            return;
        }

        $blockBefore = $this->searchBlockEndingAt($start);
        $blockAfter = $this->searchBlockStartingAt($end);

        switch (true) {
            // Isolated block --> add new
            case !$blockBefore && !$blockAfter:
                $this->addBlock($start, $end);
                break;
            // Touching previous block --> extend previous block
            case $blockBefore && !$blockAfter:
                $this->removeBlock($blockBefore[0], $blockBefore[1]);
                $this->addBlock($blockBefore[0], $end);
                break;
            // Touching next block --> extend next block
            case !$blockBefore && $blockAfter:
                $this->removeBlock($blockAfter[0], $blockAfter[1]);
                $this->addBlock($start, $blockAfter[1]);
                break;
            // Touching both previous and next block --> merge the 3 blocks
            default:
                $this->removeBlock($blockBefore[0], $blockBefore[1]);
                $this->removeBlock($blockAfter[0], $blockAfter[1]);
                $this->addBlock($blockBefore[0], $blockAfter[1]);
                break;
        }
    }

    public function getPeriods(): array
    {
        return array_map(static function ($block) {
            return new Period($block[0], $block[1]);
        }, $this->blocksByStart);
    }

    private function addBlock(DateTimeImmutable $start, DateTimeImmutable $end): void
    {
        $block = [$start, $end];
        $this->blocksByStart[$start->getTimestamp()] = $block;
        $this->blocksByEnd[$end->getTimestamp()] = $block;
    }

    private function removeBlock(DateTimeImmutable $start, DateTimeImmutable $end): void
    {
        unset(
            $this->blocksByStart[$start->getTimestamp()],
            $this->blocksByEnd[$end->getTimestamp()]
        );
    }

    private function searchBlockStartingAt(DateTimeImmutable $start)
    {
        $timestamp = $start->getTimestamp();
        return $this->blocksByStart[$timestamp] ?? null;
    }

    private function searchBlockEndingAt(DateTimeImmutable $end)
    {
        $timestamp = $end->getTimestamp();
        return $this->blocksByEnd[$timestamp] ?? null;
    }
}
