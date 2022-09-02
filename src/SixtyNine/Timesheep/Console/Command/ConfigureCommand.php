<?php

namespace SixtyNine\Timesheep\Console\Command;

use SixtyNine\Timesheep\Console\Style\MyStyle;
use SixtyNine\Timesheep\Console\TimesheepCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class ConfigureCommand extends TimesheepCommand
{
    protected static $defaultName = 'configure';

    protected function configure(): void
    {
        $this
            ->setDescription('Configure timesheep.')
            ->setAliases(['conf', 'config'])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new MyStyle($input, $output);
        $configFile = $this->config->get('config_file');

        $isNumber = static function ($number) {
            if (!is_numeric($number)) {
                throw new \RuntimeException('You must type a number.');
            }
            return $number;
        };

        $boxStyles = [
            'borderless',
            'compact',
            'symfony-style-guide',
            'box',
            'box-double',
        ];

        $io->title('Timesheep configuration');
        $io->text([
            'Modify the timesheep configuration.',
            '',
            sprintf('Changes will be written to <comment>%s</comment>.', $configFile),
        ]);
        $io->section('Configuration');
        $boxStyle = $io->choice(
            '<info>Box style:</info>',
            $boxStyles,
            $this->config->get('box_style')
        );
        $dateFormat = $io->ask('Date format:', $this->config->get('date_format'));
        $timeFormat = $io->ask('Time format:', $this->config->get('time_format'));
        $hoursPerDay = (int)$io->ask('Hours per day:', $this->config->get('hours_due_per_day'), $isNumber);
        $occupationRate = $io->ask('Occupation rate:', $this->config->get('occupation_rate'), $isNumber);

        $io->section('Summary');
        $io->listing([
            "<info>Box style</info>: <comment>$boxStyle</comment>",
            "<info>Date format</info>: <comment>$dateFormat</comment>",
            "<info>Time format</info>: <comment>$timeFormat</comment>",
            "<info>Hours per day</info>: <comment>$hoursPerDay</comment>",
            "<info>Occupation rate</info>: <comment>$occupationRate</comment>",
        ]);

        $confirmation = $io->confirm('Write changes', false);
        if (!$confirmation) {
            $io->writeln('<error>Aborted.</error>');
            $io->newLine();
            return 1;
        }

        $output = <<<EOF
timesheep:
  database_url: {$this->config->get('database_url')}
  box_style: $boxStyle
  date_format: $dateFormat
  time_format: $timeFormat
  hours_due_per_day: $hoursPerDay
  occupation_rate: $occupationRate

EOF;

        file_put_contents($configFile, $output);

        $io->writeln(sprintf('<question>Config written to %s.</question>', $configFile));
        $io->newLine();

        return 0;
    }
}
