<?php

namespace SixtyNine\Timesheep\Model\DataTable\Builder;

use SixtyNine\Timesheep\Helper\DateTimeHelper;
use SixtyNine\Timesheep\Model\DataTable\DataTable;
use SixtyNine\Timesheep\Model\ProjectStatistics;

class StatsDataTableBuilder
{
    public static function build(
        ProjectStatistics $stats,
        DateTimeHelper $dtHelper
    ): DataTable {

        $table = new DataTable(['Project', 'Duration', 'Decimal']);

        foreach ($stats->getProjectsHours() as $project => $hours) {
            $table->addRow([
                $project,
                sprintf('%sh', $hours), $dtHelper->decimalToTime($hours)
            ]);
        }

        return $table;
    }
}
