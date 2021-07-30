<?php

namespace Quiz\Command;

use Quiz\Question\GuessQuestion;
use Quiz\Question\SnippetGuessQuestion;
use Quiz\Quiz;
use Quiz\QuizLoader;
use Quiz\ReportExporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class RunQuizCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'run';

    public function __construct(string $name = null)
    {
        parent::__construct($name);

    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->addOption('stop-on-fail', 'f');
        $this->addOption('report', 'r');
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

        $questions = $quiz->questions();
        $total = count($questions);
        foreach ($questions as $index => $question) {
            $realIndex = $index + 1;
            $style->section("{$quiz->name()} [{$realIndex}/{$total}]");
            $question->answer(
                function (string $content, array $choices) use ($style, $question, $input, $output) {
                    if ($question instanceof GuessQuestion) {
                        if ($question instanceof SnippetGuessQuestion) {
                            $style->writeln(sprintf("<info>Snippet given:%s</info>", str_repeat("-", 20)));
                            $style->writeln("<comment>{$question->snippet()}</comment>");
                            $response = $style->ask($content . " ");
                            $style->writeln(sprintf("<info>%s</info>", str_repeat("-", 20)));
                        } else {
                            $response = $style->ask($content . " ");
                        }

                        $style->writeln("<info>Correct answer</info> : " . $question->explanation());
                        $style->writeln("<comment>Your answer</comment>    : " . $response);

                        $guessed = $style->ask("Guessed? [y/n] :", 'y');
                        return [(string)$response, strtolower($guessed) === 'y'];
                    } else {
                        return $style->choice($content, $choices);
                    }
                }
            );

            if ($question instanceof GuessQuestion) {
                continue;
            }

            if ($question->answerIsCorrect()) {
                $style->writeln("<info>Correct</info>");
                continue;
            }
            $style->writeln("<comment>Wrong</comment>");
            $style->writeln("<info>Explanation:</info> {$question->explanation()}");

            // show explanation and wait
            $style->confirm("Press [Enter]");
            if ($input->getOption('stop-on-fail')) {
                return Command::FAILURE;
            }
        }

        if ($input->hasOption('report')) {
            (new ReportExporter($this->getStoragePath("reports/{$quiz->name()}")))
                ->export($quiz);
        }


        return Command::SUCCESS;
    }

    protected function chooseQuiz(SymfonyStyle $style): Quiz
    {
        $finder = new Finder();
        $quizLoader = new QuizLoader($this->getStoragePath('quizzes'));

        // $HOME/.config/quizler/*.yaml

        $files = $finder->in($this->getStoragePath())
            ->name("*.yaml")
            ->files();

        $choices = [];
        foreach ($files->getIterator() as $file) {
            $choices[$file->getFilenameWithoutExtension()] = $file->getRealPath();
        }

        $response = $style->choice("Choose your quiz:", array_keys($choices));
        $filePath = $choices[$response];

        return $quizLoader->load($filePath);
    }

    protected function getStoragePath(?string $subFolders = null): string
    {
        $path = "storage";
        if ($subFolders !== null) {
            $path .= "/$subFolders";
        }
        $path = getcwd() . "/" . trim($path, "/");

        if (!is_dir($path)) {
            if (is_file($path)) {
                throw new \LogicException("Cannot create directory '$path'. A file with such name already exists.");
            }
            mkdir($path, 0777, true);
        }
        return $path;
    }
}
