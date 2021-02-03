<?php

namespace SixtyNine\Timesheep\Console\Command\Database;

use SixtyNine\Timesheep\Console\TimesheepCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ArchiveDbCommand extends TimesheepCommand
{
    protected static $defaultName = 'db:archive';

    protected function configure(): void
    {
        $this
            ->setDescription('Archive entries in a separate database.')
            ->addOption('from', null, InputOption::VALUE_OPTIONAL, 'From datetime')
            ->addOption('to', null, InputOption::VALUE_OPTIONAL, 'To datetime')
            ->addOption('week', null, InputOption::VALUE_NONE, 'Whole week')
            ->addOption('month', null, InputOption::VALUE_NONE, 'Whole month')
            ->addOption('day', null, InputOption::VALUE_NONE, 'Whole day')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the archive')
            ->addArgument('output-file', InputArgument::REQUIRED, 'Output Sqlite3 database.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        throw new \Exception('Not implemented');
    }
}
