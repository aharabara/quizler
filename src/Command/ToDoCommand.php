<?php

namespace Quiz\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ToDoCommand extends Command
{
    /* the name of the command (the part after "bin/console")*/
    protected static $defaultName = 'todo';

    public function __construct(string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Create a quiz');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $symfonyStyle->listing([
            '[x] Continue building symfony/serializer quiz.',
            '[x] Finish building symfony/serializer quiz.',
            '[x] Write a quiz questions generator from codebase.',
            '[x] Storage drivers support. Drivers: database or file.',
            '[x] Add a report entity that will save responses.',
            '[x] Finish symfony/http-kernel quiz 25/168.',
            '[x] Extend run quiz with run-from-empty-questions flag.',
            '[-] Rewrite create quiz command. #we have `run -e` and answers in reports',
            '[x] Implement import/export command.',
            '[x] Move answers to reports and rename reports into answers.',
            '[x] Finish symfony/http-kernel quiz 50/168.',
            '[x] Write a schema builder.',
            '[ ] Extract column definition parser.',
            '[ ] Remove redundant code.',
            '[ ] Commit all changes.',
            '[ ] Finish symfony/http-kernel quiz 75/168.',
            '[ ] Write a schema builder.',
            '[ ] Finish symfony/http-kernel quiz 100/168.',
            '[ ] Write a query builder.',
            '[ ] Finish symfony/http-kernel quiz 125/168.',
            '[ ] Finish symfony/http-kernel quiz 150/168.',
            '[ ] Optimize quizzler:stats.',
            '[ ] Finish symfony/http-kernel quiz 168/168.',
            '[ ] Add quiz domain and question complexity level (low, easy, middle, hard, very hard, impressive!).',
            '[ ] Write a serializer builder.',
            '[ ] challenge-me --amount={int} command - throw at users {num} questions from any quiz he will select. Save the report to calculate long-term impact',
            /** todo something like this:
             * SerializerBuilder::create()
             *                  ->accessThroughGettersAndSetters() # GetSetNormalizer
             *                  ->accessThroughPropertyAccessor()  # ObjectNormalizer
             *                  ->accessThroughReflection()        # PropertyNormalizer
             *                  ->withArrayDenormalization()
             *                  ->withUnwrapNormalization()
             *                  ->withDateTimeNormalization($format)
             *                  ->withUidNormalization($format)
             *                  ->withEnumNormalization()
             *                  ->with($customNormalizer)
             *                  ->withEncodersFor('json', 'xml');
             */
            '[ ] When a quiz question has more than one answer we can output a choice between answers (1 correct other are wrong).',
            '[ ] Continue building unit-testing quiz',
        ]);

        return Command::SUCCESS;
    }

}
