<?php

namespace SixtyNine\Timesheep\Console\Command;

use Doctrine\ORM\EntityManager;
use SixtyNine\Timesheep\Storage\Entity\Project;
use SixtyNine\Timesheep\Storage\Repository\ProjectRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class EditEntryCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected static $defaultName = 'entry:edit';

    protected function configure()
    {
        $this
            ->setDescription('Edit an entry.')
            ->setAliases(['e:edit', 'edit']);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        throw new \Exception('Not implemented');
    }
}
