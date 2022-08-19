<?php

namespace SixtyNine\Timesheep\Console\Command\Stats;

use SixtyNine\Timesheep\Console\Style\MyStyle;
use SixtyNine\Timesheep\Console\TimesheepCommand;
use SixtyNine\Timesheep\Model\DataTable\Builder\PresenceDataTableBuilder;
use SixtyNine\Timesheep\Model\DataTable\SymfonyConsoleDataTable;
use SixtyNine\Timesheep\Model\DateStrings;
use SixtyNine\Timesheep\Service\StatisticsService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PresenceStatsCommand extends TimesheepCommand
{
    protected static $defaultName = 'stats:presence';

    protected function configure(): void
    {
        $this
            ->setDescription(
                'Presence statistics for a given period. Same as <comment>entry:list --presence</comment>.'
            )
            ->addOption('from', null, InputOption::VALUE_OPTIONAL, 'From datetime')
            ->addOption('to', null, InputOption::VALUE_OPTIONAL, 'To datetime')
            ->addOption('week', null, InputOption::VALUE_NONE, 'Whole week')
            ->addOption('month', null, InputOption::VALUE_NONE, 'Whole month')
            ->addOption('day', null, InputOption::VALUE_NONE, 'Whole day')
            ->addOption('split-at', null, InputOption::VALUE_OPTIONAL, 'Split presence at this time')
            ->addOption(
                'split-for',
                null,
                InputOption::VALUE_OPTIONAL,
                'Split presence for this duration in minutes',
                30
            )
            ->addOption('csv', null, InputOption::VALUE_NONE, 'Output as CSV')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new MyStyle($input, $output);
        $dateFormat = $this->config->get('date_format');
        $timeFormat = $this->config->get('time_format');
        $ds = new DateStrings();

        $statsService = new StatisticsService($this->em);

        $period = $this->getPeriodFromParams($input);
        $csvOutput = $input->getOption('csv');

        /** @var ?string $splitAt */
        $splitAt = $input->getOption('split-at');
        /** @var int $splitFor */
        $splitFor = $input->getOption('split-for');

        if ($splitAt && !$ds->isValidTime($splitAt)) {
            $io->error(['The split-at value must be a valid time in the format H:i:', $splitAt]);
            return 1;
        }

        // --- Gather data

        $entries = $this->entriesRepo->getAllEntries($period);
        $stats = $statsService->getProjectStats($period);


        $table = SymfonyConsoleDataTable::fromDataTable(
            PresenceDataTableBuilder::build(
                $entries,
                $dateFormat,
                $timeFormat,
                !$csvOutput,
                $splitAt,
                $splitFor
            )
        );

        if ($csvOutput) {
            $io->outputCsv($table, ';');
        } else {
            $io->title('Presence stats');
            $io->outputPeriod($period, $dateFormat);
            $io->outputTable($table, $this->config->get('box_style'));
            $io->outputSummary($stats);
        }

        return 0;
    }
}
