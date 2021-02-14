<?php

namespace SixtyNine\Timesheep\Console\Command\Database;

use SixtyNine\Timesheep\Console\Style\MyStyle;
use SixtyNine\Timesheep\Console\TimesheepCommand;
use SixtyNine\Timesheep\Helper\Numbers;
use SixtyNine\Timesheep\Storage\Entity\Entry;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DbInfoCommand extends TimesheepCommand
{
    protected static $defaultName = 'db:info';

    protected function configure(): void
    {
        $this->setDescription('Get information about the database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dateFormat = $this->config->get('format.date');

        $io = new MyStyle($input, $output);

        $dbParams = $this->em->getConnection()->getParams();
        $size = filesize($dbParams['path']);
        $dbSize = Numbers::humanFileSize($size ?: 0);

        $allProjects = $this->projectRepo->findAll();
        $allEntries = $this->entriesRepo->findAll();
        /** @var Entry $firstEntry */
        $firstEntry = reset($allEntries);
        /** @var Entry $firstEntry */
        $lastEntry = end($allEntries);

        $io->title('<comment>Database file</comment>');

        $output->writeln("Url: <info>${dbParams['url']}</info>");
        $output->writeln("Driver: <info>${dbParams['driver']}</info>");
        $output->writeln("Path: <info>${dbParams['path']}</info>");
        $output->writeln("File size: <info>${dbSize}</info>");
        $output->writeln('');

        $io->title('<comment>Entries</comment>');

        $output->write(['Entries count: <info>', count($allEntries), '</info> entries', PHP_EOL]);
        $output->write([
            'First entry: <info>',
            $firstEntry->getStartFormatted($dateFormat),
            '</info>',
            PHP_EOL,
        ]);
        $output->write([
            'Last entry: <info>',
            $lastEntry->getStartFormatted($dateFormat),
            '</info>',
            PHP_EOL,
        ]);
        $output->writeln('');

        $io->title('<comment>Projects</comment>');

        $output->write(['Projects count: <info>', count($allProjects), '</info> projects']);

        $output->writeln('');
        $output->writeln('');

        return 0;
    }
}
