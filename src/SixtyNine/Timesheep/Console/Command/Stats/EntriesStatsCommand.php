<?php

namespace SixtyNine\Timesheep\Console\Command\Stats;

use SixtyNine\Timesheep\Console\Style\MyStyle;
use SixtyNine\Timesheep\Console\TimesheepCommand;
use SixtyNine\Timesheep\Model\DataTable\Builder\EntriesDataTableBuilder;
use SixtyNine\Timesheep\Service\StatisticsService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EntriesStatsCommand extends TimesheepCommand
{
    protected static $defaultName = 'stats:entries';

    protected function configure(): void
    {
        $this
            ->setDescription(
                'Entries statistics for a given period. Same as <comment>entry:list</comment> with no switch.'
            )
            ->addOption('from', null, InputOption::VALUE_OPTIONAL, 'From datetime')
            ->addOption('to', null, InputOption::VALUE_OPTIONAL, 'To datetime')
            ->addOption('week', null, InputOption::VALUE_NONE, 'Whole week')
            ->addOption('month', null, InputOption::VALUE_NONE, 'Whole month')
            ->addOption('day', null, InputOption::VALUE_NONE, 'Whole day')
            ->addOption('csv', null, InputOption::VALUE_NONE, 'Output as CSV')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new MyStyle($input, $output);

        $dateFormat = $this->config->get('date_format');
        $timeFormat = $this->config->get('time_format');

        $statsService = new StatisticsService($this->em);

        $period = $this->getPeriodFromParams($input);
        $csvOutput = $input->getOption('csv');

        $entries = $this->entriesRepo->getAllEntries($period);
        $stats = $statsService->getProjectStats($period);

        $table = EntriesDataTableBuilder::build($entries, $dateFormat, $timeFormat, !$csvOutput);

        if ($csvOutput) {
            $io->outputCsv($table, ';');
        } else {
            $io->title('Entries');
            $io->outputPeriod($period, $dateFormat);
            $io->outputTable($table, $this->config->get('box_style'));
            $io->outputSummary($stats);
        }

        return 0;
    }
}
