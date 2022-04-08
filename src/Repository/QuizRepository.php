<?php

namespace Quiz\Repository;

use Quiz\Quiz;
use Quiz\StorageDriver\StorageDriverInterface;

class QuizRepository implements QuizRepositoryInterface
{
    public function __construct(StorageDriverInterface $driver)
    {
    }

    public function listQuizzes(): array
    {
        return [];
    }

    public function loadByName(string $name): Quiz
    {
        return new Quiz();
    }

    public function save(Quiz $quiz): bool
    {
        return false;
    }
}
