<?php

namespace SixtyNine\Timesheep\Console\Command\Entry;

use SixtyNine\Timesheep\Console\TimesheepCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteEntryCommand extends TimesheepCommand
{
    protected static $defaultName = 'entry:delete';

    protected function configure(): void
    {
        $this
            ->setDescription('Removes an entry.')
            ->setAliases(['e:rm', 'rm']);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        throw new \Exception('Not implemented');
    }
}
