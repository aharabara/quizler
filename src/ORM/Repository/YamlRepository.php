<?php

namespace Quiz\ORM\Repository;

use Doctrine\Common\Annotations\AnnotationReader;
use LogicException;
use Quiz\Core\Collection;
use Quiz\Domain\Quiz;
use Symfony\Component\Finder\Finder;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class YamlRepository implements RepositoryInterface
{
    private Serializer $serializer;
    private string $folder;

    public function __construct()
    {
        $this->folder = QUIZZES_FOLDER_PATH;

        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $phpDocExtractor = new PhpDocExtractor();
        $getSetNormalizer = new GetSetMethodNormalizer($classMetadataFactory, null, $phpDocExtractor);
        $this->serializer = new Serializer(
            [
                new ArrayDenormalizer(),
                new DateTimeNormalizer(),
                $getSetNormalizer,
            ],
            [new YamlEncoder()]
        );
    }

    public function loadBy(string $class, array $criteria): Quiz
    {
        [$value] = $criteria;
        $file = "{$this->folder}/$value.yaml";
        if (!file_exists($file)) {
            throw new LogicException("Quiz '$value' does not exist.");
        }

        $content = file_get_contents($file);
        return $this->serializer->deserialize($content, Quiz::class, 'yaml');
    }

    public function save(Quiz $quiz, bool $force = false): bool
    {
        $serializedQuiz = $this->serializer->serialize($quiz, 'yaml', [
            'yaml_inline' => 4,
            YamlEncoder::YAML_INDENT => 0,
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['id', 'createdAt', 'updatedAt'],
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
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
        return rtrim($this->folder, '/')."/".str_replace("/", "-", ltrim($quiz->getName(), "/")).".yaml";
    }

    public function getList(): Collection
    {
        $finder = new Finder();

        $files = $finder->in($this->folder)
            ->name("*.yaml")
            ->files();

        $choices = [];
        foreach ($files->getIterator() as $file) {
            $choices[] = $file->getFilenameWithoutExtension();
        }

        return new Collection($choices);
    }


}
