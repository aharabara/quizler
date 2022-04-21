<?php


namespace Quiz;

use Quiz\Builder\SchemeBuilder\Identificator;

class Quiz
{
    #[Identificator()]
    protected ?int $id = null;
    protected string $name = 'not-set';
    protected int $version = 1;

    /** @var Question[] */
    protected array $questions = [];

    public function getVersion(): int
    {
        return $this->version;
    }

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
