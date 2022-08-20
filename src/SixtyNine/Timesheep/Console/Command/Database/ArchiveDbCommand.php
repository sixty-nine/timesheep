<?php

namespace SixtyNine\Timesheep\Console\Command\Database;

use Exception;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use SixtyNine\Timesheep\Console\Style\MyStyle;
use SixtyNine\Timesheep\Console\TimesheepCommand;
use SixtyNine\Timesheep\Storage\Entity\Entry;
use SQLite3;
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
        $io = new MyStyle($input, $output);
        $io->title('Archive database');

        $period = $this->getPeriodFromParams($input);
        $outputFile = $input->getArgument('output-file');
        $dateTimeFormat = $this->config->get('datetime_format');

        try {
            $fs = new Filesystem(new Local(dirname($outputFile)));
            if ($fs->has($outputFile)) {
                $io->writeln(sprintf('Deleting output file <info>%s</info>', $outputFile));
                $fs->delete($outputFile);
            }

            $io->writeln('Copying entries');
            $entries = $this->entriesRepo->getAllEntries($period);
            $db = new SQLite3($outputFile);
            $db->exec($this->getCreateStatement());
            /** @var Entry $entry */
            foreach ($entries as $entry) {
                $db->exec(sprintf(
                    'INSERT INTO entries(start, end, project, task, description) ' .
                    'VALUES(\'%s\', \'%s\', \'%s\', \'%s\', \'%s\')',
                    $entry->getStartFormatted($dateTimeFormat),
                    $entry->getEndFormatted($dateTimeFormat),
                    $entry->getProject(),
                    $entry->getTask(),
                    $entry->getDescription()
                ));
                $io->write('.');
            }
            $io->newLine();
            $io->writeln('Done');
            $io->newLine();
        } catch (Exception $ex) {
            $io->error('An error occurred: ' . $ex->getMessage());
        }

        return 0;
    }

    private function getCreateStatement(): string
    {
        return <<<SQL
CREATE TABLE entries (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL
    , start DATETIME NOT NULL --(DC2Type:datetime_immutable)
    , "end" DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
    , project VARCHAR(255) DEFAULT NULL
    , task VARCHAR(255) DEFAULT NULL
    , description VARCHAR(255) DEFAULT NULL
);
SQL;
    }
}
