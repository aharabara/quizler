<?php

namespace Quiz;

use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

class ReportExporter
{
    private Serializer $serializer;
    private string $folder;

    public function __construct(string $folder)
    {
        $propertyNormalizer = new PropertyNormalizer(null, null, new PhpDocExtractor());

        $this->serializer = new Serializer(
            [new ArrayDenormalizer(),  $propertyNormalizer],
            [new YamlEncoder()]
        );
        $this->folder = $folder;
    }

    public function export(Quiz $quiz): void
    {
        /** @fixme check if such quiz already exists */
        $serializedQuiz = $this->serializer->serialize($quiz, 'yaml', [
            'yaml_inline' => 3
        ]);

        $path = "{$this->folder}/report_".time().'.yaml';
//        if (file_exists($path)) {
//            throw new \LogicException()
//        }

        file_put_contents($path, $serializedQuiz);
    }
}
