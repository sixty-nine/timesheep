<?php

namespace SixtyNine\Timesheep;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Output\ConsoleOutput;
use Webmozart\Assert\Assert;

class Setup
{
    public const TIMESHEEP_DIR = '.timesheep';

    /** @var string */
    private $homeDir;
    /** @var Filesystem */
    private $fs;
    /** @var ConsoleOutput */
    private $output;


    public function __construct()
    {
        $homeDir = posix_getpwuid(posix_getuid());

        if (!$homeDir) {
            throw new \InvalidArgumentException('Cannot find home directory');
        }

        $this->homeDir = $homeDir['dir'];
        $this->fs = new Filesystem(new Local($this->homeDir));
        $this->output = new ConsoleOutput();
    }

    public function getInstallDir(): string
    {
        return $this->homeDir.'/'.self::TIMESHEEP_DIR;
    }

    public function isInstalled(): bool
    {
        return $this->fs->has(self::TIMESHEEP_DIR)
            && $this->fs->has(self::TIMESHEEP_DIR.'/config')
            && $this->fs->has(self::TIMESHEEP_DIR.'/database.db')
        ;
    }

    public function check(): void
    {
        if (!$this->isInstalled()) {
            $this->output->writeln('Timesheep is not installed, installing...');
            Assert::true(
                is_writable($this->homeDir),
                sprintf('The directory "%s" is not writable', $this->homeDir)
            );

            $this->install();
        }
    }

    protected function install(): void
    {
        $tsDir = $this->homeDir.'/'.self::TIMESHEEP_DIR;

        if (!$this->fs->has($tsDir)) {
            $this->output->writeln(sprintf('Creating home dir <info>%s</info>', $tsDir));
            $this->fs->createDir($tsDir);
        }

        if (!$this->fs->has(self::TIMESHEEP_DIR.'/config')) {
            $this->output->writeln('Creating <info>config</info>');
            $config = "TIMESHEEP_DB_URL=sqlite://$tsDir/database.db\n";
            $config .= "BOX_STYLE=box\n";
            $this->fs->write(self::TIMESHEEP_DIR.'/config', $config);
        }

        if (!$this->fs->has(self::TIMESHEEP_DIR.'/database.db')) {
            $this->output->writeln('Creating <info>database.db</info>');
            $localFs = new Filesystem(new Local(dirname(__DIR__, 3)));
            $stream = $localFs->readStream('/database/database.empty.db'); // TODO: add this to phar creation

            if (!$stream) {
                die('cannot create the database');
            }

            $this->fs->writeStream(self::TIMESHEEP_DIR.'/database.db', $stream);
            is_resource($stream) && fclose($stream);
        }
    }
}
