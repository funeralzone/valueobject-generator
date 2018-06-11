#!/usr/bin/env php
<?php

use Funeralzone\FAS\Consumerist\Commands\Consumerist;
use Funeralzone\FAS\Consumerist\Commands\ConsumeristManager;
use Funeralzone\FAS\FasApp\FAS;
use Funeralzone\FAS\FasApp\Prooph\Commands\Projectionist;
use Funeralzone\FAS\TenancyManager\TenancyManagerCommand;
use Symfony\Component\Console\Application as ConsoleApplication;

require __DIR__ . '/vendor/autoload.php';

$console = new ConsoleApplication;
$fas = FAS::app(__DIR__ . '/src/Application');

$console->add($fas->get(Projectionist::class));
$console->add($fas->get(TenancyManagerCommand::class));
$console->add($fas->get(Consumerist::class));
$console->add($fas->get(ConsumeristManager::class));
$console->run();
