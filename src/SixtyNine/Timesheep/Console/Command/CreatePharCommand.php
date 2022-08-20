<?php

namespace SixtyNine\Timesheep\Console\Command;

use Exception;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Phar;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SixtyNine\Timesheep\Console\Style\MyStyle;
use SixtyNine\Timesheep\Console\TimesheepCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

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
        $tempDir = sys_get_temp_dir().'/ts-phar';
        $pharFile = $tempDir.'/ts.phar';
        $outFile = $rootDir.'/dist/ts';
        $outDir = dirname($outFile);
        $fs = new Filesystem(new Local('/'));

        $io->title('Summary');
        $io->writeln("Root dir: <info>{$rootDir}</info>");
        $io->writeln("Temp dir: <info>{$tempDir}</info>");
        $io->writeln("Output PHAR: <info>{$outFile}</info>");
        $io->writeln('');

        try {
            $io->title('Execution');
            $io->writeln('<info>Create temporary dir</info>');
            $process = new Process(['rm', '-rf', $tempDir]);
            $process->run();
            $this->runOrFail(['mkdir', '-p', $tempDir], null, 'Failed creating: '.$tempDir);

            $io->writeln('');
            $io->writeln('<info>Copy files</info>');
            $fs->createDir($tempDir.'/bin');
            $fs->copy($rootDir.'/composer.json', $tempDir.'/composer.json');
            $fs->copy($rootDir.'/composer.lock', $tempDir.'/composer.lock');
            $fs->copy($rootDir.'/bin/doctrine', $tempDir.'/bin/doctrine');
            $fs->copy($rootDir.'/database/database.empty.db', $tempDir.'/database.db');
            $fs->copy($rootDir.'/timesheep.yml', $tempDir.'/timesheep.yml');
            $this->runOrFail(
                ['sed', '-i', 's/database\/database.db/database.db/g', 'timesheep.yml'],
                $tempDir
            );
            $fs->copy($rootDir.'/bin/ts', $tempDir.'/bin/ts');
            chmod($tempDir.'/bin/doctrine', 0555);
            chmod($tempDir.'/bin/ts', 0555);

            $io->writeln('');
            $io->writeln('<info>Copy source</info>');
            $this->runOrFail(['cp', '-R', $rootDir.'/src/', $tempDir.'/src']);

            chdir($tempDir);

            $io->writeln('');
            $io->writeln('<info>Run composer</info>');
            $this->runOrFail(['composer', 'install', '--no-dev']);

            $io->writeln('');
            $io->writeln('<info>Build PHAR</info>');
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

            $io->writeln('');
            $io->writeln('<info>Create '.$outFile.'</info>');
            if ($fs->has($outDir.'/ts')) {
                $fs->delete($outDir.'/ts');
            }
            if ($fs->has($outDir.'/database.db')) {
                $fs->delete($outDir . '/database.db');
            }
            $fs->copy($tempDir.'/database.db', $outDir.'/database.db');
            $fs->copy($tempDir.'/ts.phar', $outFile);
            chmod($outFile, 0555);
        } catch (Exception $ex) {
            $io->writeln('<error>'.trim($ex->getMessage()).'</error>');
        } finally {
            if (!$input->getOption('debug')) {
                $io->writeln('');
                $io->writeln('<info>Remove temporary dir</info>');
                $process = new Process(['rm', '-rf', $tempDir]);
                $process->run();

                if (!$process->isSuccessful()) {
                    $io->writeln('<error>ERR: Cannot remove temp dir: '.$tempDir.'</error>');
                }
            }
        }

        $io->writeln(PHP_EOL.'Done');

        return 0;
    }

    private function runOrFail(array $commands, string $cwd = null, string $errorMsg = null): void
    {
        $this->io->writeln(sprintf(
            '<comment>></comment> %s',
            implode(' ', $commands)
        ));
        $process = new Process($commands, $cwd);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($errorMsg ?? $process->getErrorOutput());
        }
    }
}
