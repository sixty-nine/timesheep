<?php


namespace SixtyNine\Timesheep\Model;


use Symfony\Component\Console\Helper\TableSeparator;
use Webmozart\Assert\Assert;

class DataTable
{
    /** @var array[string] */
    private $headers;
    /** @var array */
    private $rows;

    /**
     * DataTable constructor.
     * @param array $headers
     * @param array $rows
     */
    public function __construct(array $headers, array $rows = [])
    {
        $this->headers = $headers;
        $this->rows = $rows;
    }

    public function addRow(array $row): void
    {
        Assert::eq(count($row), count($this->headers), 'Invalid number of values');
        $this->rows[] = $row;
    }

    public function addSeparator(): void
    {
        $this->rows[] = [];
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getRows(): array
    {
        return $this->rows;
    }

    public function getRowsWithTableSeparators(): array
    {
        return array_map(static function ($row) {
            return empty($row) ? new TableSeparator() : $row;
        }, $this->rows);
    }
}
