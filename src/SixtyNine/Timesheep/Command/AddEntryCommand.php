<?php

namespace SixtyNine\Timesheep\Command;

use DateTime;
use SixtyNine\Timesheep\Storage\Entity\Project;
use SixtyNine\Timesheep\Storage\Repository\ProjectRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class AddEntryCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected static $defaultName = 'entry:create';

    protected function configure()
    {
        $this
            ->setDescription('Add a new entry.')
            ->setAliases(['entry:add'])
            ->addArgument('start', InputArgument::OPTIONAL, 'Start date/time.')
            ->addArgument('end', InputArgument::OPTIONAL, 'End date/time.')
            ->addArgument('project', InputArgument::OPTIONAL, 'Project code.')
            ->addArgument('description', InputArgument::OPTIONAL, 'Description.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Add a new entry');

        $start = $input->getArgument('start');
        $end = $input->getArgument('end');

        if (!$start) {
            $start = $io->ask('Start time');
        }
        if (!$end) {
            $end = $io->ask('End time');
        }

        $start = strtotime($start);
        $end = strtotime($end);

        if (!$start) {
            $io->error('Invalid start time');
            return null;
        }

        if (!$end) {
            $io->error('Invalid end time');
            return null;
        }

        $startDate = $this->dateTimeFromTs($start);
        $endDate = $this->dateTimeFromTs($end);

        var_dump(
            $endDate->diff($startDate)->format('%s')
        );

        $io->writeln(
            [
            'start',
            $startDate->format('Y-m-d H:i:s'),
            'end',
            $endDate->format('Y-m-d H:i:s'),
            'diff',
            $endDate->diff($startDate, true)->format('%i')
            ]
        );
    }

    public function dateTimeFromTs(int $timestamp): DateTime
    {
        return (new DateTime())->setTimestamp($timestamp);
    }
}
