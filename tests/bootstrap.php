<?php

$loader = require dirname(__DIR__, 3) . '/vendor/autoload.php';

$loader->addPsr4('GlpiPlugin\\Autoexportsearches\\', dirname(__DIR__) . '/src/');
$loader->addPsr4('GlpiPlugin\\Autoexportsearches\\Tests\\', dirname(__DIR__) . '/tests/');
