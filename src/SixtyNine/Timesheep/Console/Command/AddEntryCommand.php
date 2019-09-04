<?php

namespace SixtyNine\Timesheep\Console\Command;

use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use SixtyNine\Timesheep\Model\Period;
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

        $period = $this->inputTime($input, $io);

        $io->writeln([
            sprintf('Start: <info>%s</info>', $period->getStartFormatted('Y-m-d H:i:s')),
            sprintf('End: <info>%s</info>', $period->getEndFormatted('Y-m-d H:i:s')),
            sprintf('Duration: <info>%s</info>', $period->getDurationString()),
            sprintf('Decimal: <info>%s</info>', $period->getDuration()),
            sprintf('Project: <info>%s</info>', $project),
            sprintf('Task: <info>%s</info>', $task),
        ]);

        $crossingEntries = $repo->findCrossingEntries($period);
        if (0 < count($crossingEntries)) {
            /** @var Entry $firstEntry */
            $firstEntry = $crossingEntries[0];
            $io->error([
                'There is another entry crossing this one',
                sprintf(
                    'id=%s, %s-%s %s %s',
                    $firstEntry->getId(),
                    $firstEntry->getStart()->format('H:i'),
                    null !== $firstEntry->getEnd() ? $firstEntry->getEnd()->format('H:i') : '-',
                    $firstEntry->getProject(),
                    $firstEntry->getTask()
                )
            ]);
            return 1;
        }

        if (!$force && !$io->confirm('Is it correct?', false)) {
            $io->writeln('Aborted.');
            return 1;
        }

        $repo->create($period, $project ?? '', $task ?? '');
        $io->writeln('Entry created');
    }

    protected function inputTime(InputInterface $input, SymfonyStyle $io): Period
    {
        $start = $input->getArgument('start');
        $end = $input->getArgument('end');

        if (!$start) {
            $start = $io->ask('Start time');
        }
        if (!$end) {
            $end = $io->ask('End time');
        }

        return Period::fromSTring($start, $end);
    }
}
