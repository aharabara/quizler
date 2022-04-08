<?php

namespace Quiz\Repository;

use Quiz\Quiz;

interface QuizRepositoryInterface
{
    public function listQuizzes(): array;

    public function loadByName(string $name): Quiz;

    public function save(Quiz $quiz): bool;
}
