<?php


namespace Quiz;

class Quiz
{
    protected int  $id = 0;
    protected string  $name = 'not-set';
    protected string|int  $version = 1;

    /** @var Question[] */
    protected array $questions = [];

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

    public function availableQuestions(): int
    {
        $notEmpty = 0;
        foreach ($this->questions() as $question) {
            if (!empty($question->getAnswer())) {
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
        return $question;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function version(): int
    {
        $version = $this->version;
        if (is_string($version)){
            $version = (int)trim(str_replace('.', '', $version), '0');
        }
        return $version;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }
}
