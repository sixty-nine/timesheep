<?php

namespace SixtyNine\Timesheep\Console\Command;

use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use SixtyNine\Timesheep\Storage\Entity\Entry;
use SixtyNine\Timesheep\Storage\Repository\EntryRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class AddEntryCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected static $defaultName = 'entry:add';

    protected function configure()
    {
        $this
            ->setDescription('Add a new entry.')
            ->setAliases(['e:add', 'add'])
            ->addArgument('start', InputArgument::OPTIONAL, 'Start date/time.')
            ->addArgument('end', InputArgument::OPTIONAL, 'End date/time.')
            ->addArgument('project', InputArgument::OPTIONAL, 'Project code.')
            ->addArgument('task', InputArgument::OPTIONAL, 'Task, i.e. JIRA ticket or such.')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the removal.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        /** @var EntityManager $em */
        $em = $this->container->get('em');
        /** @var EntryRepository $repo */
        $repo = $em->getRepository(Entry::class);

        /** @var string $force */
        $force = $input->getOption('force');
        /** @var string $project */
        $project = $input->getArgument('project');
        /** @var string $task */
        $task = $input->getArgument('task');

        $io = new SymfonyStyle($input, $output);
        $io->title('Add a new entry');

        if (!$startEnd = $this->inputTime($input, $io)) {
            return null;
        }

        [$start, $end] = $startEnd;
        $diffs = $this->calculateDiffs($start, $end);
        $startDate = DateTimeImmutable::createFromMutable($diffs['start']);
        $endDate = DateTimeImmutable::createFromMutable($diffs['end']);

        $io->writeln([
            sprintf('Start: <info>%s</info>', $diffs['start-formatted']),
            sprintf('End: <info>%s</info>', $diffs['end-formatted']),
            sprintf('Duration: <info>%s</info>', $diffs['duration']),
            sprintf('Decimal: <info>%s</info>', $diffs['duration-decimal']),
            sprintf('Project: <info>%s</info>', $project),
            sprintf('Task: <info>%s</info>', $task),
        ]);

        $crossingEntries = $repo->findCrossingEntries($startDate, $endDate);
        if (0 < count($crossingEntries)) {
            /** @var Entry $firstEntry */
            $firstEntry = $crossingEntries[0];
            $io->error([
                'There is another entry crossing this one',
                sprintf(
                    'id=%s, %s-%s %s %s',
                    $firstEntry->getId(),
                    $firstEntry->getStart()->format('H:i'),
                    $firstEntry->getEnd()->format('H:i'),
                    $firstEntry->getProject(),
                    $firstEntry->getTask()
                )
            ]);
            return null;
        }

        if (!$force && !$io->confirm('Is it correct?', false)) {
            $io->writeln('Aborted.');
            return null;
        }

        $repo->create($startDate, $endDate, $project ?? '', $task ?? '');
        $io->writeln('Entry created');
    }

    protected function inputTime(InputInterface $input, SymfonyStyle $io)
    {
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
            return false;
        }

        if (!$end) {
            $io->error('Invalid end time');
            return false;
        }

        return [$start, $end];
    }

    public function dateTimeFromTs(int $timestamp): DateTime
    {
        return (new DateTime())->setTimestamp($timestamp);
    }

    public function calculateDiffs(int $timestamp1, int $timestamp2)
    {
        $startDate = $this->dateTimeFromTs($timestamp1);
        $endDate = $this->dateTimeFromTs($timestamp2);

        if ($endDate < $startDate) {
            $endDate->modify('+1 day');
        }

        $diff = $endDate->diff($startDate);

        return [
            'start' => $startDate,
            'end' => $endDate,
            'diff' => $diff,
            'start-formatted' => $startDate->format('Y-m-d H:i:s'),
            'end-formatted' => $endDate->format('Y-m-d H:i:s'),
            'duration' => $diff->format('%H:%I'),
            'duration-decimal' => $diff->h + round($diff->i / 60, 2),
        ];
    }
}
