<?php

namespace Quiz;

use Quiz\Console\Command\AnswerStatsCommand;
use Quiz\Console\Command\DeployCommand;
use Quiz\Console\Command\ExportCommand;
use Quiz\Console\Command\GenerateFromCommand;
use Quiz\Console\Command\ImportCommand;
use Quiz\Console\Command\RunQuizCommand;
use Quiz\Console\Command\SearchAnswerCommand;
use Quiz\Console\Command\SearchCommand;
use Quiz\Console\Command\ToDoCommand;
use Symfony\Component\Console\Application;

class ConsoleKernel extends Application
{

    public function handle(): int
    {
        $this->add(new RunQuizCommand($this));
        $this->add(new SearchAnswerCommand());
        $this->add(new AnswerStatsCommand());
        $this->add(new ToDoCommand());
        $this->add(new GenerateFromCommand($this));
        $this->add(new DeployCommand($this));
        $this->add(new ExportCommand($this));
        $this->add(new ImportCommand($this));
        $this->add(new SearchCommand($this));

        return $this->run();
    }

    public function getApplicationStoragePath(): string
    {
        return getenv('HOME') . "/.quizzler";
    }

    public function getResourcesPath(): string
    {
        return __DIR__.'/../resources';
    }

    public function getStoragePath(): string
    {
        return $this->getApplicationStoragePath() . "/storage";
    }

    public function getConfigPath(): string
    {
        return $this->getApplicationStoragePath() . "/config.json";
    }

    public function getDatabasePath(): string
    {
        return $this->getStoragePath() . "/quizzler.db";
    }

    public function getFileReplicaPath(): string
    {
        return $this->getStoragePath() . "/quizzes";
    }
}
