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

    /**
     * @return Question[]
     *
     * @psalm-return array<array-key, Question>
     */
    public function questions(): array
    {
        return $this->questions;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    public function addQuestion(Question $question): Question
    {
        $this->questions[] = $question;
        return $question;
    }
}
