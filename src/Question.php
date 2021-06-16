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

    public function content(): string
    {
        return $this->content;
    }

    public function response(): array
    {
        return $this->responses;
    }

    public function explanation(): string
    {
        return $this->explanation;
    }

    public function choices(): array
    {
        return $this->choices;
    }

    public function randomize(): array
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
        return in_array($this->answer, $this->responses);
    }

}