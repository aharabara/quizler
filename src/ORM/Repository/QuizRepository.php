<?php

namespace Quiz\ORM\Repository;

use Quiz\Domain\Quiz;
use Quiz\ORM\StorageDriver\StorageDriverInterface;

class QuizRepository implements RepositoryInterface
{
    public function __construct(StorageDriverInterface $driver)
    {
    }

    public function all(): array
    {
        return [];
    }

    public function loadByName(string $name): Quiz
    {
        return new Quiz();
    }

    public function save(object $quiz): bool
    {
        return false;
    }

    public function loadById(int $name): object
    {
        return (object)[];
    }
}
