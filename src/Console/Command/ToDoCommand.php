<?php

namespace Quiz\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ToDoCommand extends Command
{
    /* the name of the command (the part after "bin/console")*/
    protected static $defaultName = 'todo';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription('Create a quiz');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
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
            '[x] Extract column definition parser.',
            '[x] Remove redundant code.',
            '[x] Commit all changes.',
            '[x] Write a schema builder.',
            '[x] Finish symfony/http-kernel quiz 75/168.',
            '[x] Finish symfony/http-kernel quiz 100/168.',
            '[x] Write a fast search for questions/answers with tnt search',
            '[x] Finish symfony/http-kernel quiz 125/168.',
            '[x] Move command generate-from to db storage',
            '[x] Finish symfony/http-kernel quiz 150/168.',
            '[x] Finish symfony/http-kernel quiz 168/168.',
            '[x] Modify import to synchronise database and filesys.',
            '[x] Write a quiz about test types.',
            '[ ] Extend quiz about testing with testing approaches like BDD, TDD and so on.',
            '[ ] Extend quiz about testing with tools and tricks that are used in testing.',
            '[ ] Check what you can do with gherkin',
            '[ ] Cover application with tests',
            '[ ] Write a query builder.',
            '[ ] Add --only-questions flag into export/import to minimize the size of the file.',
            '[ ] Write an interactive todo app based on yaml that can be integrated into quizzler',
            '[ ] Docker quiz',
            '[ ] CodeReferenceStemmer for faster search through questions.',
            '[ ] Virtual DOM',
            '[ ] Siege gorilla - takes a OAS and iterates over each endpoint passing payloads that were mixed with correct and wrong data and data types.' .
            'After a run it will ask for is the responses of the system are correct and will try to figure out patterns of responses.' .
            'On second run it will only ask for differences in behaviour. If differences are ok - it will try to figure out the pattern, if they are not ok, then it will proved a set of scenarios where it happens.',
            '[ ] Write a primitive frontend using http kernel with builtin server in order to perform quiz runs.',
            '[ ] Implement a indexer that will extract references and will store them in a separate index-table so it can be used to jump across related questions and answers',
            '[ ] Implement graph-like UI that will tell me what tags/references are related to current question/answer and give me possibility to jump to it.',
            '[ ] Implement second adapter for search to use simple SQL instead of TNT',
            '[ ] WebsocketKernel for searches and etc. Maybe used instead of http in a give-me-fragment(SSI/ESI lol) mode.',
            '[ ] https://www.w3.org/TR/edge-arch/', # fook # fook me twice it is also about ESI
            '[ ] https://www.w3.org/1999/04/Editing/#3.1', # sheet
            '[ ] Symfony http cache.', # about caching standart used by symfony
                '[ ] https://datatracker.ietf.org/doc/html/rfc2616',
                '[ ] https://datatracker.ietf.org/doc/html/rfc7234',
                '[ ] https://tomayko.com/blog/2008/things-caches-do',
                '[ ] https://www.mnot.net/cache_docs/',
                '[ ] https://datatracker.ietf.org/doc/html/rfc7232/',
                '[ ] https://foshttpcachebundle.readthedocs.org/',
            '[ ] Write a Command bus with handlers autoresolving though method signature + reflection API.',
            '[ ] Replace is_correct with priority levels to sort responses from correct to less correct and wrong (or use statuses)',
            '[ ] Write SAML specification quiz',
            '[ ] Write JSON:API specification quiz',
            '[ ] Write Jsend specification quiz',
            '[ ] Write SOAP specification quiz',
            '[ ] Write OpenAPI specification quiz',
            '[ ] Write HATEOAS specification quiz',
            '[ ] Locking mechanisms. Filesystem locks.',
            '[ ] HTTP headers and directives.',
            '[ ] Fix column sorting with timestamps',
            '[ ] Move quizzler:stats to quizzler:run command.',
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
        ]);

        return Command::SUCCESS;
    }
}
