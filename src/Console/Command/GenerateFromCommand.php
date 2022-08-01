<?php

namespace Quiz\Console\Command;

use Quiz\ConsoleKernel;
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
    // the name of the command (the part after "bin/console")
    const OPTION_INTO_FILE_STORAGE = "into-file-storage";
    const OPTION_REWRITE = "rewrite";
    protected static $defaultName = 'generate-from';

    public function __construct(protected ConsoleKernel $kernel, string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->addArgument("folder", InputArgument::REQUIRED);
        $this->addOption(self::OPTION_INTO_FILE_STORAGE, 's', InputOption::VALUE_NEGATABLE, 'Generate into yaml file', false);
        $this->addOption(self::OPTION_REWRITE, 'r', InputOption::VALUE_NEGATABLE, '', false);
        $this->setDescription("...");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption(self::OPTION_INTO_FILE_STORAGE)) {
            $repository = new YamlRepository($this->kernel->getFileReplicaPath());
        } else {
            $repository = new DatabaseRepository($this->kernel->getDatabasePath());
        }

        $classInfo = (new BetterReflection());

        $folder = realpath($input->getArgument('folder'));
        $vendorFolder = dirname($folder, 2);

        // switch to place where composer should run it's commands
        chdir(dirname($vendorFolder));
        $output->writeln("- Vendor at: $vendorFolder");
        $output->writeln("- Project root at: " . dirname($folder));

        $output->writeln("- Dumping autoload at ".dirname($vendorFolder));
        exec('composer dumpa -o'); # generate a list of classes

        if (!file_exists($vendorFolder . "/composer/autoload_classmap.php")) {
            $output->writeln("$vendorFolder is not a vendor folder or autoload_classmap.php is missing");
        }
        $classes = require $vendorFolder . "/composer/autoload_classmap.php";

        $reflector = (new BetterReflection())
            ->reflector();

        $quiz = new Quiz();

        foreach ($classes as $class => $file) {
            if (!str_contains($file, $folder)) {
                continue;
            }
            $classInfo = $reflector->reflectClass($class);

            $shortName = $this->getShortenedName($classInfo);
            $quiz->addQuestion(
                (new Question())
                    ->setQuestion(
                        match (true) {
                            !empty($classInfo->getAttributesByName('Attribute')) => "What `@{$shortName}` attribute is used for?",
                            $classInfo->isEnum() => "What `{$shortName}` enum is used for?",
                            $classInfo->isInterface() => "What `{$shortName}` interface is used for?",
                            $classInfo->isTrait() => "What `{$shortName}` trait is used for?",
                            default => "What `{$shortName}` class is used for?",
                        }
                    )
            );

            foreach ($classInfo->getImmediateConstants() as $constant => $values) {
                $quiz->addQuestion(
                    (new Question())
                        ->setQuestion("What for is `{$shortName}::{$constant}` constant used for?")
                );
            }
        }

        $quiz->setName($this->getTestName($folder));

        $repository->save($quiz, $input->getOption(self::OPTION_REWRITE));

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
