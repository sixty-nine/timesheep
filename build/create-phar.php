<?php
// The php.ini setting phar.readonly must be set to 0

use Symfony\Component\Process\Process;

$rootDir = dirname(__DIR__);
$outDir = $rootDir.'/dist';
$pharFile = $outDir.'/ts.phar';

require_once $rootDir.'/vendor/autoload.php';

echo "Root directory: $rootDir\n";
echo "Output directory: $outDir\n";
echo "PHAR file: $pharFile\n\n";

echo "Removing old output directory\n";
$process = new Process(['rm', '-rf', $outDir]);
$process->run();

if (!$process->isSuccessful()) {
    echo 'Error while removing output dir: '.$outDir;
}

echo "Copying files\n";
mkdir($outDir);
mkdir($outDir.'/bin');
mkdir($outDir.'/database');
copy($rootDir.'/composer.json', $outDir.'/composer.json');
copy($rootDir.'/composer.lock', $outDir.'/composer.lock');
copy($rootDir.'/database/database.empty.db', $outDir.'/database/database.empty.db');
//file_put_contents($outDir.'/.env', "TIMESHEEP_DB_URL=sqlite://$rootDir/database/database.db");
file_put_contents($outDir.'/.env', "TIMESHEEP_DB_URL=sqlite://tmp/123");
//copy($rootDir.'/.env', $outDir.'/.env');
copy($rootDir.'/bin/ts', $outDir.'/bin/ts');

$process = new Process(['cp', '-R', $rootDir.'/src', $outDir.'/src']);
$process->run();

if (!$process->isSuccessful()) {
    echo 'Copy files failed: '.$process->getErrorOutput();
}

chdir($outDir);

echo "Running composer\n";
$process = new Process(['composer', 'install', '--no-dev']);
$process->run();

if (!$process->isSuccessful()) {
    echo 'composer failed: '.$process->getErrorOutput();
}

$p = new Phar($pharFile);

echo "Building PHAR\n";
$p->startBuffering();
$it = new RecursiveDirectoryIterator($outDir);
$it->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
$p->buildFromIterator(new RecursiveIteratorIterator($it), $outDir);
$defaultStub = $p->createDefaultStub('bin/ts');
$stub = "#!/usr/bin/env php\n".$defaultStub;
$p->setStub($stub);
$p->stopBuffering();

//$p->compress(Phar::GZ);

copy($outDir.'/ts.phar', $outDir.'/ts');
chmod($outDir.'/ts', 0555);

echo "\n$outDir/ts successfully created\n";
