<?php

namespace Quiz\Console\Command;

use Quiz\Console\OutputStyle\QuizStyle;
use Quiz\Domain\Quiz;
use Quiz\ORM\StorageDriver\DBStorageDriver;
use SplFileInfo;
use Symfony\Component\Console\Color;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use function Symfony\Component\String\s;

class SearchAnswerCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'search-answer';

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setDescription("Run a quiz");
    }

    /**
     * @return int
     *
     * @psalm-return 0|1
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        throw new \RuntimeException('Not implemented yet');

        $style = new QuizStyle($input, $output);
        $quizzes = $this->loadQuizzes();

        /** @todo create a fuzzy-search component */
        $style->clear();
        while ($request = $style->ask("Your request: ")) {
            foreach ($quizzes as $quiz) {
                foreach ($quiz->getQuestions() as $index => $question) {
                    $content = s($question->getQuestion());
                    if ($content->lower()->containsAny(s($request)->lower())) {
                        $style->writeln($content->padStart(3)->prepend('<comment>')->append('</comment>')->toString());
                        $style->writeln($this->textWithSidebar(">> ", $question->getFirstAnswer()));
                    }
                }
            }
            if (!$style->confirm("Continue...")) {
                return Command::SUCCESS;
            };
            $style->clear();
        }

        return Command::SUCCESS;
    }

    /** @return Quiz[] */
    protected function loadQuizzes(): array
    {
        $loader = new DBStorageDriver();
        $finder = new Finder();

        // $HOME/.config/quizler/*.yaml

        return array_map(fn(SplFileInfo $file) => $loader->loadBy('name', $file->getRealPath()), iterator_to_array($files->getIterator()));
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
