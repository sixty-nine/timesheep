<?php

namespace SixtyNine\Timesheep\Console\Command\Entry;

use DateTimeImmutable;
use SixtyNine\Timesheep\Console\TimesheepCommand;
use SixtyNine\Timesheep\Model\Period;
use SixtyNine\Timesheep\Storage\Entity\Entry;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DeleteEntryCommand extends TimesheepCommand
{
    protected static $defaultName = 'entry:delete';

    protected function configure(): void
    {
        $this
            ->setDescription('Removes an entry.')
            ->setAliases(['e:rm', 'rm'])
            ->addOption('date', null, InputOption::VALUE_OPTIONAL, 'Date inside the entry to delete')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the removal.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Delete an entry');

        $dateTimeFormat = $this->config->get('datetime_format');
        $force = $input->getOption('force');
        $fromStr = $input->getOption('date');

        $periodStart = $fromStr ? new DateTimeImmutable($fromStr) : new DateTimeImmutable();
        $period = new Period($periodStart, $periodStart);
        $entries = $this->entriesRepo->findCrossingEntries($period);

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

        $io->writeln([
            sprintf('Start: <info>%s</info>', $entry->getStartFormatted($dateTimeFormat)),
            sprintf('End: <info>%s</info>', $entry->getEndFormatted($dateTimeFormat)),
            sprintf('Duration: <info>%s</info>', $entry->getPeriod()->getDurationString()),
            sprintf('Decimal: <info>%sh</info>', $entry->getPeriod()->getDuration()),
            sprintf('Project: <info>%s</info>', $entry->getProject()),
            sprintf('Task: <info>%s</info>', $entry->getTask()),
            sprintf('Description: <info>%s</info>', $entry->getDescription()),
        ]);


        if (!$force && !$io->confirm('Do you want to delete this entry?', false)) {
            $io->error('Aborted.');
            return 1;
        }

        $this->entriesRepo->deleteEntry($entry->getId());

        $io->writeln('<question>Entry deleted</question>');
        $io->newLine();

        return 0;
    }
}
