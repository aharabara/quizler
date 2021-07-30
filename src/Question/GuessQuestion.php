<?php

namespace Quiz\Question;

use Quiz\Question;

class GuessQuestion extends Question
{
    protected string $type = 'guess';
    protected ?string $answer;

    public function answer(callable $callback): void
    {
        $this->answer = $callback($this->content, []);
    }

    public function answerIsCorrect(): bool
    {
        return $this->answer !== null;
    }
}
