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

    protected function configure(): void
    {
        $this
            ->setDescription('List all the projects.')
            ->setAliases(['proj:ls', 'p:ls']);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new MyStyle($input, $output);
        $io->title('Projects');

        $projects = $this->projectRepo->findAll();
        $repo = $this->entriesRepo;
        $format = $this->config->get('date_format');

        // Add last usage to every project
        $projects = array_map(static function (array $p) use ($repo, $format) {
            $lastUsage = $repo->getLastProjectUsage($p['name']);
            $p['last_used'] = $lastUsage ? $lastUsage->format($format) : null;
            return $p;
        }, $projects);

        // Output
        $headers = ['ID', 'Name', 'Description', 'Last usage'];
        $table = new DataTable($headers, $projects);

        $io->outputTable($table, $this->config->get('box_style'));

        return 0;
    }
}
