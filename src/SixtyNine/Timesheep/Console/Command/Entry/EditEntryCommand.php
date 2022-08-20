<?php

namespace SixtyNine\Timesheep\Console\Command\Entry;

use DateTimeImmutable;
use SixtyNine\Timesheep\Console\TimesheepCommand;
use SixtyNine\Timesheep\Model\Period;
use SixtyNine\Timesheep\Storage\Entity\Entry;
use SixtyNine\Timesheep\Storage\Entity\Project;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class EditEntryCommand extends TimesheepCommand
{
    protected static $defaultName = 'entry:edit';

    protected function configure(): void
    {
        $this
            ->setDescription('Edit an entry.')
            ->setAliases(['e:edit', 'edit'])
            ->addOption('date', null, InputOption::VALUE_OPTIONAL, 'Date inside the entry to delete')
            ->addArgument('start', InputArgument::OPTIONAL, 'Start date/time.')
            ->addArgument('end', InputArgument::OPTIONAL, 'End date/time.')
            ->addArgument('project', InputArgument::OPTIONAL, 'Project code.')
            ->addArgument('task', InputArgument::OPTIONAL, 'Task, i.e. JIRA ticket or such.')
            ->addArgument('description', InputArgument::OPTIONAL, 'Optional description')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the edit.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dateTimeFormat = $this->config->get('datetime_format');
        $force = $input->getOption('force');
        $fromStr = $input->getOption('date');
        $project = $input->getArgument('project');
        $task = $input->getArgument('task');
        $description = $input->getArgument('description');
        $start = $input->getArgument('start');
        $end = $input->getArgument('end');

        $periodStart = $fromStr ? new DateTimeImmutable($fromStr) : new DateTimeImmutable();
        $period = new Period($periodStart, $periodStart);
        $entries = $this->entriesRepo->findCrossingEntries($period);

        $io->title('Edit an entry');

        if (count($entries) === 0) {
            $io->writeln('<question>No entries found for this date</question>');
            $io->newLine();
            return 0;
        }

        if (count($entries) > 1) {
            $io->error('Multiple entries found for this date');
            $io->newLine();
            return 0;
        }

        /** @var Entry $entry */
        $entry = reset($entries);

        if (!$force && !$start) {
            $start = $io->ask('Start time');
        }

        if (!$start) {
            $start = (new DateTimeImmutable())->format($dateTimeFormat);
        }

        if (!$force && !$end) {
            $end = $io->ask('End time');
        }

        if (!$end) {
            $end = (new DateTimeImmutable())->format($dateTimeFormat);
        }

        if (!$force && !$project) {
            $project = $this->inputProject($input, $output, $this->em);
        }

        if (!$force && !$task) {
            $task = $io->ask('Task');
        }

        if (!$force && !$description) {
            $task = $io->ask('Description');
        }

        $project = Project::normalizeName($project);
        $period = Period::fromString($start, $end);

        $io->writeln('<comment>Original:</comment>');
        $io->writeln([
            sprintf('  Start: <info>%s</info>', $entry->getStartFormatted($dateTimeFormat)),
            sprintf('  End: <info>%s</info>', $entry->getEndFormatted($dateTimeFormat)),
            sprintf('  Duration: <info>%s</info>', $entry->getPeriod()->getDurationString()),
            sprintf('  Decimal: <info>%sh</info>', $entry->getPeriod()->getDuration()),
            sprintf('  Project: <info>%s</info>', $entry->getProject()),
            sprintf('  Task: <info>%s</info>', $entry->getTask()),
            sprintf('  Description: <info>%s</info>', $entry->getDescription()),
        ]);

        $io->newLine();
        $io->writeln('<comment>New:</comment>');
        $io->writeln([
            sprintf('  Start: <info>%s</info>', $period->getStartFormatted($dateTimeFormat)),
            sprintf('  End: <info>%s</info>', $period->getEndFormatted($dateTimeFormat)),
            sprintf('  Duration: <info>%s</info>', $period->getDurationString()),
            sprintf('  Decimal: <info>%s</info>', $period->getDuration()),
            sprintf('  Project: <info>%s</info>', $project),
            sprintf('  Task: <info>%s</info>', $task),
            sprintf('  Description: <info>%s</info>', $description),
        ]);

        $crossingEntry = $this->entriesRepo->checkNoCrossingEntries($period);
        if (!is_bool($crossingEntry) && $crossingEntry->getId() !== $entry->getId()) {
            $io->error(['There is another entry crossing the edited entry', $entry]);
            return 1;
        }

        if (!$io->confirm('Do you want to edit the entry?', false)) {
            $io->error('Aborted.');
            return 1;
        }

        $this->entriesRepo->editEntry(
            $entry->getId(),
            $period,
            $project ?? '',
            $task ?? '',
            $description ?? ''
        );

        $io->writeln('<question>Done.</question>');
        $io->newLine();

        return 0;
    }
}
