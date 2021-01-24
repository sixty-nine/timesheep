<?php

namespace SixtyNine\Timesheep\Model\DataTable;

use Symfony\Component\Console\Helper\TableSeparator;

class SymfonyConsoleDataTable extends DataTable
{
    public static function fromDataTable(DataTable $table): SymfonyConsoleDataTable
    {
        return new self($table->getHeaders(), $table->getRows());
    }

    public function getRows(): array
    {
        return array_map(static function ($row) {
            return empty($row) ? new TableSeparator() : $row;
        }, parent::getRows());
    }
}
