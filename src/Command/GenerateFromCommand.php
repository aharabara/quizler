<?php

namespace App\Command;

use App\Entity\Question;
use App\Entity\Quiz;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Encoder\YamlEncoder;


#[AsCommand(
    name: 'quiz:generate',
    description: 'Add a short description for your command',
)]
class GenerateFromCommand extends Command
{
    // the name of the command (the part after "bin/console")
    const OPTION_INTO_FILE_STORAGE = "into-file-storage";
    const OPTION_OVERWRITE = "overwrite";
    protected static $defaultName = 'generate-from';

    public function __construct(
        protected QuizRepository         $repository,
        protected EntityManagerInterface $em,
        string                           $name = null
    )
    {
        parent::__construct($name);
    }

    /**
     * @param mixed $alias
     * @return Quiz
     */
    public function generateConfigurationQuiz(mixed $alias): Quiz
    {
        $result = `php ./bin/console debug:config $alias --no-interaction --no-debug --no-ansi --format=yaml`;

        $yaml = implode("\n", array_slice(explode("\n", $result), 4));
        $data = (new YamlEncoder())->decode($yaml, 'yaml');

        $bundleName = ucwords(str_replace(["_", '.',], ' ', $alias));

        $quiz = new Quiz();
        $quiz->setValue("$bundleName bundle configuration");
        $quiz->setVersion(2);
        foreach ($this->toDotNotation($data) as $configKey => $value) {
            $quiz->addQuestion(
                (new Question())
                    ->setValue("Explain what parts of the bundle `$configKey` key affects? What values it can take?")
            );
        }
        return $quiz;
    }

    /**
     * @param bool|string $folder
     * @param OutputInterface $output
     * @param InputInterface $input
     * @return \App\Entity\Quiz
     */
    public function generatePackageQuiz(bool|string $folder, OutputInterface $output, InputInterface $input): \App\Entity\Quiz
    {
        $vendorFolder = dirname($folder, 2);

        // switch to place where composer should run its commands
        chdir(dirname($vendorFolder));
        $output->writeln("- Vendor at: $vendorFolder");
        $output->writeln("- Project root at: " . dirname($folder));

        $output->writeln("- Dumping autoload at " . dirname($vendorFolder));
        exec('composer dumpa -o'); # generate a list of classes

        if (!file_exists($vendorFolder . "/composer/autoload_classmap.php")) {
            $output->writeln("$vendorFolder is not a vendor folder or autoload_classmap.php is missing");
        }

        $classes = require $vendorFolder . "/composer/autoload_classmap.php";

        $reflector = (new BetterReflection())
            ->reflector();

        $quizName = $this->getTestName($folder);

        $quiz = new \App\Entity\Quiz();
        $quiz->setValue($quizName);
        $quiz->setVersion(2);

        $io = new SymfonyStyle($input, $output);


        $io->writeln("<info>Processing classes:</info>");
        foreach ($classes as $class => $file) {
            if (!str_contains($file, $folder)) {
                continue;
            }
            $io->writeln("- $class");
            $classInfo = $reflector->reflectClass($class);

            $shortName = $this->getShortenedName($classInfo);
            $quiz->addQuestion(
                (new \App\Entity\Question())
                    ->setValue(
                        $this->getQuestionContent($classInfo, $shortName)
                    )
            );

            foreach ($classInfo->getImmediateConstants() as $constant => $values) {
                $quiz->addQuestion(
                    (new \App\Entity\Question())
                        ->setValue("What for is `{$shortName}::{$constant}` constant used for?")
                );
            }
        }
        return $quiz;
    }

    public function getQuestionContent($classInfo, string $shortName): string
    {
        return match (true) {
            !empty($classInfo->getAttributesByName('Attribute')) => "What `@{$shortName}` attribute is used for?",
            $classInfo->isEnum() => "What `{$shortName}` enum is used for?",
            $classInfo->isInterface() => "What `{$shortName}` interface is used for?",
            $classInfo->isTrait() => "What `{$shortName}` trait is used for?",
            default => "What `{$shortName}` class is used for?",
        };
    }

    protected function configure()
    {
        $this->addArgument("folderOrAlias", InputArgument::REQUIRED);
        $this->addOption('config', 'c', InputOption::VALUE_NEGATABLE, 'Generate bunfde configuration quiz', false);
        $this->addOption(self::OPTION_OVERWRITE, 'r', InputOption::VALUE_NEGATABLE, '', false);
        $this->setDescription("...");
    }

    public function toDotNotation($inputArr, $returnArr = array(), $prev_key = ''): array
    {
        foreach ($inputArr as $key => $value) {
            $new_key = $prev_key . $key;

            // check if it's associative array 99% good
            if (is_array($value) && key($value) !== 0 && key($value) !== null) {
                $returnArr = array_merge($returnArr, $this->toDotNotation($value, $returnArr, $new_key . '.'));
            } else {
                $returnArr[$new_key] = $value;
            }
        }

        return $returnArr;

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $classInfo = (new BetterReflection());

        $isConfigQuiz = $input->getOption('config');
        if (!$isConfigQuiz) {
            $folder = realpath($input->getArgument('folderOrAlias'));
            $quiz = $this->generatePackageQuiz($folder, $output, $input);
        } else {
            $alias = $input->getArgument('folderOrAlias');
            $quiz = $this->generateConfigurationQuiz($alias);
        }

        if ($input->getOption(self::OPTION_OVERWRITE)) {
            $oldQuiz = $this->repository->findOneBy(['name' => $quiz->getValue()]);
            $this->em->remove($oldQuiz);
        }

        $this->em->persist($quiz);
        $this->em->flush();

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
