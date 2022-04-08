<?php

namespace Quiz\StorageDriver;

use Doctrine\Common\Annotations\AnnotationReader;
use LogicException;
use Quiz\Quiz;
use Symfony\Component\Finder\Finder;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

class YamlStorageDriver implements StorageDriverInterface
{
    private Serializer $serializer;
    private string $folder;

    public function __construct()
    {
        $this->folder = QUIZZES_FOLDER_PATH;

        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));

        $discriminator = new ClassDiscriminatorFromClassMetadata($classMetadataFactory);

        $propertyNormalizer = new PropertyNormalizer($classMetadataFactory, null, new PhpDocExtractor(), $discriminator);

        $this->serializer = new Serializer(
            [new ArrayDenormalizer(),  $propertyNormalizer],
            [new YamlEncoder()]
        );
    }

    public function loadBy(string $field, mixed $value): Quiz
    {
        $file = "{$this->folder}/$value.yaml";
        if (!file_exists($file)) {
            $content = file_get_contents($file);
            throw new LogicException("Quiz '$value' does not exist.");
        }

        $content = file_get_contents($file);
        return $this->serializer->deserialize($content, Quiz::class, 'yaml');
    }

    public function save(Quiz $quiz, bool $force = false): bool
    {
        $serializedQuiz = $this->serializer->serialize($quiz, 'yaml', [
            'yaml_inline' => 3
        ]);

        if (!$force && $this->quizExists($quiz)) {
            throw new \LogicException('Quiz already exists');
        }

        file_put_contents($this->getFileFullPathFor($quiz), $serializedQuiz);

        return true;
    }

    protected function quizExists(Quiz $quiz): bool
    {
        return file_exists($this->getFileFullPathFor($quiz));
    }

    protected function getFileFullPathFor(Quiz $quiz): string
    {
        return "{$this->folder}/{$quiz->name()}.yaml";
    }

    public function getList(): array
    {
        $finder = new Finder();

        $files = $finder->in($this->folder)
            ->name("*.yaml")
            ->files();

        $choices = [];
        foreach ($files->getIterator() as $file) {
            $choices[] = $file->getFilenameWithoutExtension();
        }

        return $choices;
    }


}
