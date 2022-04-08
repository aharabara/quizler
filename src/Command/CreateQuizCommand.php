<?php

namespace Quiz\Command;

use Quiz\Question;
use Quiz\Quiz;
use Quiz\StorageDriver\YamlStorageDriver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class CreateQuizCommand extends Command
{
    /* the name of the command (the part after "bin/console")*/
    protected static $defaultName = 'create';
    private YamlStorageDriver $quizLoader;

    public function __construct(string $name = null)
    {
        parent::__construct($name);
        $this->quizLoader = new YamlStorageDriver();
    }

    protected function configure()
    {
        $this->setDescription('Create a quiz');
        $this->addOption('continue', 'c', InputOption::VALUE_NEGATABLE, 'Continue adding existing quiz', false);
        $this->addOption('short', 's', InputOption::VALUE_NEGATABLE, 'Only questions mod', false);
        $this->addOption('instant-commit', 'i', InputOption::VALUE_NEGATABLE, 'Instantly commits changes after a question was added.', false);
        $this->addOption('question-type', 't', InputOption::VALUE_OPTIONAL, 'Type of questions that are going to be introduced');
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $questionTypes = ['choice', 'guess', 'snippet-guess'];

        /** @todo refactor into a separate style, like SymfonyStyle (QuizStyle) */
        $ask = function (string $text, string $default = "") use ($symfonyStyle, $output, $input): string {
            $text = ($default) ? $text . " <comment>[$default]</comment>:" : $text . ":";
            $text = "<info>$text</info> ";

            return $symfonyStyle->ask($text, $default);
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


        $questionType = $input->getOption('question-type');
        if (!in_array($questionType, array_merge($questionTypes, [null, '']))) {
            $output->writeln("Question type '$questionType' is not supported.");
            return Command::FAILURE;
        }

        if ($input->getOption('continue')) {
            [$quiz, $fileName] = $this->chooseQuiz($symfonyStyle);
            $fileName .= ".yaml";
        } else {
            $quiz = new Quiz();
            $name = $ask("Quiz name", "test");
            $version = $ask("Quiz version", "1.0.0");
            $fileName = $ask("Quiz file name", strtolower("{$name}.yaml"));

            $quiz->setName($name);
            $quiz->setVersion($version);
        }


        do {
            $questionType = $questionType ?? $symfonyStyle->choice("Question type?", $questionTypes);
            $content = $ask("Question (empty to exit)");
            if (empty($content)) {
                $symfonyStyle->writeln("<comment>Break question prompting.</comment>");
                break;
            }
            $question = $quiz
                ->addQuestion(new Question())
                ->setQuestion($content);
//            if (!$input->getOption('short') && $questionType === 'choice') {
//                $question
//                    ->setChoices($askMany("Option"))
//                    ->setResponse(explode(",", $ask("Answer (comma separated indexes)")))
//                    ->setExplanation($ask("Explanation"));
//            }
            if ($input->hasOption('instant-commit')) {
                $this->quizLoader->save($quiz, $fileName);
            }
        } while (true);

        $this->quizLoader->save($quiz, $fileName);

        $symfonyStyle->writeln("<info>Quiz file was saved</info>");

        return Command::SUCCESS;
    }

    protected function chooseQuiz(SymfonyStyle $style): array
    {
        $loader = $this->quizLoader;
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

        return [$loader->loadBy('name', $filePath), $response];
    }
}
