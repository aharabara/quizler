<?php


namespace Quiz;

class Quiz
{
    protected string  $name;
    protected string  $version;

    /** @var Question[] */
    protected array $questions;

    public function name(): string
    {
        return $this->name;
    }

    public function version(): string
    {
        return $this->version;
    }

    public function questions(): array
    {
        return $this->questions;
    }
}