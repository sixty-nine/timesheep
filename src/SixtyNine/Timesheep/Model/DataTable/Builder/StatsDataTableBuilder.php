<?php

namespace SixtyNine\Timesheep\Model\DataTable\Builder;

use SixtyNine\Timesheep\Model\DataTable\DataTable;
use SixtyNine\Timesheep\Model\DateStrings;
use SixtyNine\Timesheep\Model\ProjectStatistics;

class StatsDataTableBuilder
{
    public static function build(
        ProjectStatistics $stats
    ): DataTable {

        $ds = new DateStrings();
        $table = new DataTable(['Project', 'Duration', 'Decimal']);

        foreach ($stats->getProjectsHours() as $project => $hours) {
            $table->addRow([
                $project,
                sprintf('%sh', $hours), $ds->decimalToTime($hours)
            ]);
        }

        return $table;
    }
}
