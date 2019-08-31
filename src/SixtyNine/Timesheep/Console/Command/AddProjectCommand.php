<?php

namespace SixtyNine\Timesheep\Console\Command;

use Doctrine\ORM\EntityManager;
use SixtyNine\Timesheep\Storage\Entity\Project;
use SixtyNine\Timesheep\Storage\Repository\ProjectRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class AddProjectCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected static $defaultName = 'proj:add';

    protected function configure()
    {
        $this
            ->setDescription('Create a new project.')
            ->setAliases(['p:add'])
            ->addArgument('name', InputArgument::OPTIONAL, 'The new project name.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        /** @var EntityManager $em */
        $em = $this->container->get('em');
        /** @var ProjectRepository $repo */
        $repo = $em->getRepository(Project::class);
        $name = $input->getArgument('name');

        $io->title('Create a new project');

        if (!$name) {
            $name = $io->ask('New project name');
        }

        if (!$name) {
            $io->writeln('Aborted.');
            return null;
        }

        if ($repo->exists($name)) {
            $io->error(sprintf('The project "%s" already exists', $name));
            return null;
        }

        $proj = $repo->create($name);

        $io->writeln(sprintf(
            'Project <info>%s</info> created (id = <info>%s</info>)',
            $proj->getName(),
            $proj->getId()
        ));
    }
}
