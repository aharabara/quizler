<?php

namespace Quiz\Domain;

use Quiz\ORM\Scheme\Attribute\Identificator;
use Quiz\ORM\Scheme\Attribute\ParentRelation;
use Quiz\ORM\Scheme\Attribute\Searchable;
use Quiz\ORM\Traits\Timestampable;
use Symfony\Component\Serializer\Annotation\Ignore;

class Answer
{
    use Timestampable;
    #[Identificator()]
    protected int $id;

    #[Searchable()]
    protected string $content;
    protected bool $isCorrect = false;

    #[Ignore]
    #[ParentRelation(Question::class, 'question_id', 'id')]
    protected Question $question;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Question
     */
    public function getQuestion(): Question
    {
        return $this->question;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param int $id
     *
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @param Question $question
     *
     * @return $this
     */
    public function setQuestion(Question $question): Answer
    {
        $this->question = $question;

        return $this;
    }

    /**
     * @param string $content
     *
     * @return $this
     */
    public function setContent(string $content): Answer
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsCorrect(): bool
    {
        return $this->isCorrect;
    }

    /**
     * @param bool $isCorrect
     *
     * @return $this
     */
    public function setIsCorrect(bool $isCorrect): Answer
    {
        $this->isCorrect = $isCorrect;

        return $this;
    }
}
