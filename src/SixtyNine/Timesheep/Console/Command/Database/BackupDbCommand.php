<?php

namespace SixtyNine\Timesheep\Console\Command\Database;

use InvalidArgumentException;
use SixtyNine\Timesheep\Console\TimesheepCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $outPath = $input->getArgument('output-file');

        $dbParams = $this->em->getConnection()->getParams();
        $path = $dbParams['path'];

        if (!file_exists($path)) {
            throw new InvalidArgumentException('Cannot find DB file: ' . $path);
        }

        if (file_exists($outPath) && !is_writable($outPath)) {
            throw new InvalidArgumentException('The output file is not writable: ' . $outPath);
        }

        $process = new Process(['cp', $path, $outPath]);
        $process->run();

        if (!$process->isSuccessful()) {
            $io->error('Error while copying the database: ' . $process->getErrorOutput());
        } else {
            $io->writeln('Database copied to ' . $outPath);
        }

        return 0;
    }
}
