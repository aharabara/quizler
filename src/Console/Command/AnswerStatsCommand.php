<?php

namespace Quiz\Console\Command;

use Quiz\Console\OutputStyle\QuizStyle;
use Quiz\Domain\Quiz;
use Quiz\ORM\Repository\DatabaseRepository;
use Symfony\Component\Console\Color;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AnswerStatsCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'stats';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription("...");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new QuizStyle($input, $output);
        $quizzes = $this->loadQuizzes();

        ksort($quizzes);

        $quizzes = array_reverse($quizzes);

        $style->clear();
        $style->title("Quizzes statistics");

        foreach ($quizzes as $quiz) {
            $total = count($quiz->getQuestions());

            $availableQuestions = $quiz->availableQuestions();

            $color = match ($total) {
                0 => new Color("white"),
                $availableQuestions => new Color("green"),
                default => new Color("yellow"),
            };

            $style->writeln($color->apply("Quiz '{$quiz->getName()}' is {$availableQuestions}/{$total} done"));
        }

        $style->writeln("");

        return Command::SUCCESS;
    }

    /** @return Quiz[] */
    protected function loadQuizzes(): array
    {
        $loader = new DatabaseRepository();

        return $loader->getList()
            ->map(fn (string $name): Quiz => $loader->loadBy(Quiz::class, ['name' => $name]))
            ->toArray();
    }
}
