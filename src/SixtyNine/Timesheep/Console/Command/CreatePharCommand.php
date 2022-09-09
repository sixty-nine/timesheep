<?php

namespace SixtyNine\Timesheep\Console\Command;

use Exception;
use RuntimeException;
use SixtyNine\Timesheep\Console\Style\MyStyle;
use SixtyNine\Timesheep\Console\TimesheepCommand;
use SixtyNine\Timesheep\Service\PharGenerator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreatePharCommand extends TimesheepCommand
{
    protected static $defaultName = 'create-phar';

    /** @var MyStyle */
    private $io;

    protected function configure(): void
    {
        $this
            ->setDescription('Create the Timesheep PHAR.')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'If set, the temporary dir is not removed');
    }

    protected function execute(InputInterface $input, OutputInterface $io): int
    {
        $io = new MyStyle($input, $io);
        $this->io = $io;

        $rootDir = dirname(__DIR__, 5);

        $generator = new PharGenerator($rootDir . '/dist');

        $io->title('Summary');
        $io->writeln("Root dir: <info>{$rootDir}</info>");
        $io->writeln("Temp dir: <info>{$generator->getTempDir()}</info>");
        $io->writeln("Output PHAR: <info>{$generator->getOutFile()}</info>");
        $io->writeln('');

        try {
            $io->title('Execution');
            $io->writeln('<info>Create temporary dir</info>');
            $generator->createTempDir();

            $io->writeln('');
            $io->writeln('<info>Copy files</info>');
            $generator->copyFiles();

            $io->writeln('');
            $io->writeln('<info>Run composer</info>');
            $generator->installDeps();

            $io->writeln('');
            $io->writeln('<info>Build PHAR</info>');
            $generator->buildPhar();

            $io->writeln('');
            $io->writeln('<info>Create '.$generator->getOutFile().'</info>');
            $generator->writeOutput();
        } catch (Exception $ex) {
            $io->writeln('<error>'.trim($ex->getMessage()).'</error>');
        } finally {
            if (!$input->getOption('debug')) {
                $io->writeln('');
                $io->writeln('<info>Remove temporary dir</info>');

                try {
                    $generator->removeTempDir();
                } catch (RuntimeException $ex) {
                    $io->writeln('<error>ERR: Cannot remove temp dir: '.$generator->getTempDir().'</error>');
                }
            }
        }

        $io->writeln(PHP_EOL.'Done');

        return 0;
    }
}
