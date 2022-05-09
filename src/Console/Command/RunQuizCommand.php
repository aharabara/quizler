<?php

namespace Quiz\Console\Command;

use Quiz\Domain\Answer;
use Quiz\Domain\Quiz;
use Quiz\ORM\Repository\QuizRepository;
use Quiz\ORM\StorageDriver\DBStorageDriver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RunQuizCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'run';
    private QuizRepository $quizRepository;
    private $driver;

    public function __construct(string $name = null)
    {
        parent::__construct($name);
        $this->driver = new DBStorageDriver(DB_PATH);
//        $this->driver = new YamlStorageDriver();
        $this->quizRepository = new QuizRepository($this->driver);

    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->addOption('stop-on-fail', 'f');
        $this->addOption('start-from-empty', 'e');
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

        $questions = $quiz->getQuestions();
        $total = count($questions);

        foreach ($questions as $index => $question) {
            if ($input->getOption('start-from-empty')){
                if (!empty($question->getFirstAnswer())) continue; // skip all present answers
            }
            $realIndex = $index + 1;
            $style->section("{$quiz->getName()} [{$realIndex}/{$total}]");
            if ($question->getTip()) {
                $style->writeln("<comment>{$question->getTip()}</comment>");
            }
            $response = $style->ask($question->getQuestion() . " ");

            if (empty($response) && $style->confirm("Wanna skip?")){
                continue;
            }

            $style->writeln("<info>Correct answer</info> : " . $question->getFirstAnswer());
            $style->writeln("<comment>Your answer</comment>    : " . $response);

            $this->driver
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

//        (new ReportExporter(REPORTS_FOLDER_PATH))->export($quiz);


        return Command::SUCCESS;
    }

    protected function chooseQuiz(SymfonyStyle $style): Quiz
    {
        ## new YamlStorageDriver();

        $choices = $this->driver->getList();

        $name = $style->choice("Choose your quiz:", $choices->getArrayCopy());

        return $this->driver->loadBy('name', $name);
    }
}