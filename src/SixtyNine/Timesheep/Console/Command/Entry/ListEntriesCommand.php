<?php

namespace SixtyNine\Timesheep\Console\Command\Entry;

use InvalidArgumentException;
use SixtyNine\Timesheep\Console\Style\MyStyle;
use SixtyNine\Timesheep\Console\TimesheepCommand;
use SixtyNine\Timesheep\Model\Calendar;
use SixtyNine\Timesheep\Model\DataTable\Builder\EntriesDataTableBuilder;
use SixtyNine\Timesheep\Model\DataTable\Builder\PresenceDataTableBuilder;
use SixtyNine\Timesheep\Model\DataTable\Builder\StatsDataTableBuilder;
use SixtyNine\Timesheep\Model\DataTable\SymfonyConsoleDataTable;
use SixtyNine\Timesheep\Model\Schedule;
use SixtyNine\Timesheep\Service\StatisticsService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListEntriesCommand extends TimesheepCommand
{
    protected static $defaultName = 'entry:list';

    protected function configure(): void
    {
        $this
            ->setDescription('List all the entries.')
            ->setAliases(['entry:ls', 'e:ls', 'ls'])
            ->addOption('from', null, InputOption::VALUE_OPTIONAL, 'From datetime')
            ->addOption('to', null, InputOption::VALUE_OPTIONAL, 'To datetime')
            ->addOption('week', null, InputOption::VALUE_NONE, 'Whole week')
            ->addOption('month', null, InputOption::VALUE_NONE, 'Whole month')
            ->addOption('day', null, InputOption::VALUE_NONE, 'Whole day')
            ->addOption('stats', null, InputOption::VALUE_NONE, 'Display the project stats')
            ->addOption('presence', null, InputOption::VALUE_NONE, 'Display presence time')
            ->addOption('csv', null, InputOption::VALUE_NONE, 'Output as CSV')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $schedule = new Schedule();
        $io = new MyStyle($input, $output);

        $dateFormat = $this->config->get('format.date');
        $timeFormat = $this->config->get('format.time');

        $statsService = new StatisticsService($this->em);

        // --- Process parameters
        $displayStats = $input->getOption('stats');
        $displayPresence = $input->getOption('presence');
        $csvOutput = $input->getOption('csv');
        $aggregateDate = !$csvOutput;

        if ($displayStats && $displayPresence) {
            throw new InvalidArgumentException(
                'The --stats and --presence switches cannot be used together'
            );
        }

        $period = $this->getPeriodFromParams($input);

        $entries = $this->entriesRepo->getAllEntries($period);
        $stats = $statsService->getProjectStats($period);

        if ($displayStats) {
            $table = StatsDataTableBuilder::build($stats);
        } elseif ($displayPresence) {
            $table = SymfonyConsoleDataTable::fromDataTable(
                PresenceDataTableBuilder::build($entries, $dateFormat, $timeFormat, $aggregateDate)
            );
        } else {
            $table = EntriesDataTableBuilder::build($entries, $dateFormat, $timeFormat, $aggregateDate);
        }

        if ($csvOutput) {
            $io->outputCsv($table, ';');
        } else {
            $io->title('Entries');
            $io->outputPeriod($period, $dateFormat);
            $io->outputTable($table, $this->config->get('console.box-style'));
            $io->outputSummary($stats);
            $io->newLine();
            $io->writeln(sprintf('Due per week: %s', $schedule->dueHoursPerWeek()));
            $io->writeln(sprintf(
                'Due this month: %s',
                $schedule->dueHoursPerMonth($period->getFirstDateOrToday())
            ));
        }

        return 0;
    }
}
