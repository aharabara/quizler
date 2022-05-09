<?php

namespace Quiz\ORM\Repository;

use Quiz\Domain\Question;
use Quiz\Domain\Quiz;
use Quiz\ORM\StorageDriver\StorageDriverInterface;

class QuestionRepository implements RepositoryInterface
{
    public function __construct(protected StorageDriverInterface $driver)
    {
    }

    public function all(): array
    {
        return [];
//        return $this->driver->;
    }

    public function loadByName(string $name): Quiz
    {
        return new Quiz();
    }

    public function save(object $quiz): bool
    {
        return false;
    }

    public function loadById(int $name): Question
    {
        return new Question();
    }
}
