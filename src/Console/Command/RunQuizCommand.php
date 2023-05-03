<?php

namespace Quiz\Console\Command;

use Quiz\ConsoleKernel;
use Quiz\Domain\Answer;
use Quiz\Domain\Quiz;
use Quiz\ORM\Repository\DatabaseRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RunQuizCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'run';
    private DatabaseRepository $repository;

    public function __construct(protected ConsoleKernel $kernel,string $name = null)
    {
        parent::__construct($name);

    }
    public function getDatabaseRepository(): DatabaseRepository
    {
        return $this->repository ??= new DatabaseRepository($this->kernel->getDatabasePath());
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

            if ($response === '~'){
                $this->getDatabaseRepository()
                    ->save(
                        (new Answer())
                            ->setQuestion($question)
                            ->setContent($response)
                            ->setIsCorrect(false)
                    );
                $style->writeln("<info>Question marked as irelevant (~)</info>");
                continue;
            }

            $style->writeln("<info>Correct answer</info> : " . $question->getFirstAnswer());
            $style->writeln("<comment>Your answer</comment>    : " . $response);

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

//        (new ReportExporter(REPORTS_FOLDER_PATH))->export($quiz);


        return Command::SUCCESS;
    }

    protected function chooseQuiz(SymfonyStyle $style): Quiz
    {
        $choices = $this->getDatabaseRepository()->getList();

        $name = $style->choice("Choose your quiz:", $choices->getArrayCopy());

        return $this->getDatabaseRepository()->loadBy(Quiz::class, ['name' => $name]);
    }
}
