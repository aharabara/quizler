<?php

namespace Quiz\ORM\Repository;

use Doctrine\Common\Annotations\AnnotationReader;
use LogicException;
use PDO;
use PDOException;
use Quiz\Core\Collection;
use Quiz\Domain\Answer;
use Quiz\Domain\Question;
use Quiz\Domain\Quiz;
use Quiz\ORM\Scheme\Definition\TableDefinition;
use Quiz\ORM\Scheme\Extractor\CachedDefinitionExtractor;
use Quiz\ORM\Scheme\Extractor\TableDefinitionExtractor;
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

/** @fixme rewrite to support any entity, not only quiz and question. */
class DatabaseRepository implements RepositoryInterface
{
    private Serializer $serializer;
    private PDO $connection;
    private CachedDefinitionExtractor|TableDefinitionExtractor $tableExtractor;

    public function __construct(protected string $dbPath)
    {
        $this->tableExtractor = new CachedDefinitionExtractor(new TableDefinitionExtractor());
        $this->connection = $this->getConnection();
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));

        $camelCaseToSnakeCaseNameConverter = new CamelCaseToSnakeCaseNameConverter();
        $phpDocExtractor = new PhpDocExtractor();
        $getSetNormalizer = new GetSetMethodNormalizer($classMetadataFactory, $camelCaseToSnakeCaseNameConverter, $phpDocExtractor);
        $this->serializer = new Serializer(
            [new ArrayDenormalizer(), new DateTimeNormalizer(), $getSetNormalizer],
            [new YamlEncoder()]
        );
    }

    public function loadBy(string $class, array $criteria): object
    {
        $quizData = $this->first($class, $criteria);
        if (empty($quizData)) {
            throw new LogicException(
                sprintf("'%s' with criteria %s does not exist.", $class, json_encode($criteria))
            );
        }

        return $this->serializer->denormalize($quizData, $class);
    }

    public function loadAllBy(string $class, array $criteria): array
    {
        $quizData = $this->all($class, $criteria)->toArray();
        if (empty($quizData)) {
            return [];
        }

        return $this->serializer->denormalize($quizData, $class.'[]');
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
            if (!$force && $this->exists($model)) {
                throw new LogicException('Quiz already exists');
            }
            if ($force) {
                // fixme use insert ignore by unique field
            }
            $this->insertQuiz($model);
        }

        return true;
    }

    /* note This method looks fine  */
    public function delete(object $model, bool $cascade = false): bool
    {
        if (!$this->exists($model)) {
            throw new LogicException('Entity does not exists.' .
                ' Class :"' . get_class($model) . '", ID :"' . $model->getId() . '"');
        }
        $table = $this->tableExtractor->extract(get_class($model));

        if ($cascade) {
            foreach ($table->getColumns() as $column) {
                if ($column->isChildRelationFiled()) {
                    $relationAttribute = $column->getRelationAttribute();
                    $nestedItems = $this->loadAllBy($relationAttribute->getClass(), [
                        $relationAttribute->getRelationKey() => $model->getId()
                    ]);
                    /*fixme add here a check for isCascade. If it isn't the throw an exception */
                    foreach ($nestedItems as $item){
                        $this->delete($item, $cascade);
                    }
                }
            }
        }

        $field = $table->getIdentityColumn();

        $query = $this
            ->connection
            ->query("DELETE FROM {$table->getName()} WHERE {$field->getName()}={$model->getId()}");

        $query->execute();

        return true;
    }

    public function exists(object $model): bool
    {
        $table = $this->tableExtractor->extract(get_class($model));
        $field = $table->getUniqueColumn() ?? $table->getIdentityColumn();

        $refObj = new \ReflectionObject($model);
        $property = $refObj->getProperty($field->getName());
        $criteria = [
            $field->getName() => $property->getValue($model)
        ];
        $result = $this
            ->queryAll("SELECT count(*) as aggregate FROM {$table->getName()} WHERE {$this->prepareWhereClause($criteria)}");

        return $result->first()['aggregate'] !== 0;
    }

    public function getList(): Collection
    {
        return $this->queryAll('SELECT id, name FROM quizzes')->pluck('name', 'id');
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

    public function drop(string $class): bool
    {
        $table = $this->tableExtractor->extract($class);
        $this->connection->exec("DROP TABLE IF EXISTS {$table->getName()};");
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

        try {
            $query->execute();
        } catch (\Throwable $e) {
            dump("Exception thrown for '{$question->getQuestion()}' question from '{$question->getQuiz()->getName()}' quiz.");
            throw $e;
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

    /**
     * @throws PDOException
     */
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

        try {
            $query->execute();
        } catch (\Throwable $e) {
            dump("Exception thrown for '{$quiz->getName()}'. Exception " . $e->getMessage());
            throw $e;
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

    protected function first(string $class, array $criteria): ?array
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
//            if ($column->isParentRelationFiled()){
//                $parentIds =
//                $definitionOfParent = $this->tableExtractor->extract($column->getType());
//                $this->queryAll(
//                    <<<SQL
//    SELECT *
//    FROM {$definitionOfParent->getName()}
//    WHERE {$definitionOfParent->getIdentityColumn()} IN (:$parentIds)
//SQL
//);
//            }

            if ($column->isChildRelationFiled()) { /* fixme add eager/lazy loading and bidirectional setting */
//                dump($column);
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

    public function getConnection(): PDO
    {
        try {
            return new PDO('sqlite:' . $this->dbPath);
        } catch (PDOException $e) {
            if ($e->getCode() === 14) {
                throw new RuntimeException('Application was not yet deployed. Please use `quizler deploy` command');
            }
            throw $e;
        }
    }

    public function getStats(): array
    {
        $totals = $this
            ->getConnection()
            ->query("SELECT quizzes.id, quizzes.name, COUNT(questions.id) as total
                FROM quizzes 
                LEFT JOIN questions ON questions.quiz_id = quizzes.id
                GROUP BY questions.quiz_id
                ORDER BY quizzes.name DESC")
            ->fetchAll(PDO::FETCH_ASSOC);

        $answered = $this
            ->getConnection()
            ->query("SELECT quizzes.id, COUNT(answers.id) total
                FROM quizzes 
                LEFT JOIN questions ON questions.quiz_id = quizzes.id
                LEFT JOIN answers ON answers.question_id = questions.id
                WHERE
                    answers.is_correct = 1
                GROUP BY questions.quiz_id")
            ->fetchAll(PDO::FETCH_ASSOC);

        $totals = array_column($totals, null, 'id');
        $answered = array_column($answered, null, 'id');

        foreach ($totals as $key => $row) {
            $totals[$key]['answered'] = $answered[$key]['total'] ?? 0;
        }
        $done = array_filter($totals, fn(array $quiz) => $quiz['answered'] > ($quiz['total'] - 1));
        $inProgress = array_filter($totals, fn(array $quiz) => $quiz['answered'] < $quiz['total'] && ($quiz['answered'] !== 0));
        $notStarted = array_filter($totals, fn(array $quiz) => $quiz['answered'] === 0);


        return [...$done, ...$inProgress, ...$notStarted];
    }
}