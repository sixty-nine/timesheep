<?php

namespace SixtyNine\Timesheep\Console\Command;

use SixtyNine\Timesheep\Console\TimesheepCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class DeleteEntryCommand extends TimesheepCommand
{
    use ContainerAwareTrait;

    protected static $defaultName = 'entry:delete';

    protected function configure()
    {
        $this
            ->setDescription('Removes an entry.')
            ->setAliases(['e:rm', 'rm']);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        throw new \Exception('Not implemented');
    }
}
