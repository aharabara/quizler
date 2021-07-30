<?php


namespace Quiz;

use Symfony\Component\Serializer\Annotation\DiscriminatorMap;

/**
 * @DiscriminatorMap(typeProperty="type", mapping={
 *    "choice"="\Quiz\Question\ChoiceQuestion",
 *    "guess"="\Quiz\Question\GuessQuestion",
 *    "snippet-guess"="\Quiz\Question\SnippetGuessQuestion"
 * })
 */
abstract class Question
{
    protected string $type;
    protected string $content;
    protected string $explanation = '';

    public abstract function answer(callable $callback): void;

    public abstract function answerIsCorrect(): bool;

    /**
     * @return static
     */
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return static
     */
    public function setExplanation(string $explanation): self
    {
        $this->explanation = $explanation;
        return $this;
    }

    public function explanation(): string
    {
        return $this->explanation;
    }
}
