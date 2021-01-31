<?php

namespace SixtyNine\Timesheep\Console\Command\Project;

use Doctrine\ORM\EntityManager;
use SixtyNine\Timesheep\Config;
use SixtyNine\Timesheep\Console\Style\MyStyle;
use SixtyNine\Timesheep\Console\TimesheepCommand;
use SixtyNine\Timesheep\Storage\Entity\Project;
use SixtyNine\Timesheep\Storage\Repository\ProjectRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class ListProjectsCommand extends TimesheepCommand
{
    use ContainerAwareTrait;

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
        /** @var EntityManager $em */
        $em = $this->container->get('em');
        /** @var ProjectRepository $repo */
        $repo = $em->getRepository(Project::class);
        /** @var Config $config */
        $config = $this->container->get('config');

        $io->table(
            ['ID', 'Name', 'Description'],
            $repo->findAll(),
            $config->get('console.box-style')
        );
    }
}
