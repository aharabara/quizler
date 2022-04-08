<?php

namespace Quiz\Command;

use Quiz\Quiz;
use Quiz\Report;
use Quiz\ReportExporter;
use Quiz\Repository\QuizRepository;
use Quiz\StorageDriver\DBStorageDriver;
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
        $reports = [];

        foreach ($questions as $index => $question) {
            $realIndex = $index + 1;
            $style->section("{$quiz->name()} [{$realIndex}/{$total}]");
            if ($question->getTip()) {
                $style->writeln("<comment>{$question->getTip()}</comment>");
            }
            $response = $style->ask($question->getQuestion() . " ");

            $style->writeln("<info>Correct answer</info> : " . $question->getAnswer());
            $style->writeln("<comment>Your answer</comment>    : " . $response);

            $this->driver
                ->save(
                    new Report(null, $question->getId(), $response, $style->confirm("Guessed?:"))
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

        $name = $style->choice("Choose your quiz:", $choices);

        return $this->driver->loadBy('name', $name);
    }
}
