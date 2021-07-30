<?php

namespace Quiz;

use Doctrine\Common\Annotations\AnnotationReader;
use LogicException;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

class QuizLoader
{
    private Serializer $serializer;
    private string $folder;

    public function __construct(string $folder)
    {
        $this->folder = $folder;

        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));

        $discriminator = new ClassDiscriminatorFromClassMetadata($classMetadataFactory);

        $propertyNormalizer = new PropertyNormalizer($classMetadataFactory, null, new PhpDocExtractor(), $discriminator);

        $this->serializer = new Serializer(
            [new ArrayDenormalizer(),  $propertyNormalizer],
            [new YamlEncoder()]
        );
    }

    public function load(string $file): Quiz
    {
        if (!file_exists($file)) {
            throw new LogicException("File '$file' does not exist.");
        }

        $content = file_get_contents($file);
        return $this->serializer->deserialize($content, Quiz::class, 'yaml');
        # string:yaml => php:array
        # reflection(Quiz::class) => metadata
            # php:array{quiz:{...:string, questions:Question[]}} => new Quiz()
    }

    public function save(Quiz $quiz, string $fileName): void
    {
        /** @fixme check if such quiz already exists */
        $serializedQuiz = $this->serializer->serialize($quiz, 'yaml', [
            'yaml_inline' => 3
        ]);

        $path = "{$this->folder}/$fileName";
//        if (file_exists($path)) {
//            throw new \LogicException()
//        }

        file_put_contents($path, $serializedQuiz);
    }
}
