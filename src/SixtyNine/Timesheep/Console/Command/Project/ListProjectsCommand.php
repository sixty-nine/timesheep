<?php

namespace SixtyNine\Timesheep\Console\Command\Project;

use SixtyNine\Timesheep\Console\Style\MyStyle;
use SixtyNine\Timesheep\Console\TimesheepCommand;
use SixtyNine\Timesheep\Model\DataTable\DataTable;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListProjectsCommand extends TimesheepCommand
{
    protected static $defaultName = 'proj:list';

    protected function configure()
    {
        $this
            ->setDescription('List all the projects.')
            ->setAliases(['proj:ls', 'p:ls']);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new MyStyle($input, $output);
        $io->title('Projects');

        $table = new DataTable(
            ['ID', 'Name', 'Description'],
            $this->projectRepo->findAll()
        );

        $io->outputTable($table, $this->config->get('console.box-style'));
    }
}
