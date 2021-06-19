<?php

namespace Quiz;

use LogicException;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

class QuizLoader
{
    private Serializer $serializer;

    public function __construct()
    {
        $propertyNormalizer = new PropertyNormalizer(null, null, new PhpDocExtractor());

        $this->serializer = new Serializer(
            [new ArrayDenormalizer(), $propertyNormalizer],
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
    }

    public function save(Quiz $quiz, string $fileName): void
    {
        $serializedQuiz = $this
            ->serializer
            ->serialize($quiz, 'yaml', [
                'yaml_inline' => 3
            ]);

        $path = getcwd(). "/storage/$fileName";
//        if (file_exists($path)) {
//            throw new \LogicException()
//        }

        file_put_contents($path, $serializedQuiz);
    }
}
