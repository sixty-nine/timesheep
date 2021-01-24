<?php

namespace SixtyNine\Timesheep\Model\DataTable\Builder;

use SixtyNine\Timesheep\Model\DataTable\DataTable;
use SixtyNine\Timesheep\Storage\Entity\Entry;

class EntriesDataTableBuilder
{
    public static function build(array $entries): DataTable
    {
        $headers = ['Day', 'From', 'To', 'Duration', 'Project', 'Task', 'Description'];
        $table = new DataTable($headers);
        $padding = strlen(' Duration ') - 2;
        $lastDate = null;

        /** @var Entry $entry */
        foreach ($entries as $entry) {
            $entryDate = $entry->getStartFormatted('Y-m-d');
            $date = $lastDate !== $entryDate ? $entryDate : '';
            $lastDate = $entryDate;

            $table->addRow([
                $date,
                $entry->getStart()->format('H:i'),
                $entry->getEndFormatted('H:i'),
                str_pad($entry->getPeriod()->getDurationString(), $padding, ' ', STR_PAD_LEFT),
                $entry->getProject(),
                $entry->getTask(),
                $entry->getDescription(),
            ]);
        }

        return $table;
    }
}
