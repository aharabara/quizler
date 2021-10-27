#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new \Quiz\Command\RunQuizCommand());
$application->add(new \Quiz\Command\CreateQuizCommand());
$application->add(new \Quiz\Command\SearchAnswerCommand());
$application->add(new \Quiz\Command\AnswerStatsCommand());
$application->add(new \Quiz\Command\ToDoCommand());
$application->run();

// choice of quiz
// quiz
// stats