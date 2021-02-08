<?php

namespace SixtyNine\Timesheep\Model\DataTable\Builder;

use SixtyNine\Timesheep\Model\DataTable\DataTable;
use SixtyNine\Timesheep\Model\NonOverlappingPeriodList;
use SixtyNine\Timesheep\Model\Period;
use SixtyNine\Timesheep\Storage\Entity\Entry;

class PresenceDataTableBuilder
{
    public static function build(
        array $entries,
        $dateFormat = 'd-m-Y',
        $timeFormat = 'H:i',
        $aggregateDate = true
    ): DataTable {

        $periods = array_map(static function (Entry $entry) {
            return $entry->getPeriod();
        }, $entries);

        $blocks = new NonOverlappingPeriodList($periods);

        $headers = ['Date', 'Start', 'End', 'Duration', 'Decimal'];
        $table = new DataTable($headers);
        $lastDate = null;

        /** @var Period $p */
        foreach ($blocks->getPeriods() as $p) {
            $date = $p->getStartFormatted($dateFormat);

            $newRow = $lastDate !== $date;
            if ($newRow) {
                if ($lastDate) {
                    $table->addSeparator();
                }
                $lastDate = $date;
            }

            $table->addRow([
                (!$aggregateDate || $newRow) ? $p->getStartFormatted($dateFormat) : '',
                (null !== $p->getStart()) ? $p->getStart()->format($timeFormat) : '-',
                (null !== $p->getEnd()) ? $p->getEnd()->format($timeFormat) : '-',
                $p->getDurationString(),
                $p->getDuration() . 'h',
            ]);
        }

        return $table;
    }
}
