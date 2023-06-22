<?php

namespace App\Command;

use App\Entity\Quiz;
use App\Repository\QuizRepository;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand(
    name: 'quiz:export',
    description: 'Add a short description for your command',
)]
class QuizExportCommand extends Command
{
    protected readonly SerializerInterface $serializer;
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly QuizRepository         $quizRepository,
        string                                    $name = null
    )
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $metadataAwareNameConverter = new MetadataAwareNameConverter($classMetadataFactory);
        $infoExtractor = new ReflectionExtractor();

        $normalizer =  new ObjectNormalizer(
            classMetadataFactory: $classMetadataFactory,
            nameConverter: $metadataAwareNameConverter,
            propertyTypeExtractor: $infoExtractor,
        );

        $this->serializer = new Serializer(
            [new DateTimeNormalizer(), new ArrayDenormalizer(), $normalizer],
            [new YamlEncoder()]
        );
;
        parent::__construct($name);
    }

    /**
     * @param mixed $forceOverwrite
     * @param SymfonyStyle $io
     * @param Quiz $quiz
     * @return bool
     */
    public function askIfFileShouldBeRemoved(mixed $forceOverwrite, SymfonyStyle $io, Quiz $quiz): bool
    {
        return $forceOverwrite || $io->confirm("Quiz export file '{$quiz->getValue()}' already exists. Do you want to delete it?", false);
    }

    protected function configure(): void
    {
        $this->addOption('all', 'a', InputOption::VALUE_NEGATABLE, 'Export full set of fields', false);
        $this->addOption('overwrite', 'o', InputOption::VALUE_NEGATABLE, 'Overwrite old exports', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /* @fixme move to env file this path */
        $baseDir = $_SERVER['HOME'] . '/.quizzler/storage/quizzes';

        $exportGroups = $input->getOption('all') ? ['export', 'export_extra'] : ['export'];
        $forceOverwrite = $input->getOption('overwrite');

        $io->section('Exporting...');

        foreach ($this->quizRepository->findAll() as $quiz) {
            $filePath = "$baseDir/{$quiz->getValue()}.yaml";
            if (file_exists($filePath)) {
                if (!$this->askIfFileShouldBeRemoved($forceOverwrite, $io, $quiz)) {
                    continue;
                }
                unlink($filePath);
            }

            $yaml = $this->serializer->serialize($quiz, 'yaml', [
                'groups' => $exportGroups,
                YamlEncoder::YAML_INLINE => 4,
                YamlEncoder::YAML_INDENTATION => 4,
            ]);

            file_put_contents($filePath, $yaml);

            $io->writeln("<info> - [EXPORTED] '$filePath'</info>");
        }

        $io->success('All quizzes were exported.');

        return Command::SUCCESS;
    }
}
