<?php

namespace SixtyNine\Timesheep\Console\Command\Database;

use SixtyNine\Timesheep\Console\TimesheepCommand;
use SixtyNine\Timesheep\Helper\Numbers;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DbInfoCommand extends TimesheepCommand
{
    protected static $defaultName = 'db:info';

    protected function configure(): void
    {
        $this->setDescription('Get information about the database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dbParams = $this->em->getConnection()->getParams();
        $dbSize = Numbers::humanFileSize(filesize($dbParams['path']));

        $output->writeln('');
        $output->writeln("Url: <info>${dbParams['url']}</info>");
        $output->writeln("Driver: <info>${dbParams['driver']}</info>");
        $output->writeln("Path: <info>${dbParams['path']}</info>");
        $output->writeln('');

        $output->write([
            'Entries count: <info>',
            count($this->entriesRepo->findAll()),
            '</info> entries']);
        $output->writeln('');

        $output->write([
            'Project count: <info>',
            count($this->projectRepo->findAll()),
            '</info> projects']);

        $output->writeln('');
        $output->writeln("File size: <info>${dbSize}</info>");
        $output->writeln('');
    }
}
