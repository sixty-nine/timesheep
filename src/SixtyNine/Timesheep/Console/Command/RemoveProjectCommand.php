<?php

namespace SixtyNine\Timesheep\Console\Command;

use Doctrine\ORM\EntityManager;
use SixtyNine\Timesheep\Console\TimesheepCommand;
use SixtyNine\Timesheep\Storage\Entity\Project;
use SixtyNine\Timesheep\Storage\Repository\ProjectRepository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class RemoveProjectCommand extends TimesheepCommand
{
    use ContainerAwareTrait;

    protected static $defaultName = 'proj:remove';

    protected function configure()
    {
        $this
            ->setDescription('Remove an existing project.')
            ->setAliases(['proj:rm', 'p:rm'])
            ->addArgument('name', InputArgument::OPTIONAL, 'The project name.')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the removal.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        /**
 * @var EntityManager $em
*/
        $em = $this->container->get('em');
        /**
 * @var ProjectRepository $repo
*/
        $repo = $em->getRepository(Project::class);
        $name = $input->getArgument('name');
        $force = (bool)$input->getOption('force');

        $io->title('Remove a project');

        if (!$name) {
            $name = $io->ask('Name of the project to remove');
        }

        if (!$name) {
            $io->writeln('Aborted.');
            return 1;
        }

        if (!$repo->exists($name)) {
            $io->error(sprintf('The project "%s" does exists', $name));
            return 1;
        }

        if (!$force && !$io->confirm(sprintf('Are you sure you want to remove the project "%s"', $name), false)) {
            $io->writeln('Aborted');
            return 1;
        }

        $repo->delete($name);

        $io->writeln(sprintf('Project <info>%s</info> removed.', $name));
    }
}
