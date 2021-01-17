<?php

namespace SixtyNine\Timesheep\Console\Command;

use SixtyNine\Timesheep\Console\TimesheepCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class EditEntryCommand extends TimesheepCommand
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
