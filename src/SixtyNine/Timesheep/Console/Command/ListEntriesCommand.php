<?php

namespace SixtyNine\Timesheep\Console\Command;

use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use SixtyNine\Timesheep\Config;
use SixtyNine\Timesheep\Console\Style\MyStyle;
use SixtyNine\Timesheep\Model\Period;
use SixtyNine\Timesheep\Service\StatisticsService;
use SixtyNine\Timesheep\Storage\Entity\Entry;
use SixtyNine\Timesheep\Storage\Repository\EntryRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class ListEntriesCommand extends Command implements ContainerAwareInterface
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
        /** @var Config $config */
        $config = $this->container->get('config');

        $statsService = new StatisticsService($em);

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
        }

        $entries = $repo->getAllEntries($period);
        $stats = $statsService->getProjectStats($period);

        $headers = ['Day', 'From', 'To', 'Duration', 'Project', 'Task', 'Description'];
        $padding = strlen(' Duration ') - 2;

        $io->writeln([
            sprintf('From: <info>%s</info>', $period->getStartFormatted('Y-m-d')),
            sprintf('To: <info>%s</info>', $period->getEndFormatted('Y-m-d')),
            '',
        ]);

        $io->table(
            $headers,
            $this->prepareEntries($entries, $padding),
            $config->get('console.box-style')
        );

        $io->writeln([
            sprintf('Total: <info>%sh</info>', $stats->getTotalString()),
            sprintf('Decimal: <info>%sh</info>', $stats->getTotal()),
            '',
        ]);
    }

    protected function prepareEntries(array $entries, int $padding = 0): array
    {
        $lastDate = null;

        return array_map(static function (Entry $entry) use (&$lastDate, $padding) {
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
    }
}
