<?php

namespace Quiz\Console\Command;

use Quiz\Domain\Answer;
use Quiz\Domain\Question;
use Quiz\Domain\Quiz;
use Quiz\ORM\Repository\DatabaseRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class DeployCommand extends Command
{
    /* the name of the command (the part after "bin/console")*/
    protected static $defaultName = 'deploy';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addOption('force', 'f', InputOption::VALUE_NEGATABLE, 'drop db before deploy', false);
        $this->setDescription('Create a quiz');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fs = new Filesystem();

        if (!$fs->exists(QUIZZES_FOLDER_PATH)) {
            $fs->mkdir(QUIZZES_FOLDER_PATH);
        }

        $dbDriver = new DatabaseRepository();

        $force = false;

        if ($input->getOption('force')) {
            $response = readline('Sure? [y/N] >') ?: 'n';
            $force = strtolower($response) === 'y';
        }

        if ($force) {
            $dbDriver->drop(Quiz::class);
            $dbDriver->drop(Question::class);
            $dbDriver->drop(Answer::class);
            $output->writeln("DB dropped.");
        }

        $dbDriver->deploy();
        $output->writeln("DB deployed.");

        return Command::SUCCESS;
    }
}
