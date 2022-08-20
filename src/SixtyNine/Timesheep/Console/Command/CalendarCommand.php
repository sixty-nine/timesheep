<?php

namespace SixtyNine\Timesheep\Console\Command;

use DateTimeImmutable;
use Exception;
use SixtyNine\Timesheep\Console\Style\MyStyle;
use SixtyNine\Timesheep\Console\TimesheepCommand;
use SixtyNine\Timesheep\Model\Calendar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CalendarCommand extends TimesheepCommand
{
    protected static $defaultName = 'calendar';

    protected function configure(): void
    {
        $this
            ->setDescription('Show a calendar.')
            ->addOption('from', null, InputOption::VALUE_OPTIONAL, 'Start datetime')
            ->setAliases(['cal'])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cal = new Calendar();
        $io = new MyStyle($input, $output);

        $days = $cal->getDayNames();

        try {
            $start = $input->getOption('from')
                ? new DateTimeImmutable($input->getOption('from'))
                : new DateTimeImmutable()
            ;
        } catch (Exception $ex) {
            $io->writeln(sprintf('<error>Invalid start date: %s</error>', $input->getOption('from')));
            return 1;
        }

        $firstDate = $start->modify('first day of this month');
        $firstDay = getdate($firstDate->getTimestamp());
        $lastDay = getdate($start->modify('last day of this month')->getTimestamp());

        $io->newLine();
        $io->writeln(sprintf(' <comment>%s %s</comment>', $firstDay['month'], $firstDay['year']));

        // Display day's names
        for ($i = 0; $i < 7; $i++) {
            $io->write(sprintf('<question> %s</question>', $days[$i]));
        }
        $io->newLine();

        // Display empty days before the month
        $count = 0;
        for ($i = 0; $i < $firstDay['wday']; $i++) {
            $io->write('  * ');
            $count++;
        }

        // Display the month's days
        for ($i = 1; $i <= $lastDay['mday']; $i++) {
            $spacer = $i < 10 ? ' ' : '';
            $styled = $cal->isWorkingDay($firstDate->modify(($i - 1) . ' day'));

            $io->write(sprintf('%s %s%s %s', $styled ? '<info>' : '', $spacer, $i, $styled ? '</info>' : ''));
            if ($count % 7 === 6) {
                $io->newLine();
            }
            $count++;
        }

        // Display empty days after the month
        for ($i = $lastDay['wday'] + 1; $i < 7; $i++) {
            $io->write('  * ');
        }

        $io->newLine();
        $io->newLine();

        return 0;
    }
}
