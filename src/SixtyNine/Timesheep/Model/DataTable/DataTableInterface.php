<?php

namespace SixtyNine\Timesheep\Model\DataTable;

interface DataTableInterface
{
    public function addRow(array $row): void;
    public function addSeparator(): void;
    public function getHeaders(): array;
    public function getRows(): array;
}
