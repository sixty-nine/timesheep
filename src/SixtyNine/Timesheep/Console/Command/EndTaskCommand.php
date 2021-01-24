<?php

namespace SixtyNine\Timesheep\Console\Command;

use SixtyNine\Timesheep\Console\TimesheepCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class EndTaskCommand extends TimesheepCommand
{
    use ContainerAwareTrait;

    protected static $defaultName = 'task:stop';

    protected function configure()
    {
        $this
            ->setDescription('Finish the current task.')
            ->setAliases(['t:stop', 'stop']);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        throw new \Exception('Not implemented');
    }
}
