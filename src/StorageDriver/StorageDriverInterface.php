<?php

namespace Quiz\StorageDriver;

use Quiz\Quiz;

interface StorageDriverInterface
{
    public function save(Quiz $quiz): bool;
    public function loadBy(string $field, mixed $value): object;
    public function getList(): array;
}
