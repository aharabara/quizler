<?php

namespace Quiz\Console\Command;

use Quiz\Domain\Answer;
use Quiz\Domain\Quiz;
use Quiz\ORM\Repository\DatabaseRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RunQuizCommand extends Command
{
    protected static $defaultName = 'run';

    private DatabaseRepository $repository;

    /**
     * @return DatabaseRepository
     */
    public function getDatabaseRepository(): DatabaseRepository
    {
        return $this->repository ??= new DatabaseRepository();
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addOption('stop-on-fail', 'f');
        $this->addOption('start-from-empty', 'e');
        $this->setDescription("Run a quiz");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @todo implement N-fails mode. */
        /** @todo implement random questions mode. */

        $style = new SymfonyStyle($input, $output);

        $quiz = $this->chooseQuiz($style);

        $questions = $quiz->getQuestions();
        $total = count($questions);

        foreach ($questions as $index => $question) {
            // skip all present answers
            if ($input->getOption('start-from-empty') && !empty($question->getFirstAnswer())) {
                continue;
            }

            $realIndex = $index + 1;

            $style->section("{$quiz->getName()} [{$realIndex}/{$total}]");

            if ($question->getTip()) {
                $style->writeln("<comment>{$question->getTip()}</comment>");
            }

            $response = $style->ask($question->getQuestion()." ");

            if (empty($response) && $style->confirm("Wanna skip?")) {
                continue;
            }

            $style->writeln("<info>Correct answer</info> : ".$question->getFirstAnswer());
            $style->writeln("<comment>Your answer</comment>    : ".$response);

            $this->getDatabaseRepository()
                ->save(
                    (new Answer())
                        ->setQuestion($question)
                        ->setContent($response)
                        ->setIsCorrect($style->confirm("Guessed?:"))
                );

            if ($input->getOption('stop-on-fail')) {
                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }

    /**
     * @param SymfonyStyle $style
     *
     * @return Quiz
     */
    protected function chooseQuiz(SymfonyStyle $style): Quiz
    {
        $choices = $this->getDatabaseRepository()->getList();

        $name = $style->choice("Choose your quiz:", $choices->getArrayCopy());

        return $this->getDatabaseRepository()->loadBy(Quiz::class, ['name' => $name]);
    }
}
