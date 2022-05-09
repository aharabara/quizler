<?php

namespace Quiz\ORM\StorageDriver;

use Doctrine\Common\Annotations\AnnotationReader;
use LogicException;
use PDO;
use PDOException;
use Quiz\Domain\Answer;
use Quiz\Domain\Question;
use Quiz\Domain\Quiz;
use Quiz\ORM\Builder\CachedDefinitionExtractor;
use Quiz\ORM\Builder\SchemeBuilder\TableDefinition;
use Quiz\ORM\Builder\TableDefinitionExtractor;
use Quiz\ORM\Collection;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflector\Reflector;
use RuntimeException;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\String\Inflector\EnglishInflector;

/** @fixme rewrite to support any entity, not only quiz and question. */
class DBStorageDriver implements StorageDriverInterface
{
    private Serializer $serializer;
    private PDO $connection;
    private EnglishInflector $inflector;
    private Reflector $reflector;
    private CachedDefinitionExtractor $tableExtractor;

    public function __construct()
    {
        $this->tableExtractor = new CachedDefinitionExtractor(new TableDefinitionExtractor());
        $this->inflector = new EnglishInflector();
        $this->reflector = (new BetterReflection())->reflector();
        try {
            $this->connection = new PDO('sqlite:' . DB_PATH);
        } catch (PDOException $e) {
            die ('DB Error. ' . $e->getMessage());
        }
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));

        $camelCaseToSnakeCaseNameConverter = new CamelCaseToSnakeCaseNameConverter();
        $phpDocExtractor = new PhpDocExtractor();
        $getSetNormalizer = new GetSetMethodNormalizer($classMetadataFactory, $camelCaseToSnakeCaseNameConverter, $phpDocExtractor);
        $this->serializer = new Serializer(
            [new ArrayDenormalizer(), new DateTimeNormalizer(), $getSetNormalizer],
            [new YamlEncoder()]
        );
    }

    public function loadBy(string $field, mixed $value): Quiz
    {

        $quizData = $this->first(Quiz::class, [$field => $value]);
        if (empty($quizData)) {
            throw new LogicException("Quiz with criteria $field=$value does not exist.");
        }

        return $this->serializer->denormalize($quizData, Quiz::class);
    }

    public function save(object $model, bool $force = false): bool
    {
        /*fixme replace with universal entity support*/
        if ($model instanceof Answer) {
            return $this->insertAnswer($model);
        }
        if ($model instanceof Question) {
            return $this->insertQuestion($model);
        }
        if ($model instanceof Quiz) {
            if (!$force && $this->quizExists($model)) {
                throw new LogicException('Quiz already exists');
            }
            $this->insertQuiz($model);
        }

        return true;
    }

    protected function quizExists(Quiz $model): bool
    {
        /* fixme create an attribute based uniqness check. */
        return $this
                ->queryAll("SELECT count(*) FROM quizzes WHERE name = '{$model->getName()}'")
                ->first() !== 0;
    }

    public function getList(): Collection
    {
        return $this->queryAll('SELECT * FROM quizzes')->pluck('name');
    }

    public function deploy(): bool
    {
        /** fixme extract it from environment */
        $models = [
            Quiz::class,
            Question::class,
            Answer::class
        ];
        $tableDefinitionExtractor = new TableDefinitionExtractor;

        foreach ($models as $model) {
            $schema = $tableDefinitionExtractor
                ->extract($model)
                ->build();
            $this->connection->exec($schema);
        }

        return true;
    }

    public function drop(): bool
    {
        /*fixme drop by entity class */
        $this->connection->exec('DROP TABLE IF EXISTS questions;');
        $this->connection->exec('DROP TABLE IF EXISTS quizzes;');
        $this->connection->exec('DROP TABLE IF EXISTS answers;');
        $this->connection->exec('DROP TABLE IF EXISTS reports;');
        return true;
    }

    protected function insertQuestion(Question $question): bool
    {
        $query = $this->connection
            ->prepare(
                "INSERT INTO questions (question,tip,quiz_id,updated_at,created_at)" .
                " VALUES (:question, :tip, :quiz_id, :updated_at, :created_at)");

        $query->bindValue('question', $question->getQuestion());
        $query->bindValue('tip', $question->getTip());
        $query->bindValue('quiz_id', $question->getQuiz()->getId());
        $query->bindValue('updated_at', time());
        $query->bindValue('created_at', time());

        if (!$query->execute()) {
            throw new RuntimeException("[{$query->errorCode()}] {$query->errorInfo()}");
        }

        $question->setId((int)$this->connection->lastInsertId('questions'));

        foreach ($question->getAnswers() as $answer) {
            if (empty($answer->getContent())) {
                continue;
            }
            $this->insertAnswer($answer);
        }

        return true;
    }

    protected function insertAnswer(Answer $answer): bool
    {
        $query = $this->connection
            ->prepare(
                "INSERT INTO answers (question_id, content, is_correct ,updated_at,created_at)" .
                " VALUES (:question_id, :content, :is_correct, :updated_at, :created_at)");

        $query->bindValue('question_id', $answer->getQuestion()->getId());
        $query->bindValue('content', $answer->getContent());
        $query->bindValue('is_correct', $answer->getIsCorrect());
        $query->bindValue('updated_at', $answer->getUpdatedAt()->getTimestamp());
        $query->bindValue('created_at', $answer->getCreatedAt()->getTimestamp());

        if (!$query->execute()) {
            throw new RuntimeException("[{$query->errorCode()}] {$query->errorInfo()}");
        }
        $answer->setId((int)$this->connection->lastInsertId('answers'));

        return true;
    }

    protected function insertQuiz(Quiz $quiz): bool
    {
        $query = $this->connection
            ->prepare(
                "INSERT INTO quizzes (name, version, created_at, updated_at)" .
                " VALUES (:name, :version, :created_at, :updated_at)");

        $query->bindValue('name', $quiz->getName());
        $query->bindValue('version', $quiz->getVersion());
        $query->bindValue('created_at', time());
        $query->bindValue('updated_at', time());

        if (!$query->execute()) {
            throw new RuntimeException("[{$query->errorCode()}] {$query->errorInfo()}");
        }
        $quiz->setId((int)$this->connection->lastInsertId('quizzes'));

        foreach ($quiz->getQuestions() as $question) {
            $this->save($question);
        }

        return true;
    }

    public function queryAll(string $sql): Collection
    {
        $query = $this->connection->query($sql);
        if (!$query->execute()) {
            throw new RuntimeException("[{$query->errorCode()}] {$query->errorInfo()}");
        }
        return new Collection($query->fetchAll(PDO::FETCH_ASSOC));
    }

    protected function first(string $class, array $criteria): array
    {
        return $this
            ->all($class, $criteria, 1)
            ->first();
    }

    protected function all(string $class, array $criteria, ?int $limit = null, int $offset = 0): Collection
    {
        /** @var TableDefinition $definition */
        $definition = $this->tableExtractor->extract($class);

        /*fixme we need a query builder */
        $sql = "SELECT * FROM {$definition->getName()} WHERE {$this->prepareWhereClause($criteria)}";
        if ($limit !== null) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }

        $rows = $this->queryAll($sql);

        $parentRecords = new Collection();
        $parentRecords->push(...$rows);

        foreach ($definition->getColumns() as $column) {
            if ($column->isRelationFiled()) { /* fixme add eager/lazy loading and bidirectional setting */
                $relation = $column->getRelationAttribute();
                $localKey = $relation->getLocalKey();
                $relationKey = $relation->getRelationKey();
                $relationSearchCriteria = [$relationKey => $parentRecords->pluck($localKey)->toArray()];

                $relatedRecordsByOwner = $this
                    ->all($relation->getClass(), $relationSearchCriteria)
                    ->groupBy($relationKey);

                foreach ($parentRecords as &$parentRecord) {
                    $parentRecord[$column->getName()] = $relatedRecordsByOwner[$parentRecord[$localKey]] ?? [];
                }
            }
        }

        return $parentRecords;
    }

    protected function prepareWhereClause(array $criteriaList): string
    {
        $clause = [];
        foreach ($criteriaList as $field => $value) {
            if (is_array($value)) {
                $clause[] = "$field IN (" . implode(', ', $value) . ")";
                continue;
            }
            if (is_null($value)) {
                $clause[] = "$field IS NULL";
                continue;
            }
            if (is_string($value)) {
                $value = $this->connection->quote($value);
                $clause[] = "$field = $value";
                continue;
            }
            if (is_bool($value)) {
                if ($value) {
                    $clause[] = "$field IS TRUE";
                } else {
                    $clause[] = "$field IS FALSE";
                }
                continue;
            }
            $clause[] = "$field = $value";
        }
        return implode(' AND ', $clause);
    }
}
