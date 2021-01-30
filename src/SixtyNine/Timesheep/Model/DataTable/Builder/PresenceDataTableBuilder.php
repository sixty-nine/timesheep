<?php

namespace SixtyNine\Timesheep\Model\DataTable\Builder;

use SixtyNine\Timesheep\Model\DataTable\DataTable;
use SixtyNine\Timesheep\Model\NonOverlappingPeriodList;
use SixtyNine\Timesheep\Model\Period;
use SixtyNine\Timesheep\Storage\Entity\Entry;

class PresenceDataTableBuilder
{
    public static function build(array $entries, $splitLongerThan = 0, $splitDuration = 0.5): DataTable
    {
        // Handle null params
        $splitLongerThan = $splitLongerThan ?? 0;
        $splitDuration = $splitDuration ?? 0.5;

        $periods = array_map(static function (Entry $entry) {
            return $entry->getPeriod();
        }, $entries);

        $blocks = new NonOverlappingPeriodList($periods);

        if ($splitLongerThan > 0) {
            $blocks = $blocks->splitPeriodsLongerThan($splitLongerThan, $splitDuration);
        }

        $headers = ['Date', 'Start', 'End', 'Duration', 'Decimal'];
        $table = new DataTable($headers);
        $lastDate = null;

        /** @var Period $p */
        foreach ($blocks->getPeriods() as $p) {
            $date = $p->getStartFormatted('Y-m-d');

            if ($lastDate !== $date) {
                if ($lastDate) {
                    $table->addSeparator();
                }
                $lastDate = $date;
            }

            $table->addRow([
                $p->getStartFormatted('Y-m-d'),
                (null !== $p->getStart()) ? $p->getStart()->format('H:i') : '-',
                (null !== $p->getEnd()) ? $p->getEnd()->format('H:i') : '-',
                $p->getDurationString(),
                $p->getDuration() . 'h',
            ]);
        }

        return $table;
    }
}
