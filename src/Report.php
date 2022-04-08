<?php

namespace Quiz;

class Report
{
    public function __construct(
        protected ?int   $id,
        protected int    $questionId,
        protected string $answer,
        protected bool   $correct
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getQuestionId(): int
    {
        return $this->questionId;
    }

    public function getAnswer(): string
    {
        return $this->answer;
    }

    public function isCorrect(): bool
    {
        return $this->correct;
    }

}