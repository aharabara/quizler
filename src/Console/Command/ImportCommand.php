<?php

namespace Quiz\Console\Command;

use Quiz\Domain\Quiz;
use Quiz\ORM\Repository\DatabaseRepository;
use Quiz\ORM\Repository\YamlRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends Command
{
    /* the name of the command (the part after "bin/console")*/
    protected static $defaultName = 'import';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addOption('force', 'f', InputOption::VALUE_NEGATABLE, '', false);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fileDriver = new YamlRepository();
        $dbDriver = new DatabaseRepository();

        foreach ($fileDriver->getList() as $name){
            $quiz = $fileDriver->loadBy(Quiz::class, ['name' => $name]);

            if (!$dbDriver->exists($quiz)){
                $output->writeln("Importing $name.");
                $dbDriver->save($quiz, $input->getOption('force'));
                continue;
            }

            $dbQuiz = $dbDriver->loadBy(Quiz::class, ['name' => $quiz->getName()]);

            $output->writeln("Updating $name.");

            foreach ($quiz->getQuestions() as $question) {
                if (!$dbQuiz->hasQuestion($question)){
                    $output->writeln("- {$quiz->getName()} : {$question->getQuestion()}.");
                    $dbQuiz->addQuestion($question);
                    $dbDriver->save($question);
                }
            }
        }

        return Command::SUCCESS;
    }
}
