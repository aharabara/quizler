#!/usr/bin/env php
<?php

const STORAGE_FOLDER = __DIR__.'/storage';
const DB_PATH = STORAGE_FOLDER.'/quizler.db';
const DB_FOLDER_PATH = __DIR__.'/storage/';
const QUIZZES_FOLDER_PATH = DB_FOLDER_PATH.'/quizzes';

require __DIR__ . '/vendor/autoload.php';

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