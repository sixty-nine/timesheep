<?php

namespace SixtyNine\Timesheep\Console\Command;

use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use SixtyNine\Timesheep\Config;
use SixtyNine\Timesheep\Console\Style\MyStyle;
use SixtyNine\Timesheep\Console\TimesheepCommand;
use SixtyNine\Timesheep\Helper\DateTimeHelper;
use SixtyNine\Timesheep\Model\Period;
use SixtyNine\Timesheep\Model\ProjectStatistics;
use SixtyNine\Timesheep\Model\TimeBlocks;
use SixtyNine\Timesheep\Service\StatisticsService;
use SixtyNine\Timesheep\Storage\Entity\Entry;
use SixtyNine\Timesheep\Storage\Repository\EntryRepository;
use Symfony\Component\Console\Helper\TableSeparator;
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

        $statsService = new StatisticsService($em);

        $displayStats = $input->getOption('stats');
        $displayPresence = $input->getOption('presence');

        if ($displayStats && $displayPresence) {
            throw new \InvalidArgumentException(
                'The --stats and --presence switches cannot be used together'
            );
        };

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

        $entries = $repo->getAllEntries($period);
        $stats = $statsService->getProjectStats($period);

        $io->writeln([
            sprintf('From: <info>%s</info>', $period->getStartFormatted('Y-m-d')),
            sprintf('To: <info>%s</info>', $period->getEndFormatted('Y-m-d')),
            '',
        ]);

        if ($displayStats) {
            $this->displayStats($io, $stats, $config, $dtHelper);
        } elseif ($displayPresence) {
            $this->displayPresence($io, $entries, $config, $dtHelper);
        } else {
            $this->displayEntries($io, $entries, $config);
        }

        $total = $stats->getTotal();
        $io->writeln([
            sprintf('Total: <info>%sh</info>', $dtHelper->decimalToTime($total)),
            sprintf('Decimal: <info>%sh</info>', $total),
            '',
        ]);
    }

    protected function displayStats(
        MyStyle $io,
        ProjectStatistics $stats,
        Config $config,
        DateTimeHelper $dtHelper
    ): void {
        $rows = [];
        foreach ($stats->getProjectsHours() as $project => $hours) {
            $rows[] = [$project, sprintf('%sh', $hours), $dtHelper->decimalToTime($hours)];
        }
        $io->table(
            ['Project', 'Duration', ''],
            $rows,
            $config->get('console.box-style')
        );
    }

    protected function displayPresence(MyStyle $io, array $entries, Config $config, DateTimeHelper $dtHelper): void
    {
        $headers = ['Date', 'Start', 'End', 'Duration'];

        $list = (static function () use ($entries) {

            $blocks = new TimeBlocks();
            /** @var Entry $entry */
            foreach ($entries as $entry) {
                $blocks->addPeriod($entry->getPeriod());
            }

            $lastDate = null;
            $arr = [];
            /** @var Period $p */
            foreach ($blocks->getPeriods() as $p) {
                $date = $p->getStartFormatted('Y-m-d');

                if ($lastDate !== $date) {
                    if ($lastDate) {
                        $arr[] = new TableSeparator();
                    }
                    $lastDate = $date;
                }

                $arr[] = [
                    $p->getStartFormatted('Y-m-d'),
                    (null !== $p->getStart()) ? $p->getStart()->format('H:i') : '-',
                    (null !== $p->getEnd()) ? $p->getEnd()->format('H:i') : '-',
                    $p->getDurationString(),
                    $p->getDuration().' h',
                ];
            }
            return $arr;
        })();

        $io->table($headers, $list, $config->get('console.box-style'));
    }

    protected function displayEntries(MyStyle $io, $entries, Config $config): void
    {
        $headers = ['Day', 'From', 'To', 'Duration', 'Project', 'Task', 'Description'];
        $padding = strlen(' Duration ') - 2;
        $lastDate = null;
        $list = array_map(static function (Entry $entry) use (&$lastDate, $padding) {
            $entryDate = $entry->getStartFormatted('Y-m-d');
            $date = $lastDate !== $entryDate ? $entryDate : '';
            $lastDate = $entryDate;
            return [
                $date,
                $entry->getStart()->format('H:i'),
                $entry->getEndFormatted('H:i'),
                str_pad($entry->getPeriod()->getDurationString(), $padding, ' ', STR_PAD_LEFT),
                $entry->getProject(),
                $entry->getTask(),
                $entry->getDescription(),
            ];
        }, $entries);

        $io->table($headers, $list, $config->get('console.box-style'));
    }
}
