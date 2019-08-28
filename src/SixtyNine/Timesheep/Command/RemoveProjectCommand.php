<?php

namespace SixtyNine\Timesheep\Command;

use Doctrine\ORM\EntityManager;
use SixtyNine\Timesheep\Storage\Entity\Project;
use SixtyNine\Timesheep\Storage\Repository\ProjectRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class RemoveProjectCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected static $defaultName = 'proj:remove';

    protected function configure()
    {
        $this
            ->setDescription('Remove an existing project.')
            ->setAliases(['proj:rm'])
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
            return null;
        }

        if (!$repo->exists($name)) {
            $io->error(sprintf('The project "%s" does exists', $name));
            return null;
        }

        if (!$force && !$io->confirm(sprintf('Are you sure you want to remove the project "%s"', $name), false)) {
            $io->writeln('Aborted');
            return null;
        }

        $repo->delete($name);

        $io->writeln(sprintf('Project <info>%s</info> removed.', $name));
    }
}
