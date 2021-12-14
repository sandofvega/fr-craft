<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Fortrabbit\CraftPluginList\Commands\CraftPluginListCommand;

$craftPluginListCommand = new CraftPluginListCommand;

$application = new Application();

$application->add($craftPluginListCommand);
$application->setDefaultCommand($craftPluginListCommand->getName(), true);

$application->run();