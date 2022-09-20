<?php

namespace Quiz\Console\Command;

use Quiz\Domain\Question;
use Quiz\Domain\Quiz;
use Quiz\ORM\Repository\DatabaseRepository;
use Quiz\ORM\Repository\YamlRepository;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateFromCommand extends Command
{
    private const OPTION_INTO_FILE_STORAGE = "into-file-storage";

    private const OPTION_REWRITE = "rewrite";

    protected static $defaultName = 'generate-from';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addArgument("folder", InputArgument::REQUIRED)
            ->addOption(self::OPTION_INTO_FILE_STORAGE, 's', InputOption::VALUE_NEGATABLE, 'Generate into yaml file', false)
            ->addOption(self::OPTION_REWRITE, 'r', InputOption::VALUE_NEGATABLE, '', false)
            ->setDescription("...");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repository = $input->getOption(self::OPTION_INTO_FILE_STORAGE) ? new YamlRepository() : new DatabaseRepository();

        // generate a list of classes
        exec('composer dump -o');

        $classes = require VENDOR_FOLDER . "/composer/autoload_classmap.php";

        $reflector = (new BetterReflection())->reflector();

        $folder = realpath($input->getArgument('folder'));

        $quiz = new Quiz();

        foreach ($classes as $class => $file) {
            $file = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $file);

            if (!str_contains($file, $folder)) {
                continue;
            }

            $classInfo = $reflector->reflectClass($class);
            $question = $this->prepareQuestion($classInfo);

            $quiz->addQuestion($question);
        }

        $quiz->setName($this->getTestName($folder));

        $repository->save($quiz, $input->getOption(self::OPTION_REWRITE));

        $output->writeln("Test generated.");

        // fallback
        exec('composer dump');

        return Command::SUCCESS;
    }

    /**
     * @param ReflectionClass $classInfo
     *
     * @return Question
     */
    private function prepareQuestion(ReflectionClass $classInfo): Question
    {
        $question = new Question();

        $shortName = $this->getShortenedName($classInfo);

        $question
            ->setQuestion(
                match (true) {
                    !empty($classInfo->getAttributesByName('Attribute')) => "What `@{$shortName}` attribute is used for?",
                    $classInfo->isEnum() => "What `{$shortName}` enum is used for?",
                    $classInfo->isInterface() => "What `{$shortName}` interface is used for?",
                    $classInfo->isTrait() => "What `{$shortName}` trait is used for?",
                    default => "What `{$shortName}` class is used for?",
                }
            );

        $immediateConstants = $classInfo->getImmediateConstants();

        if (!empty($immediateConstants)) {
            foreach ($immediateConstants as $constant => $values) {
                $question->setQuestion("What for is `{$shortName}::{$constant}` constant used for?");
            }
        }

        return $question;
    }

    /**
     * @param ReflectionClass $classInfo
     *
     * @return string
     */
    protected function getShortenedName(ReflectionClass $classInfo): string
    {
        return implode("\\", array_slice(explode("\\", $classInfo->getName()), -2, 2));
    }

    /**
     * @param string $folder
     *
     * @return string
     */
    protected function getTestName(string $folder): string
    {
        return implode('-', array_slice(explode(DIRECTORY_SEPARATOR, $folder), -2, 2));
    }
}
