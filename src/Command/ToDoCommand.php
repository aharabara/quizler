<?php

namespace Quiz\Command;

use Quiz\Question as QuizQuestion;
use Quiz\Quiz;
use Quiz\QuizLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class ToDoCommand extends Command
{
    /* the name of the command (the part after "bin/console")*/
    protected static $defaultName = 'todo';
    private QuizLoader $quizLoader;

    public function __construct(string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Create a quiz');
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $symfonyStyle->listing([
            '[TODO APP] Add items',
            '[TODO APP] Edit items',
            '[TODO APP] Delete items',
            '[TODO APP] Mark as done items',
            '[TODO APP] Search items',
            '[TODO APP] Register as global application and show on each terminal start',
            '[Quiz] Continue building symfony/serializer quiz.',
            '[Quiz] Continue building unit-testing quiz',
            '[Quiz] Quizler to anki',
            '[Quiz] Storage drivers support. Choose format from : yaml, json, sqlite and etc',
            '[ANKI] https://github.com/bmaupin/flashcard-sets/blob/main/scripts/csv-to-apkg.py',
            '[ANKI] https://addon-docs.ankiweb.net/getting-started.html',
            '[ANKI] https://github.com/bmaupin/flashcard-sets/tree/main/scripts#anki-api',
            '[ANKI] https://docs.ankiweb.net/getting-started.html#key-concepts',
        ]);

        return Command::SUCCESS;
    }

}
