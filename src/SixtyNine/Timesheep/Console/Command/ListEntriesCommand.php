<?php

namespace SixtyNine\Timesheep\Console\Command;

use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use SixtyNine\Timesheep\Console\Style\MyStyle;
use SixtyNine\Timesheep\Helper\DatePeriod;
use SixtyNine\Timesheep\Helper\DateTime as DateTimeHelper;
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

        /** @var string $fromStr */
        $fromStr = $input->getOption('from');
        $from = $fromStr ? new DateTimeImmutable($fromStr) : null;
        /** @var string $toStr */
        $toStr = $input->getOption('to');
        $to = $toStr ? new DateTimeImmutable($toStr) : null;

        if ($input->getOption('week')) {
            $p = DatePeriod::getWeek(
                $from ?: (
                    $to ? $to : new DateTimeImmutable()
                )
            );
            /** @var ?DateTimeImmutable $from */
            $from = $p->start;
            /** @var ?DateTimeImmutable $to */
            $to = $p->end;
        } elseif ($input->getOption('month')) {
            $p = DatePeriod::getMonth(
                $from ?: (
                    $to ? $to : new DateTimeImmutable()
                )
            );
            /** @var ?DateTimeImmutable $from */
            $from = $p->start;
            /** @var ?DateTimeImmutable $to */
            $to = $p->end;
        }

        $entries = $repo->getAllEntries($from, $to);

        $headers = ['Day', 'From', 'To', 'Duration', 'Project', 'Task', 'Description'];
        $duration = 0;
        $padding = strlen(' Duration ') - 2;
        $lastDate = null;

        $rows = array_map(static function (Entry $entry) use (&$duration, &$lastDate, $padding) {
            $duration += $entry->getDecimalDuration();
            $entryDate = $entry->getStart()->format('Y-m-d');
            $date = $lastDate !== $entryDate ? $entryDate : '';
            $lastDate = $entryDate;
            return [
                $date,
                $entry->getStart()->format('H:i'),
                $entry->getEnd()->format('H:i'),
                str_pad($entry->getDuration(), $padding, ' ', STR_PAD_LEFT),
                $entry->getProject(),
                $entry->getTask(),
                $entry->getDescription(),
            ];
        }, $entries);

        $io->writeln([
            sprintf('From: <info>%s</info>', $from ? $from->format('Y-m-d') : '-'),
            sprintf('To: <info>%s</info>', $to ? $to->format('Y-m-d') : '-'),
            '',
        ]);
        $io->table($headers, $rows, 'box');

        $io->writeln([
            sprintf('Total: <info>%sh</info>', DateTimeHelper::decimalToTime($duration)),
            sprintf('Decimal: <info>%sh</info>', $duration),
            '',
        ]);
    }
}
