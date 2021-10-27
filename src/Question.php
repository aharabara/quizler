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
    /* @todo $question should not be aware of how it should be responded, and also shouldn't keep answer on itself*/

    /* @fixme move to a separated class that will keep answers in Question=>Answer relation*/
    public abstract function answer(callable $callback): void;

    /* @fixme maybe replace with $report->answerToQuestionIsCorrect($question) ?*/
    public abstract function answerIsCorrect(): bool;

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getType(): string
    {
        return $this->type;
    }

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
