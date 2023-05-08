<?php


namespace Quiz\Domain;

use Quiz\ORM\Scheme\Attribute\Identificator;
use Quiz\ORM\Scheme\Attribute\ChildRelation;
use Quiz\ORM\Scheme\Attribute\Unique;
use Quiz\ORM\Traits\Timestampable;
use Symfony\Component\Serializer\Annotation\Groups;

class Quiz
{
    #[Identificator()]
    #[Groups(['api'])]
    protected ?int $id = null;

    #[Unique()]
    #[Groups(['api'])]
    protected string $name = 'not-set';

    #[Groups(['api'])]
    protected int $version = 1;

    use Timestampable;

    /** @var Question[] */
    #[ChildRelation(Question::class, 'id', 'quiz_id')]
    #[Groups(['api'])]
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
