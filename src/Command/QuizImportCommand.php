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
use Symfony\Component\Finder\Finder;
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
    name: 'quiz:import',
    description: 'Add a short description for your command',
)]
class QuizImportCommand extends Command
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
        parent::__construct($name);
    }

    /**
     * @param mixed $forceOverwrite
     * @param SymfonyStyle $io
     * @param string $quizName
     * @return bool
     */
    public function askIfQuizShouldBeRemoved(mixed $forceOverwrite, SymfonyStyle $io, string $quizName): bool
    {
        return $forceOverwrite || $io->confirm("Quiz '$quizName' already exists. Do you want to delete it?", false);
    }

    protected function configure(): void
    {
        $this->addOption('overwrite', 'o', InputOption::VALUE_NEGATABLE, 'Overwrite database quiz with data from files.', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /* @fixme move storage path to env */
        $files = (new Finder())
            ->in($_SERVER['HOME'] . '/.quizzler/storage/quizzes')
            ->name('*.yaml')
            ->getIterator();

        $forceOverwrite = $input->getOption('overwrite');

        $io->section('Importing...');
        foreach ($files as $file) {
            $quizName = $file->getFilenameWithoutExtension();
            if ($quiz = $this->quizRepository->findOneBy(['value' => $quizName])) {
                if (!$this->askIfQuizShouldBeRemoved($forceOverwrite, $io, $quizName)) {
                    continue;
                }
                $this->entityManager->remove($quiz);
                $this->entityManager->flush();
            }
            /** @var Quiz $quiz */
            $quiz = $this->serializer->deserialize($file->getContents(), Quiz::class, 'yaml');
            $quiz->setValue($quizName);

            $this->entityManager->persist($quiz);
            $this->entityManager->flush();
            $io->writeln("<info>- [IMPORTED] $quizName</info>");
        }

        $io->success('You imported all files from quizzler storage.');

        return Command::SUCCESS;
    }
}
