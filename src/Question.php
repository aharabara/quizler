<?php


namespace Quiz;

use Quiz\Builder\SchemeBuilder\Identificator;
use Quiz\Builder\SchemeBuilder\Relation;
use Quiz\Builder\SchemeBuilder\Searchable;
use Quiz\Traits\Timestampable;
use Symfony\Component\Serializer\Annotation\Ignore;

class Question
{
    #[Identificator()]
    protected int $id;

    #[Searchable]
    protected string $question;
    protected array $answers = [];
    protected ?string $tip = null;

    #[Relation(Quiz::class)]
    #[Ignore]
    private Quiz $quiz;

    use Timestampable;

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setTip(?string $tip): void
    {
        $this->tip = $tip;
    }

    public function setQuestion(string $question): self
    {
        $this->question = $question;
        return $this;
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function getTip(): ?string
    {
        return $this->tip;
    }

    /** @Ignore */
    public function getFirstAnswer(): string
    {
        /*fixme first correct answer */
        if (empty($this->answers)) {
            return '';
        }

        return reset($this->answers)->getContent();
    }

    /**
     * @return Answer[]
     */
    public function getAnswers(): array
    {
        return $this->answers;
    }

    /**
     * @param Answer[] $answers
     * @return $this
     */
    public function setAnswers(array $answers): self
    {
        foreach ($answers as $answer) {
            $answer->setQuestion($this);
        }
        $this->answers = $answers;
        return $this;
    }


    public function getId(): int
    {
        return $this->id;
    }

    public function getQuiz(): Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(Quiz $quiz): static
    {
        $this->quiz = $quiz;
        return $this;
    }
}
