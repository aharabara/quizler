<?php

namespace Quiz;

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
        $this->serializer = new Serializer([new ArrayDenormalizer(), new PropertyNormalizer(null, null, new PhpDocExtractor())], [new YamlEncoder()]);
    }

    public function load(string $file): Quiz
    {
        if (!file_exists($file)) {
            throw new \LogicException("File '$file' does not exist.");
        }

        $content = file_get_contents($file);
        return $this->serializer->deserialize($content, Quiz::class, 'yaml');
    }
}