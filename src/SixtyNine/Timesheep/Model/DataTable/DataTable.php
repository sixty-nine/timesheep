<?php

namespace SixtyNine\Timesheep\Model\DataTable;

use Webmozart\Assert\Assert;

class DataTable implements DataTableInterface
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
}
