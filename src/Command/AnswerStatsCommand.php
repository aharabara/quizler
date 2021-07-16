<?php

namespace Quiz\Command;

use Quiz\OutputStyle\QuizStyle;
use Quiz\Quiz;
use Quiz\QuizLoader;
use SplFileInfo;
use Symfony\Component\Console\Color;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use function Symfony\Component\String\s;

class AnswerStatsCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'stats';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setDescription("...");
    }

    /**
     * @return int
     *
     * @psalm-return 0|1
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
            $done = 0;
            $total = count($quiz->questions());
            foreach ($quiz->questions() as $question) {
                if (!empty($question->explanation())) {
                    $done++;
                }
            }
            if ($total === $done) {
                $color = new Color("green");
            } else {
                $color = new Color("white");
            }
            $style->writeln($color->apply("Quiz '{$quiz->name()}' is {$done}/{$total} done"));
        }
        $style->writeln("");

        return Command::SUCCESS;
    }

    /** @return Quiz[] */
    protected function loadQuizzes(): array
    {
        $loader = new QuizLoader();
        $finder = new Finder();

        // $HOME/.config/quizler/*.yaml

        $files = $finder->in(getcwd() . "/storage/")
            ->name("*.yaml")
            ->files();

        return array_map(fn(SplFileInfo $file) => $loader->load($file->getRealPath()), iterator_to_array($files->getIterator()));
    }

    /**
     * @param string $text
     * @param string $bar
     * @return string
     */
    protected function textWithSidebar(string $bar, string $text): string
    {
        $barColor = new Color("cyan");
        $bar = ($barColor)->apply($bar);
        return
            s($text)
                ->wordwrap(getenv('COLUMNS') - strlen($bar))
                ->prepend("$bar<info>")
                ->append("</info>");
    }
}
