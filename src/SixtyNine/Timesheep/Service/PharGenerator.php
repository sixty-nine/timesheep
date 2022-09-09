<?php

namespace SixtyNine\Timesheep\Service;

use League\Flysystem\Adapter\Local;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use Phar;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Symfony\Component\Process\Process;

class PharGenerator
{
    /** @var string */
    private $tempDir;
    /** @var string */
    private $pharFile;
    /** @var string */
    private $outDir;
    /** @var string */
    private $outFile;
    /** @var Filesystem */
    private $fs;
    /** @var string */
    private $rootDir;

    public function __construct(string $outDir)
    {
        $this->rootDir = dirname(__DIR__, 4);
        $this->tempDir = sys_get_temp_dir() . '/ts-phar';
        $this->pharFile = $this->tempDir . '/ts.phar';
        $this->outDir = $outDir;
        $this->outFile = $outDir . '/ts';
        $this->fs = new Filesystem(new Local('/'));
    }

    /**
     * @throws RuntimeException
     */
    public function createTempDir(): string
    {
        $this->removeTempDir();

        $process = new Process(['mkdir', '-p', $this->tempDir]);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new RuntimeException('Failed creating: ' . $this->tempDir);
        }
        return $this->tempDir;
    }
    
    /**
     * @throws FileExistsException
     * @throws FileNotFoundException
     * @throws RuntimeException
     */
    public function copyFiles(): void
    {
        $this->fs->createDir($this->tempDir.'/bin');
        $this->fs->copy($this->rootDir.'/composer.json', $this->tempDir.'/composer.json');
        $this->fs->copy($this->rootDir.'/composer.lock', $this->tempDir.'/composer.lock');
        $this->fs->copy($this->rootDir.'/bin/doctrine', $this->tempDir.'/bin/doctrine');
        $this->fs->copy($this->rootDir.'/database/database.empty.db', $this->tempDir.'/database.db');
        $this->fs->copy($this->rootDir.'/timesheep.yml', $this->tempDir.'/timesheep.yml');

        $process = new Process(['sed', '-i', 's/database\/database.db/database.db/g', 'timesheep.yml'], $this->tempDir);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput());
        }

        $this->fs->copy($this->rootDir.'/bin/ts', $this->tempDir.'/bin/ts');
        chmod($this->tempDir.'/bin/doctrine', 0555);
        chmod($this->tempDir.'/bin/ts', 0555);

        $process = new Process(['cp', '-R', $this->rootDir.'/src/', $this->tempDir.'/src']);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput());
        }
    }

    public function installDeps(): void
    {
        chdir($this->tempDir);

        $process = new Process(['composer', 'install', '--no-dev']);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput());
        }
    }

    public function buildPhar(): void
    {
        $vendorFiles = new RecursiveDirectoryIterator($this->tempDir, RecursiveDirectoryIterator::SKIP_DOTS);

        $p = new Phar($this->pharFile);
        $p->startBuffering();
        $p->buildFromIterator(new RecursiveIteratorIterator($vendorFiles), $this->tempDir);

        $defaultStub = Phar::createDefaultStub('bin/ts');
        $stub = "#!/usr/bin/env php\n".$defaultStub;
        $p->setStub($stub);
        $p->stopBuffering();
        $p->compressFiles(Phar::GZ);
    }

    /**
     * @throws FileExistsException
     * @throws FileNotFoundException
     */
    public function writeOutput(): void
    {
        if ($this->fs->has($this->outDir.'/ts')) {
            $this->fs->delete($this->outDir.'/ts');
        }
        if ($this->fs->has($this->outDir.'/database.db')) {
            $this->fs->delete($this->outDir . '/database.db');
        }
        if ($this->fs->has($this->outDir.'/timesheep.yml')) {
            $this->fs->delete($this->outDir . '/timesheep.yml');
        }

        $this->fs->copy($this->tempDir.'/database.db', $this->outDir.'/database.db');
        $this->fs->copy($this->tempDir.'/timesheep.yml', $this->outDir.'/timesheep.yml');
        $this->fs->copy($this->tempDir.'/ts.phar', $this->outFile);
        chmod($this->outFile, 0555);
    }

    /**
     * @throws RuntimeException
     */
    public function removeTempDir(): void
    {
        $process = new Process(['rm', '-rf', $this->tempDir]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException('Cannot remove temp dir: '.$this->tempDir);
        }
    }

    public function getTempDir(): string
    {
        return $this->tempDir;
    }

    public function getOutFile(): string
    {
        return $this->outFile;
    }
}
