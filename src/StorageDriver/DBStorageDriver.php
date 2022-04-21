<?php

namespace Quiz\StorageDriver;

use Doctrine\Common\Annotations\AnnotationReader;
use LogicException;
use PDO;
use PDOException;
use Quiz\Answer;
use Quiz\Question;
use Quiz\Quiz;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflector\Reflector;
use RuntimeException;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\String\Inflector\EnglishInflector;

/** @fixme rewrite to support any entity, not only quiz and question. */
class DBStorageDriver implements StorageDriverInterface
{
    private Serializer $serializer;
    private PDO $connection;
    private EnglishInflector $inflector;
    private Reflector $reflector;

    public function __construct()
    {
        $this->inflector = new EnglishInflector();
        $this->reflector = (new BetterReflection())->reflector();
        try {
            $this->connection = new PDO('sqlite:' . DB_PATH);
        } catch (PDOException $e) {
            die ('DB Error. ' . $e->getMessage());
        }
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));

        $discriminator = new ClassDiscriminatorFromClassMetadata($classMetadataFactory);

        $propertyNormalizer = new PropertyNormalizer($classMetadataFactory, null, new PhpDocExtractor(), $discriminator);

        $this->serializer = new Serializer(
            [new ArrayDenormalizer(), $propertyNormalizer],
            [new YamlEncoder()]
        );
    }

    public function loadBy(string $field, mixed $value): Quiz
    {
        $data = $this->queryOne("SELECT * FROM quizzes WHERE $field = '$value'");

        if (empty($data)) {
            throw new LogicException("Quiz with criteria $field=$value does not exist.");
        }
        $questions = $this->queryAll("SELECT * FROM questions WHERE quiz_id = $data[id]");
        foreach ($questions as $k => $question) {
            $questions[$k]['answers'] = $this->queryAll("SELECT * FROM answers WHERE question_id = $question[id]");
        }
        $data['questions'] = $questions;

        return $this->serializer->denormalize($data, Quiz::class);
    }

    public function save(object $model, bool $force = false): bool
    {
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
//        $reflClass = $this->reflector->reflectClass($model);
//        $table = $this->getModelTableName($reflClass);
//        if ($reflClass->hasMethod('getId')){
//        }
        /*@fixme write a query builder */
//        $data = $this->serializer->normalize($model);
//
//        $keys = implode(', ', array_keys($data));

//        $insert = "INSERT INTO $table VALUES ($keys) VALUES ";
//        $insert .= "(" . implode(", ", array_map([$this, 'escape'], $data)).")";

        return true;
    }

    protected function quizExists(Quiz $model): bool
    {
        /* fixme create an attribute based uniqness check. */
        return $this->aggregate("SELECT count(*) FROM quizzes WHERE name = '{$model->getName()}'") !== 0;
    }

    public function getList(): array
    {
        return array_column($this->queryAll('SELECT * FROM quizzes'), 'name');
    }

    public function deploy(): bool
    {
        /** @todo generate from models */
        $schema = <<<'SCHEMA'
CREATE TABLE IF NOT EXISTS questions
(
    id         integer
        constraint questions_pk
            primary key autoincrement,
    question   string  not null,
    answer   string,
    tip        string default 'none',
    quiz_id    integer not null,
    updated_at integer,
    created_at integer not null
);

CREATE TABLE IF NOT EXISTS answers
(
    id         integer
        constraint questions_pk
            primary key autoincrement,
    content   string,
    question_id   integer not null,
    is_correct    integer not null,
    updated_at integer,
    created_at integer not null
);

CREATE TABLE IF NOT EXISTS quizzes
(
    id         integer
    constraint questions_pk
        primary key autoincrement,
    name       string  not null,
    version    integer not null,
    create_at  int,
    updated_at integer
);
SCHEMA;

        $this->connection->exec($schema);
        return true;
    }

    public function drop(): bool
    {
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
        $query->bindValue('is_correct', $answer->isCorrect());
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
                "INSERT INTO quizzes (name, version, create_at, updated_at)" .
                " VALUES (:name, :version, :create_at, :updated_at)");

        $query->bindValue('name', $quiz->getName());
        $query->bindValue('version', $quiz->getVersion());
        $query->bindValue('create_at', time());
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

    protected function queryAll(string $sql): array
    {
        $query = $this->connection->query($sql);
        if (!$query->execute()) {
            throw new RuntimeException("[{$query->errorCode()}] {$query->errorInfo()}");
        }
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function queryOne(string $sql): array
    {
        $query = $this->connection->query($sql);
        if (!$query->execute()) {
            throw new RuntimeException("[{$query->errorCode()}] {$query->errorInfo()}");
        }
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    protected function aggregate(string $sql): mixed
    {
        $data = $this->queryAll($sql);
        return array_shift($data);
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @return void
     */
    protected function getModelTableName(ReflectionClass $reflectionClass): void
    {
        strtolower($this->inflector->pluralize($reflectionClass->getShortName()));
    }
}
