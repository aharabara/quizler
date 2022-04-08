<?php

namespace Quiz\Command;

use Quiz\StorageDriver\YamlStorageDriver;
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
            '[ ] Finish symfony/http-kernel quiz.',
            '[ ] Implement import/export command.',
            '[ ] Implement answers fetch from reports.',
            '[ ] Rewrite create quiz command.',
            '[ ] Write a schema builder.',
            '[ ] Write a query builder.',
            '[ ] Write a query builder.',
            '[ ] challenge-me --amount={int} command - throw at users {num} questions from any quiz he will select. Save the report to calculate long-term impact',
            '[ ] Write a serializer builder.',
            /** todo something like this:
             * SerializerBuilder::create()
             *                  ->accessThroughGettersAndSetters() # GetSetNormalizer
             *                  ->accessThroughPropertyAccessor()  # ObjectNormalizer
             *                  ->accessThroughReflection()        # PropertyNormalizaer
             *                  ->withArrayDenormalization()
             *                  ->withUnwrapNormalization()
             *                  ->withDateTimeNormalization($format)
             *                  ->withUidNormalization($format)
             *                  ->withEnumNormalization()
             *                  ->with($customNormalizer)
             *                  ->withEncodersFor('json', 'xml');
             */
            '[ ] Report answers into quiz load and compare. Use wrong answer to give multiple choices.',
            '[ ] Continue building unit-testing quiz',
        ]);

        return Command::SUCCESS;
    }

}
