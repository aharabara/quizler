<?php

namespace Quiz\Command;

use Quiz\Question as QuizQuestion;
use Quiz\Quiz;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

class CreateQuizCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'create';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        $quiz = new Quiz();

        $ask = function (string $text, string $default = "") use ($output, $input, $helper): string {
            $text = ($default) ? $text . " <comment>[$default]</comment>:" : $text . ":";
            $text = "<info>$text</info>";

            return $helper->ask($input, $output, new Question($text, $default));
        };

        $askMany = function(string $question) use ($ask) {
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
            $quiz
                ->addQuestion(new QuizQuestion())
                ->setContent($content)
                ->setChoices($askMany("Option"))
                ->setResponses(explode(",", $ask("Answer (comma separated indexes)")))
                ->setExplanation($ask("Explanation"))
            ;
        } while (true);

        $serializer = new Serializer([new ArrayDenormalizer(), new PropertyNormalizer()], [new YamlEncoder()]);

        $fileName = $ask("Quiz file name", strtolower("{$name}.yaml"));

        $serializedQuiz = $serializer->serialize($quiz, 'yaml', [
            'yaml_inline' => 3
        ]);

        file_put_contents(__DIR__."/../../storage/$fileName", $serializedQuiz);

        $output->writeln("<info>Quiz file was saved</info>");

        return Command::SUCCESS;
    }
}
