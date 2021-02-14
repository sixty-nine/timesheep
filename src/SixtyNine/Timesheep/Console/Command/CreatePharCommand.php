<?php

namespace SixtyNine\Timesheep\Console\Command;

use Exception;
use Phar;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class CreatePharCommand extends Command
{
    protected static $defaultName = 'phar';

    protected function configure(): void
    {
        $this->setDescription('Create the Timesheep PHAR.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rootDir = dirname(__DIR__, 5);
        $tempDir = sys_get_temp_dir().'/ts-phar';
        $pharFile = $tempDir.'/ts.phar';
        $outFile = $rootDir.'/dist/ts';
        $outDir = dirname($outFile);

        $output->writeln('');
        $output->writeln("Root dir: <info>{$rootDir}</info>");
        $output->writeln("Temp dir: <info>{$tempDir}</info>");
        $output->writeln("Output PHAR: <info>{$outFile}</info>");
        $output->writeln('');

        try {
            $output->writeln('<comment>Create temporary dir</comment>');
            $this->runOrFail(['mkdir', '-p', $tempDir], 'Failed creating: '.$tempDir);

            $output->writeln('<comment>Copy files</comment>');
            mkdir($tempDir.'/bin');
            copy($rootDir.'/composer.json', $tempDir.'/composer.json');
            copy($rootDir.'/composer.lock', $tempDir.'/composer.lock');
            copy($rootDir.'/cli-config.php', $tempDir.'/cli-config.php');
            file_put_contents($tempDir.'/.env', "TIMESHEEP_DB_URL=sqlite://./database.db");
            // TODO: other .env options
            copy($rootDir.'/bin/ts', $tempDir.'/bin/ts');

            $output->writeln('<comment>Copy source</comment>');
            $this->runOrFail(['cp', '-R', $rootDir.'/src/', $tempDir.'/src']);

            chdir($tempDir);

            $output->writeln('<comment>Run composer</comment>');
            $this->runOrFail(['composer', 'install', '--no-dev']);

            $output->writeln('<comment>Create database</comment>');
            $this->runOrFail(['touch', $tempDir.'/database.db']);
            $this->runOrFail(['vendor/bin/doctrine', 'orm:schema-tool:create', '-q']);

            $output->writeln('<comment>Build PHAR</comment>');
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

            $output->writeln('<comment>Create '.$outFile.'</comment>');
            unlink($outDir.'/ts');
            unlink($outDir.'/database.db');
            copy($tempDir.'/database.db', $outDir.'/database.db');
            copy($tempDir.'/ts.phar', $outFile);
            chmod($outFile, 0555);
        } catch (\Exception $ex) {
            $output->writeln('<error>'.trim($ex->getMessage()).'</error>');
        } finally {
            $output->writeln('<comment>Remove temporary dir</comment>');
            $process = new Process(['rm', '-rf', $tempDir]);
            $process->run();

            if (!$process->isSuccessful()) {
                $output->writeln('<error>ERR: Cannot remove temp dir: '.$tempDir.'</error>');
            }
        }

        $output->writeln(PHP_EOL.'Done');

        return 0;
    }

    private function runOrFail(array $commands, string $errorMsg = null): void
    {
        $process = new Process($commands);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new Exception($errorMsg ?? $process->getErrorOutput());
        }
    }
}
