<?php

namespace Quiz\Domain;

use Quiz\ORM\Scheme\Attribute\Identificator;
use Quiz\ORM\Scheme\Attribute\ChildRelation;
use Quiz\ORM\Scheme\Attribute\Unique;
use Quiz\ORM\Traits\Timestampable;

class Quiz
{
    use Timestampable;

    #[Identificator()]
    protected ?int $id = null;

    #[Unique()]
    protected string $name = 'not-set';

    protected int $version = 1;

    /** @var Question[] */
    #[ChildRelation(Question::class, 'id', 'quiz_id')]
    protected array $questions = [];

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Question[]
     *
     * @psalm-return array<array-key, Question>
     */
    public function getQuestions(): array
    {
        return $this->questions;
    }

    /**
     * @return int
     */
    public function availableQuestions(): int
    {
        $notEmpty = 0;

        foreach ($this->getQuestions() as $question) {
            if (!empty($question->getFirstAnswer())) {
                $notEmpty++;
            }
        }

        return $notEmpty;
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param int $version
     *
     * @return void
     */
    public function setVersion(int $version): void
    {
        $this->version = $version;
    }

    /**
     * @param Question $question
     *
     * @return Question
     */
    public function addQuestion(Question $question): Question
    {
        $this->questions[] = $question;

        $question->setQuiz($this);

        return $question;
    }

    /**
     * @param Question[] $questions
     */
    public function setQuestions(array $questions): self
    {
        $this->questions = $questions;

        foreach ($questions as $question) {
            $question->setQuiz($this);
        }

        return $this;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
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
     * @return bool
     */
    public function hasQuestion(Question $question): bool
    {
        foreach ($this->questions as $ownQuestions) {
            if ($ownQuestions->getQuestion() === $question->getQuestion()) {
                return true;
            }
        }

        return false;
    }
}
