<?php

namespace Quiz\ORM\StorageDriver;

use Quiz\Domain\Quiz;
use Quiz\ORM\Collection;

interface StorageDriverInterface
{
    public function save(Quiz $quiz): bool;
    public function loadBy(string $field, mixed $value): object;
    public function getList(): Collection;
}
