<?php

namespace SixtyNine\Timesheep\Command;

use Doctrine\ORM\EntityManager;
use SixtyNine\Timesheep\Storage\Entity\Project;
use SixtyNine\Timesheep\Storage\Repository\ProjectRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class ListProjectsCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected static $defaultName = 'proj:list';

    protected function configure()
    {
        $this
            ->setDescription('List all the projects.')
            ->setAliases(['proj:ls']);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Projects');
        /**
 * @var EntityManager $em
*/
        $em = $this->container->get('em');
        /**
 * @var ProjectRepository $repo
*/
        $repo = $em->getRepository(Project::class);

        $io->table(['ID', 'Name'], $repo->findAll());
    }
}
