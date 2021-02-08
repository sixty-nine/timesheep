<?php

namespace SixtyNine\Timesheep\Console\Style;

use SixtyNine\Timesheep\Helper\DateTimeHelper;
use SixtyNine\Timesheep\Model\DataTable\DataTable;
use SixtyNine\Timesheep\Model\Period;
use SixtyNine\Timesheep\Model\ProjectStatistics;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Style\SymfonyStyle;

class MyStyle extends SymfonyStyle
{
    /**
     * {@inheritdoc}
     */
    public function table(array $headers, array $rows, $styleName = 'symfony-style-guide')
    {
        $style = clone Table::getStyleDefinition($styleName);
        $style->setCellHeaderFormat('<info>%s</info>');

        $table = new Table($this);
        $table->setHeaders($headers);
        $table->setRows($rows);
        $table->setStyle($style);

        $table->render();
        $this->newLine();
    }

    public function outputPeriod(Period $period, string $dateFormat): void
    {
        $this->writeln([
            sprintf('From: <info>%s</info>', $period->getStartFormatted($dateFormat)),
            sprintf('To: <info>%s</info>', $period->getEndFormatted($dateFormat)),
            '',
        ]);
    }

    public function outputTable(DataTable $table, string $style): void
    {
        $this->table($table->getHeaders(), $table->getRows(), $style);
    }

    public function outputCsv(DataTable $table, $delimiter = ',', $quotes = '"'): void
    {
        $quote = static function (array $arr) use ($quotes) {
            return array_map(static function (string $item) use ($quotes) {
                return sprintf('%s%s%s', $quotes, trim($item), $quotes);
            }, $arr);
        };

        $this->writeln(implode($delimiter, $quote($table->getHeaders())));

        foreach ($table->getRows() as $row) {
            if (is_object($row)) {
                continue;
            }
            $this->writeln(implode($delimiter, $quote($row)));
        }
    }

    public function outputSummary(ProjectStatistics $stats): void
    {
        $total = $stats->getTotal();
        $this->writeln([
            sprintf('Total: <info>%sh</info>', DateTimeHelper::decimalToTime($total)),
            sprintf('Decimal: <info>%sh</info>', $total),
            '',
        ]);
    }
}
