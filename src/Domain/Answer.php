<?php

namespace Quiz\Domain;

use Quiz\ORM\Builder\SchemeBuilder\Identificator;
use Quiz\ORM\Builder\SchemeBuilder\Searchable;
use Quiz\ORM\Traits\Timestampable;
use Symfony\Component\Serializer\Annotation\Ignore;

class Answer
{
    #[Identificator()]
    protected int $id;

    #[Searchable()]
    protected string $content;
    protected bool $isCorrect = false;

//    #[Relation(Question::class)] fixme add loading when it is main class (if quiz then load nested, if answer, then load parent). Maybe solve with ParentRelation or LazyRelation
    #[Ignore]
    protected Question $question;

    use Timestampable;

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

    public function getIsCorrect(): bool
    {
        return $this->isCorrect;
    }

    public function setIsCorrect(bool $isCorrect): Answer
    {
        $this->isCorrect = $isCorrect;
        return $this;
    }

}
