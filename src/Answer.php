<?php

namespace Quiz;

use Quiz\Builder\SchemeBuilder\Identificator;
use Quiz\Builder\SchemeBuilder\Relation;

class Answer
{
    #[Identificator()]
    protected int $id;
    protected string $content;
    protected bool $isCorrect = false;

    #[Relation(Question::class)]
    protected Question $question;

    protected ?\DateTimeInterface $createdAt = null;
    protected ?\DateTimeInterface $updatedAt = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getQuestion(): Question
    {
        return $this->question;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        if (null === $this->createdAt){
            $this->createdAt = date_create();
        }
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        if (null === $this->updatedAt){
            $this->updatedAt = date_create();
        }
        return $this->updatedAt;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setQuestion(Question $question): Answer
    {
        $this->question = $question;
        return $this;
    }

    public function setContent(string $content): Answer
    {
        $this->content = $content;
        return $this;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function isCorrect(): bool
    {
        return $this->isCorrect;
    }

    public function setIsCorrect(bool $isCorrect): Answer
    {
        $this->isCorrect = $isCorrect;
        return $this;
    }

}
