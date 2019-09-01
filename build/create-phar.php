<?php
// The php.ini setting phar.readonly must be set to 0
$pharFile = dirname(__DIR__).'/ts.phar';

// clean up
if (file_exists($pharFile)) {
    unlink($pharFile);
}
//if (file_exists($pharFile . '.gz')) {
//    unlink($pharFile . '.gz');
//}
// create phar
chdir(dirname(__DIR__));
$p = new Phar($pharFile);

$p->startBuffering();
$p->addFile('bin/ts');
$p->addFile('composer.json');
$p->addFile('composer.lock');
$p->buildFromDirectory(dirname(__DIR__), '/(?:vendor|src|\.env$)/');
$defaultStub = $p->createDefaultStub('bin/ts');
$stub = "#!/usr/bin/php \n".$defaultStub;

// Add the stub
$p->setStub($stub);
$p->stopBuffering();

//$p->compress(Phar::GZ);

echo "$pharFile successfully created\n";
