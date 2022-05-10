<?php

namespace Quiz\ORM\Repository;

use Quiz\Core\Collection;
use Quiz\Domain\Quiz;

interface RepositoryInterface
{
    public function save(Quiz $quiz): bool;
    public function loadBy(string $class, array $criteria): object;
    public function getList(): Collection;
}
