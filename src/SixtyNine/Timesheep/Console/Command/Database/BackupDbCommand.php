<?php

namespace SixtyNine\Timesheep\Console\Command\Database;

use SixtyNine\Timesheep\Console\TimesheepCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BackupDbCommand extends TimesheepCommand
{
    protected static $defaultName = 'db:backup';

    protected function configure(): void
    {
        $this
            ->setDescription('Create a backup of the entire DB.')
            ->addArgument('output-file', InputArgument::REQUIRED, 'Output Sqlite3 database.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        throw new \Exception('Not implemented');
    }
}
