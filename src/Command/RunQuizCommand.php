<?php

namespace Quiz\Command;

use Quiz\Quiz;
use Quiz\QuizLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
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
        $helper = $this->getHelper('question');

        $quiz = $this->chooseQuiz($helper, $input, $output);
        $this->displayQuizHeader($output, $quiz);

        foreach ($quiz->questions() as $question) {
            $question->answer(
                function (string $content, array $choices) use ($helper, $input, $output) {
                    return $helper->ask($input, $output, new ChoiceQuestion($content, $choices));
                }
            );

            if (!$question->answerIsCorrect()) {
                $output->writeln("<info>Correct</info>");
                $output->writeln("");
                continue;
            }
            $output->writeln("<comment>Wrong</comment>");
            $output->writeln("<info>Explanation:</info> {$question->explanation()}");

            // show explanation and wait
            $helper->ask($input, $output, new ConfirmationQuestion("Press [Enter]"));
            if ($input->getOption('stop-on-fail')) {
                return Command::FAILURE;
            }
            $output->writeln("");
        }


        return Command::SUCCESS;
    }

    protected function displayQuizHeader(OutputInterface $output, Quiz $quiz): void
    {
        $headerWidth = 40;
        $emptyLine = "<info>" . str_repeat("#", $headerWidth) . "</info>";
        $headerLine = "<info>" . str_pad(" {$quiz->name()} ", $headerWidth, '#', STR_PAD_BOTH) . "</info>";
        $headerLine = str_replace(" {$quiz->name()} ", "<comment> {$quiz->name()} </comment>", $headerLine);

        $output->writeln("");
        $output->writeln($emptyLine);
        $output->writeln($headerLine);
        $output->writeln($emptyLine);
    }

    protected function chooseQuiz($helper, InputInterface $input, OutputInterface $output): Quiz
    {
        $loader = new QuizLoader();
        $finder = new Finder();

        $files = $finder->in(getcwd() . "/storage/")
            ->name("*.yaml")
            ->files();

        $choices = [];
        foreach ($files->getIterator() as $file) {
            $choices[$file->getFilenameWithoutExtension()] = $file->getRealPath();
        }

        $response = $helper->ask($input, $output, new ChoiceQuestion("Choose your quiz:", array_keys($choices)));
        $filePath = $choices[$response];

        return $loader->load($filePath);
    }
}
