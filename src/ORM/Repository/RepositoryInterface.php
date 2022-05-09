<?php

namespace Quiz\ORM\Repository;

interface RepositoryInterface
{
    public function all(): array;

    public function loadByName(string $name): object;

    public function loadById(int $name): object;

    public function save(object $quiz): bool;
}
