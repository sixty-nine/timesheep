<?php

namespace SixtyNine\Timesheep\Console\Style;

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
}
