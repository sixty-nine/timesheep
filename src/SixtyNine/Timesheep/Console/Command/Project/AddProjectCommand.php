<?php

namespace SixtyNine\Timesheep\Console\Command\Project;

use SixtyNine\Timesheep\Console\TimesheepCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddProjectCommand extends TimesheepCommand
{
    protected static $defaultName = 'proj:add';

    protected function configure(): void
    {
        $this
            ->setDescription('Create a new project.')
            ->setAliases(['p:add'])
            ->addArgument('name', InputArgument::OPTIONAL, 'The new project name.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');

        $io->title('Create a new project');

        if (!$name) {
            $name = $io->ask('New project name');
        }

        if (!$name) {
            $io->writeln('Aborted.');
            return 1;
        }

        if ($this->projectRepo->exists($name)) {
            $io->error(sprintf('The project "%s" already exists', $name));
            return 1;
        }

        $proj = $this->projectRepo->create($name);

        $io->writeln(sprintf(
            'Project <info>%s</info> created (id = <info>%s</info>)',
            $proj->getName(),
            $proj->getId()
        ));

        return 0;
    }
}
