<?php

require_once __DIR__.'/../vendor/autoload.php';

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use SixtyNine\Timesheep\Bootstrap;

$container = Bootstrap::boostrap();

// You can append new commands to $commands array, if needed

return ConsoleRunner::createHelperSet($container->get('em'));

