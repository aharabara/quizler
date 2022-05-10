#!/usr/bin/env php
<?php
require __DIR__ . '/bootloader.php';

use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new \Quiz\Console\Command\RunQuizCommand());
$application->add(new \Quiz\Console\Command\SearchAnswerCommand());
$application->add(new \Quiz\Console\Command\AnswerStatsCommand());
$application->add(new \Quiz\Console\Command\ToDoCommand());
$application->add(new \Quiz\Console\Command\GenerateFromCommand());
$application->add(new \Quiz\Console\Command\DeployCommand());
$application->add(new \Quiz\Console\Command\ExportCommand());
$application->add(new \Quiz\Console\Command\ImportCommand());
$application->add(new \Quiz\Console\Command\SearchCommand());
$application->run();

// choice of quiz
// quiz
// stats