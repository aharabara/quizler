<?php

namespace Quiz;

use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

class Exporter
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

        $this->getStoragePath("{$this->folder}/{$quiz->getName()}");
        $path = "{$this->folder}/report_".time().'.yaml';

        file_put_contents($path, $serializedQuiz);
    }

    protected function getStoragePath(?string $subFolders = null): string
    {
        $path = "storage";
        if ($subFolders !== null) {
            $path .= "/$subFolders";
        }
        $path = getcwd() . "/" . trim($path, "/");

        if (!is_dir($path)) {
            if (is_file($path)) {
                throw new \LogicException("Cannot create directory '$path'. A file with such name already exists.");
            }
            mkdir($path, 0777, true);
        }
        return $path;
    }

}
