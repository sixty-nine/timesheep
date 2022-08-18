<?php

namespace SixtyNine\Timesheep;

use InvalidArgumentException;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;

class Setup
{
    /** @var string */
    private $directory;
    /** @var Filesystem */
    private $fs;
    /** @var ConsoleOutput */
    private $output;


    public function __construct(string $directory)
    {
        if (!file_exists($directory)) {
            throw new InvalidArgumentException('Cannot find directory '.$directory);
        }

        $this->directory = $directory;
        $this->fs = new Filesystem(new Local($directory));
        $this->output = new ConsoleOutput();
    }

    public function isInstalled(): bool
    {
        return $this->fs->has('/.env') && $this->fs->has('/database/database.db');
    }

    public function check(): void
    {
        if ($this->isInstalled()) {
            $this->output->writeln('<question>Timesheep is already installed</question>');
            die;
        }

        $this->output->writeln('Timesheep is not installed, installing...');
        Assert::true(
            is_writable($this->directory),
            sprintf('<error>The directory "%s" is not writable</error>', $this->directory)
        );

        $this->install();
    }

    protected function install(): void
    {
        if (!$this->fs->has('/.env')) {
            $this->output->writeln('Creating <info>config</info>');
            $config = "TIMESHEEP_DB_URL=sqlite://./database/database.db\n";
            $config .= "BOX_STYLE=box\n";
            $this->fs->write('/.env', $config);
        }

        if (!$this->fs->has('/database/database.db')) {
            $this->output->writeln('Creating <info>database.db</info>');

            try {
                if (!$this->fs->has('database')) {
                    $this->fs->createDir('database');
                }
                $dbFile = "{$this->directory}/database/database.db";
                $process = Process::fromShellCommandline("touch $dbFile");
                $process->mustRun();
            } catch (ProcessFailedException $ex) {
                $this->output->writeln(sprintf('<error>Cannot create the database file: %s</error>', $dbFile));
                $this->output->writeln($ex->getMessage());
                die;
            }

            try {
                $process = Process::fromShellCommandline('bin/doctrine orm:schema-tool:create -q');
                $process->mustRun();
            } catch (ProcessFailedException $ex) {
                $this->output->writeln('Doctrine error: Cannot install the database');
                die;
            }
        }
    }
}
