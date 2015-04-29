<?php

use Composer\Autoload\ClassLoader;

/** @var ClassLoader $loader */
$loader = require __DIR__ . '/../../../../../vendor/autoload.php';
$loader->addPsr4('Box\\Component\\Console\\Tests\\', __DIR__);
