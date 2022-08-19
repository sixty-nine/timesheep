<?php

namespace SixtyNine\Timesheep\Console\Command\Project;

use SixtyNine\Timesheep\Console\Style\MyStyle;
use SixtyNine\Timesheep\Console\TimesheepCommand;
use SixtyNine\Timesheep\Model\DataTable\DataTable;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FindDuplicatedProjectsCommand extends TimesheepCommand
{
    protected static $defaultName = 'proj:duplicates';

    protected function configure(): void
    {
        $this
            ->setDescription('Find duplicates project.')
            ->setAliases(['proj:dup'])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new MyStyle($input, $output);

        $duplicatedProjects = $this->projectRepo->findDuplicates();

        $io->title('<comment>Duplicated projects</comment>');

        $table = new DataTable(['Code', 'Names', 'Ids']);
        foreach ($duplicatedProjects as $key => $list) {
            $table->addRow([
                '<comment>'.$key.'</comment>',
                implode(', ', array_column($list, 'name')),
                '<info>'.implode('</info>, <info>', array_column($list, 'id')).'</info>'
            ]);
        }

        $io->outputTable($table, $this->config->get('box_style'));
        $output->writeln('');

        return 0;
    }
}
