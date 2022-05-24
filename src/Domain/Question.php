<?php


namespace Quiz\Domain;

use Quiz\ORM\Scheme\Attribute\Identificator;
use Quiz\ORM\Scheme\Attribute\ParentRelation;
use Quiz\ORM\Scheme\Attribute\ChildRelation;
use Quiz\ORM\Scheme\Attribute\Searchable;
use Quiz\ORM\Scheme\Attribute\Unique;
use Quiz\ORM\Traits\Timestampable;
use Symfony\Component\Serializer\Annotation\Ignore;

class Question
{
    #[Identificator()]
    protected int $id;

    #[Searchable]
    #[Unique]
    protected string $question;

    #[ChildRelation(Answer::class, 'id', 'question_id')]
    protected array $answers = [];
    protected ?string $tip = null;

    #[Ignore]
    #[ParentRelation(Quiz::class, 'quiz_id', 'id')]
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
