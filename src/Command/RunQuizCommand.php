<?php

namespace Quiz\Command;

use Quiz\Quiz;
use Quiz\QuizLoader;
use Symfony\Component\Console\Color;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class RunQuizCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'run';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->addOption('stop-on-fail');
        $this->setDescription("Run a quiz");
    }

    /**
     * @return int
     *
     * @psalm-return 0|1
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @todo implement N-fails mode. */
        /** @todo implement random questions mode. */

        $style = new SymfonyStyle($input, $output);

        $quiz = $this->chooseQuiz($style);
        $style->title($quiz->name());

        foreach ($quiz->questions() as $question) {
            $question->answer(
                function (string $content, array $choices) use ($style, $question, $input, $output) {
                    if ($question->isGuessQuestion()) {
                        $response = $style->ask($content . " ");

                        $style->writeln("<info>Correct answer</info> : " . $question->explanation());
                        $style->writeln("<comment>Your answer</comment>    : " . $response);

                        $guessed = $style->ask("Guessed? [y/n] :", 'y');
                        return strtolower($guessed) === 'y';
                    } else {
                        return $style->choice($content, $choices);
                    }
                }
            );

            if ($question->isGuessQuestion()) {
                $output->writeln(sprintf("<comment>%s</comment>", str_pad("", getenv('COLUMNS'), "=")));
                continue;
            }
            if ($question->answerIsCorrect()) {
                $output->writeln("<info>Correct</info>");
                continue;
            }
            $output->writeln("<comment>Wrong</comment>");
            $output->writeln("<info>Explanation:</info> {$question->explanation()}");

            // show explanation and wait
            $style->confirm("Press [Enter]");
            if ($input->getOption('stop-on-fail')) {
                return Command::FAILURE;
            }
        }


        return Command::SUCCESS;
    }

    protected function chooseQuiz(SymfonyStyle $style): Quiz
    {
        $loader = new QuizLoader();
        $finder = new Finder();

        // $HOME/.config/quizler/*.yaml

        $files = $finder->in(getcwd() . "/storage/")
            ->name("*.yaml")
            ->files();

        $choices = [];
        foreach ($files->getIterator() as $file) {
            $choices[$file->getFilenameWithoutExtension()] = $file->getRealPath();
        }

        $response = $style->choice("Choose your quiz:", array_keys($choices));
        $filePath = $choices[$response];

        return $loader->load($filePath);
    }
}
