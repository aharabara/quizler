<?php

namespace Quiz\Console\Command;

use Quiz\Domain\Question;
use Quiz\Domain\Quiz;
use Quiz\ORM\StorageDriver\YamlStorageDriver;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateFromCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'generate-from';

    protected function configure()
    {
        $this->addArgument("folder", InputArgument::REQUIRED);
        $this->addOption("rewrite", 'r', InputOption::VALUE_NEGATABLE, '', false);
        $this->setDescription("...");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $folder = $input->getArgument('folder');
//        $finder = new Finder();
//        $files = $finder
//            ->in($folder)
//            ->name('*.php')
//            ->getIterator();

        $classInfo = (new BetterReflection());

        exec('composer dumpa -o'); # generate a list of classes
        $classes = require __DIR__."/../../vendor/composer/autoload_classmap.php";

        $reflector = (new BetterReflection())
            ->reflector();

        $folder = realpath($folder);
        $quiz = new Quiz();

        foreach ($classes as $class => $file) {
            if (!str_contains($file, $folder)){
                continue;
            }
            $classInfo = $reflector->reflectClass($class);

            $shortName = $this->getShortenedName($classInfo);
            $quiz->addQuestion(
                (new Question())
                    ->setQuestion(
                        match (true){
                            !empty($classInfo->getAttributesByName('Attribute')) => "What `@{$shortName}` attribute is used for?",
                            $classInfo->isEnum() => "What `{$shortName}` enum is used for?",
                            $classInfo->isInterface() => "What `{$shortName}` interface is used for?",
                            $classInfo->isTrait() => "What `{$shortName}` trait is used for?",
                            default => "What `{$shortName}` class is used for?",
                        }
                    )
            );

            foreach ($classInfo->getImmediateConstants() as $constant => $values){
                $quiz->addQuestion(
                    (new Question())
                        ->setQuestion("What for is `{$shortName}::{$constant}` constant used for?")
                );
            }
        }

        $quiz->setName($this->getTestName($folder));


        $loader = new YamlStorageDriver();
        $loader->save($quiz, $input->getOption('rewrite'));

        $output->writeln("Test generated.");

        exec('composer dumpa'); # fallback

        return Command::SUCCESS;
    }


    protected function getShortenedName(ReflectionClass $classInfo): string
    {
        return implode("\\", array_slice(explode("\\", $classInfo->getName()), -2, 2));
    }

    protected function getTestName(string $folder): string
    {
        return implode('-', array_slice(explode(DIRECTORY_SEPARATOR, $folder), -2, 2));
    }

}
