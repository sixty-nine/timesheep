<?php

namespace SixtyNine\Timesheep\Console\Command;

use Exception;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Phar;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class CreatePharCommand extends Command
{
    protected static $defaultName = 'create-phar';

    /** @var OutputInterface */
    private $output;

    protected function configure(): void
    {
        $this
            ->setDescription('Create the Timesheep PHAR.')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'If set, the temporary dir is not removed');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;

        $rootDir = dirname(__DIR__, 5);
        $tempDir = sys_get_temp_dir().'/ts-phar';
        $pharFile = $tempDir.'/ts.phar';
        $outFile = $rootDir.'/dist/ts';
        $outDir = dirname($outFile);
        $fs = new Filesystem(new Local('/'));

        $output->writeln('');
        $output->writeln("Root dir: <info>{$rootDir}</info>");
        $output->writeln("Temp dir: <info>{$tempDir}</info>");
        $output->writeln("Output PHAR: <info>{$outFile}</info>");
        $output->writeln('');

        try {
            $output->writeln('<question>Create temporary dir</question>');
            $this->runOrFail(['mkdir', '-p', $tempDir], null, 'Failed creating: '.$tempDir);

            $output->writeln('');
            $output->writeln('<question>Copy files</question>');
            $fs->createDir($tempDir.'/bin');
            $fs->copy($rootDir.'/composer.json', $tempDir.'/composer.json');
            $fs->copy($rootDir.'/composer.lock', $tempDir.'/composer.lock');
            $fs->copy($rootDir.'/bin/doctrine', $tempDir.'/bin/doctrine');
            $fs->copy($rootDir.'/database/database.empty.db', $tempDir.'/database.db');
            chmod($tempDir.'/bin/doctrine', 0555);
            $fs->write($tempDir.'/.env', "TIMESHEEP_DB_URL=sqlite://./database.db\n");
            // TODO: other .env options
            $fs->copy($rootDir.'/bin/ts', $tempDir.'/bin/ts');
            chmod($tempDir.'/bin/ts', 0555);

            $output->writeln('');
            $output->writeln('<question>Copy source</question>');
            $this->runOrFail(['cp', '-R', $rootDir.'/src/', $tempDir.'/src']);

            chdir($tempDir);

            $output->writeln('');
            $output->writeln('<question>Run composer</question>');
            $this->runOrFail(['composer', 'install', '--no-dev']);

            $output->writeln('');
            $output->writeln('<question>Build PHAR</question>');
            $p = new Phar($pharFile);
            $p->startBuffering();
            $it = new RecursiveDirectoryIterator($tempDir);
            $it->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
            $p->buildFromIterator(new RecursiveIteratorIterator($it), $tempDir);
            $defaultStub = $p->createDefaultStub('bin/ts');
            $stub = "#!/usr/bin/env php\n".$defaultStub;
            $p->setStub($stub);
            $p->stopBuffering();
            //$p->compress(Phar::GZ);

            $output->writeln('');
            $output->writeln('<question>Create '.$outFile.'</question>');
            if ($fs->has($outDir.'/ts')) {
                $fs->delete($outDir.'/ts');
            }
            if ($fs->has($outDir.'/database.db')) {
                $fs->delete($outDir . '/database.db');
            }
            $fs->copy($tempDir.'/database.db', $outDir.'/database.db');
            $fs->copy($tempDir.'/ts.phar', $outFile);
            chmod($outFile, 0555);
        } catch (\Exception $ex) {
            $output->writeln('<error>'.trim($ex->getMessage()).'</error>');
        } finally {
            if (!$input->getOption('debug')) {
                $output->writeln('');
                $output->writeln('<question>Remove temporary dir</question>');
                $process = new Process(['rm', '-rf', $tempDir]);
                $process->run();

                if (!$process->isSuccessful()) {
                    $output->writeln('<error>ERR: Cannot remove temp dir: '.$tempDir.'</error>');
                }
            }
        }

        $output->writeln(PHP_EOL.'Done');

        return 0;
    }

    private function runOrFail(array $commands, string $cwd = null, string $errorMsg = null): void
    {
        $this->output->writeln(sprintf(
            '> <info>%s</info>',
            implode(' ', $commands)
        ));
        $process = new Process($commands, $cwd);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($errorMsg ?? $process->getErrorOutput());
        }
    }
}
