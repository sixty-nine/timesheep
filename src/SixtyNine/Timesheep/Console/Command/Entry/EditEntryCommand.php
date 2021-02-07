<?php

namespace SixtyNine\Timesheep\Console\Command\Entry;

use SixtyNine\Timesheep\Console\TimesheepCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EditEntryCommand extends TimesheepCommand
{
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
