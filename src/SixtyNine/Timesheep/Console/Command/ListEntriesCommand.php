<?php

namespace SixtyNine\Timesheep\Console\Command;

use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use SixtyNine\Timesheep\Config;
use SixtyNine\Timesheep\Console\Style\MyStyle;
use SixtyNine\Timesheep\Console\TimesheepCommand;
use SixtyNine\Timesheep\Helper\DateTimeHelper;
use SixtyNine\Timesheep\Model\DataTable\Builder\EntriesDataTableBuilder;
use SixtyNine\Timesheep\Model\DataTable\Builder\PresenceDataTableBuilder;
use SixtyNine\Timesheep\Model\DataTable\Builder\StatsDataTableBuilder;
use SixtyNine\Timesheep\Model\DataTable\SymfonyConsoleDataTable;
use SixtyNine\Timesheep\Model\Period;
use SixtyNine\Timesheep\Service\StatisticsService;
use SixtyNine\Timesheep\Storage\Entity\Entry;
use SixtyNine\Timesheep\Storage\Repository\EntryRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class ListEntriesCommand extends TimesheepCommand
{
    use ContainerAwareTrait;

    protected static $defaultName = 'entry:list';

    protected function configure()
    {
        $this
            ->setDescription('List all the entries.')
            ->setAliases(['entry:ls', 'e:ls', 'ls'])
            ->addOption('from', null, InputOption::VALUE_OPTIONAL, 'From datetime')
            ->addOption('to', null, InputOption::VALUE_OPTIONAL, 'To datetime')
            ->addOption('week', null, InputOption::VALUE_NONE, 'This week')
            ->addOption('month', null, InputOption::VALUE_NONE, 'This week')
            ->addOption('day', null, InputOption::VALUE_NONE, 'This day')
            ->addoption('stats', null, InputOption::VALUE_NONE, 'Display the project stats')
            ->addoption('presence', null, InputOption::VALUE_NONE, 'Display presence time')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new MyStyle($input, $output);
        $io->title('Entries');
        /** @var EntityManager $em */
        $em = $this->container->get('em');
        /** @var EntryRepository $repo */
        $repo = $em->getRepository(Entry::class);
        /** @var DateTimeHelper $dtHelper */
        $dtHelper = $this->container->get('datetime-helper');
        /** @var Config $config */
        $config = $this->container->get('config');

        $dateFormat = $config->get('format.date');
        $timeFormat = $config->get('format.time');

        $statsService = new StatisticsService($em);

        // --- Process parameters

        $displayStats = $input->getOption('stats');
        $displayPresence = $input->getOption('presence');

        if ($displayStats && $displayPresence) {
            throw new \InvalidArgumentException(
                'The --stats and --presence switches cannot be used together'
            );
        }

        /** @var string $fromStr */
        $fromStr = $input->getOption('from');
        /** @var string $toStr */
        $toStr = $input->getOption('to');
        $period = new Period(
            $fromStr ? new DateTimeImmutable($fromStr) : null,
            $toStr ? new DateTimeImmutable($toStr) : null
        );

        if ($input->getOption('week')) {
            $period = Period::getWeek($period->getFirstDateOrToday());
        } elseif ($input->getOption('month')) {
            $period = Period::getMonth($period->getFirstDateOrToday());
        } elseif ($input->getOption('day')) {
            $day = $period->getFirstDateOrToday();
            $period = new Period($day, $day);
        }

        // --- Gather data

        $entries = $repo->getAllEntries($period);
        $stats = $statsService->getProjectStats($period);

        if ($displayStats) {
            $table = StatsDataTableBuilder::build($stats, $dtHelper);
        } elseif ($displayPresence) {
            $table = SymfonyConsoleDataTable::fromDataTable(
                PresenceDataTableBuilder::build($entries, $dateFormat, $timeFormat)
            );
        } else {
            $table = EntriesDataTableBuilder::build($entries, $dateFormat, $timeFormat);
        }

        // --- Output results

        $io->writeln([
            sprintf('From: <info>%s</info>', $period->getStartFormatted($dateFormat)),
            sprintf('To: <info>%s</info>', $period->getEndFormatted($dateFormat)),
            '',
        ]);

        $io->table(
            $table->getHeaders(),
            $table->getRows(),
            $config->get('console.box-style')
        );

        $total = $stats->getTotal();
        $io->writeln([
            sprintf('Total: <info>%sh</info>', $dtHelper->decimalToTime($total)),
            sprintf('Decimal: <info>%sh</info>', $total),
            '',
        ]);
    }
}
