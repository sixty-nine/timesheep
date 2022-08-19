<?php

namespace SixtyNine\Timesheep;

use InvalidArgumentException;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use SixtyNine\Timesheep\Console\Style\MyStyle;
use Symfony\Component\Console\Input\StringInput;
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
    /** @var MyStyle */
    private $io;


    public function __construct(string $directory)
    {
        if (!file_exists($directory)) {
            throw new InvalidArgumentException('Cannot find directory '.$directory);
        }

        $this->directory = $directory;
        $this->fs = new Filesystem(new Local($directory));
        $this->io = new MyStyle(new StringInput(''), new ConsoleOutput());
    }

    public function isInstalled(): bool
    {
        return $this->fs->has('/.env') && $this->fs->has('/database/database.db');
    }

    public function check(): void
    {
        if ($this->isInstalled()) {
            $this->io->writeln('<question>Timesheep is already installed</question>');
            die;
        }

        $this->io->writeln('Timesheep is not installed, installing...');
        Assert::true(
            is_writable($this->directory),
            sprintf('<error>The directory "%s" is not writable</error>', $this->directory)
        );

        $this->install();
    }

    protected function install(): void
    {
        if (!$this->fs->has('/.env')) {
            $this->io->writeln('<info>Creating config</info>');
            $config = "TIMESHEEP_DB_URL=sqlite://./database/database.db\n";
            $config .= "BOX_STYLE=box\n";
            $this->fs->write('/.env', $config);
        } else {
            $this->io->writeln('<info>Config already exists</info>');
        }

        if (!$this->fs->has('/database/database.db')) {
            $this->io->writeln('<info>Creating database</info>');

            try {
                if (!$this->fs->has('database')) {
                    $this->fs->createDir('database');
                }
                $dbFile = "{$this->directory}/database/database.db";
                $process = Process::fromShellCommandline("touch $dbFile");
                $process->mustRun();
            } catch (ProcessFailedException $ex) {
                $this->io->writeln(sprintf('<error>Cannot create the database file: %s</error>', $dbFile));
                $this->io->writeln($ex->getMessage());
                die;
            }

            try {
                $process = Process::fromShellCommandline('bin/doctrine orm:schema-tool:create -q');
                $process->mustRun();
            } catch (ProcessFailedException $ex) {
                $this->io->writeln('Doctrine error: Cannot install the database');
                die;
            }
        } else {
            $this->io->writeln('<info>Database already created</info>');
        }
    }
}
