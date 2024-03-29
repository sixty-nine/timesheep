<?php

namespace SixtyNine\Timesheep\Console\Command\Entry;

use SixtyNine\Timesheep\Console\TimesheepCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddEntryCommand extends TimesheepCommand
{
    protected static $defaultName = 'entry:add';

    protected function configure(): void
    {
        $this
            ->setDescription('Add a new entry.')
            ->setAliases(['e:add', 'add'])
            ->addArgument('start', InputArgument::OPTIONAL, 'Start date/time.')
            ->addArgument('end', InputArgument::OPTIONAL, 'End date/time.')
            ->addArgument('project', InputArgument::OPTIONAL, 'Project code.')
            ->addArgument('task', InputArgument::OPTIONAL, 'Task, i.e. JIRA ticket or such.')
            ->addArgument('description', InputArgument::OPTIONAL, 'Optional description')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the creation.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $format = $this->config->get('datetime_format');

        /** @var string $force */
        $force = $input->getOption('force');
        /** @var string $project */
        $project = $input->getArgument('project');
        /** @var string $task */
        $task = $input->getArgument('task');
        /** @var string $description */
        $description = $input->getArgument('description');

        $io = new SymfonyStyle($input, $output);
        $io->title('Add a new entry');

        $period = $this->inputTime($input, $io);

        if (!$force && !$project) {
            $project = $this->inputProject($input, $output, $this->em);
        }

        if (!$force && !$task) {
            $task = $io->ask('Task');
        }

        if (!$force && !$description) {
            $task = $io->ask('Description');
        }

        $io->writeln([
            sprintf('Start: <info>%s</info>', $period->getStartFormatted($format)),
            sprintf('End: <info>%s</info>', $period->getEndFormatted($format)),
            sprintf('Duration: <info>%s</info>', $period->getDurationString()),
            sprintf('Decimal: <info>%s</info>', $period->getDuration()),
            sprintf('Project: <info>%s</info>', $project),
            sprintf('Task: <info>%s</info>', $task),
            sprintf('Description: <info>%s</info>', $description),
        ]);

        if ($entry = $this->entriesRepo->checkNoCrossingEntries($period)) {
            $io->error(['There is another entry crossing this one', $entry]);
            return 1;
        }

        if (!$force && !$io->confirm('Is it correct?', false)) {
            $io->writeln('Aborted.');
            return 1;
        }

        $this->entriesRepo->create($period, $project ?? '', $task ?? '', $description ?? '');

        $io->writeln('Entry created');

        return 0;
    }
}
