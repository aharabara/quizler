<?php


namespace Quiz;

class Question
{
    protected string $content;
    protected array $response = [];
    protected array $choices = [];
    protected string $explanation = '';

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
        if ($this->isGuessQuestion()) {
            $this->answer = $callback($this->content, []);
        } else {
            $choices = $this->randomize();
            $selected = $callback($this->content, $choices);
            $this->answer = array_search($choices[$selected], $this->choices);
        }
    }

    public function answerIsCorrect(): bool
    {
        if ($this->isGuessQuestion()) {
            return $this->answer;
        }
        return in_array($this->answer, array_map('intval', $this->response));
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
    public function setResponse(array $response): self
    {
        $this->response = $response;
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

    public function isGuessQuestion(): bool
    {
        /** @fixme split into ChoiceQuestion and GuessQuestion */
        return empty($this->response) && empty($this->choices);
    }
}
