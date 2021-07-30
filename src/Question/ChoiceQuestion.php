<?php


namespace Quiz\Question;

use Quiz\Question;

class ChoiceQuestion extends Question
{
    protected string $type = 'choice';
    protected array $response = [];
    protected array $choices = [];

    /* result that will be saved after answer() method */
    protected int $answer;

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
        return in_array($this->answer, array_map('intval', $this->response));
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
}
