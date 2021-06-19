<?php


namespace Quiz;

class Question
{
    protected string $content;
    protected array $responses;
    protected array $choices;
    protected string $explanation;

    /* result that will be saved after answer() method */
    protected int $answer;

    public function explanation(): string
    {
        return $this->explanation;
    }

    /**
     * @return array|false
     *
     * @psalm-return array<string, mixed>|false
     */
    public function randomize()
    {
        $values = shuffle_assoc($this->choices);
        $keys = array_slice(range("a", "z"), 0, count($values));

        return array_combine($keys, $values);
    }

    public function answer(callable $callback): void
    {
        $choices = $this->randomize();
        $selected = $callback($this->content, $choices);
        $this->answer = array_search($choices[$selected], $this->choices);
    }

    public function answerIsCorrect(): bool
    {
        return in_array($this->answer, array_map('intval', $this->responses));
    }

    /**
     * @return static
     */
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return static
     */
    public function setResponses(array $responses): self
    {
        $this->responses = $responses;
        return $this;
    }

    /**
     * @return static
     */
    public function setChoices(array $choices): self
    {
        $this->choices = $choices;
        return $this;
    }

    /**
     * @return static
     */
    public function setExplanation(string $explanation): self
    {
        $this->explanation = $explanation;
        return $this;
    }
}
