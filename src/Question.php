<?php


namespace Quiz;

class Question
{
    protected int $id;
    protected string $question;
    protected string $answer = '';
    protected string $tip = '';

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setTip(string $tip): void
    {
        $this->tip = $tip;
    }

    public function setQuestion(string $question): self
    {
        $this->question = $question;
        return $this;
    }

    public function setAnswer(string $answer): self
    {
        $this->answer = $answer;
        return $this;
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function getTip(): string
    {
        return $this->tip;
    }

    public function getAnswer(): string
    {
        return $this->answer;
    }

    public function getId()
    {
        return $this->id;
    }
}
