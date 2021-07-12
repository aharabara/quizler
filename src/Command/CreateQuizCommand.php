<?php

namespace Quiz\Command;

use Quiz\Question as QuizQuestion;
use Quiz\Quiz;
use Quiz\QuizLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateQuizCommand extends Command
{
    /* the name of the command (the part after "bin/console")*/
    protected static $defaultName = 'create';

    protected function configure()
    {
        $this->setDescription('Create a quiz');
        $this->addOption('short', 's', InputOption::VALUE_NEGATABLE, 'Only questions mod', false);
        $this->addOption('instant-commit', 'ic', InputOption::VALUE_NEGATABLE, 'Instantly commits changes after a question was added.', false);
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @todo refactor with SymfonyStyle */
        $loader = new QuizLoader();
        $helper = $this->getHelper('question');

        $quiz = new Quiz();

        $ask = function (string $text, string $default = "") use ($output, $input, $helper): string {
            $text = ($default) ? $text . " <comment>[$default]</comment>:" : $text . ":";
            $text = "<info>$text</info> ";

            return $helper->ask($input, $output, new Question($text, $default));
        };

        $askMany = /**
         * @return string[]
         *
         * @psalm-return list<string>
         */
            function (string $question) use ($ask): array {
                $choices = [];
                $index = 0;
                while ($choice = $ask("{$question}[$index]")) {
                    $choices[] = $choice;
                    $index++;
                }
                return $choices;
            };

        $name = $ask("Quiz name", "test");
        $version = $ask("Quiz version", "1.0.0");

        $quiz->setName($name);
        $quiz->setVersion($version);


        do {
            $content = $ask("Question (empty to exit)");
            if (empty($content)) {
                $output->writeln("<comment>Break question prompting.</comment>");
                break;
            }
            $question = $quiz
                ->addQuestion(new QuizQuestion())
                ->setContent($content);
            if (!$input->getOption('short')) {
                $question
                    ->setChoices($askMany("Option"))
                    ->setResponse(explode(",", $ask("Answer (comma separated indexes)")))
                    ->setExplanation($ask("Explanation"));
            }
            if (!$input->getOption('instant-commit')){
                $loader->save($quiz, $ask("Quiz file name", strtolower("{$name}.yaml")));
            }
        } while (true);
        $loader->save($quiz, $ask("Quiz file name", strtolower("{$name}.yaml")));

        $output->writeln("<info>Quiz file was saved</info>");

        return Command::SUCCESS;
    }
}
