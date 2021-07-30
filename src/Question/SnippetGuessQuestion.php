<?php

namespace Quiz\Question;

use Quiz\Question;

class SnippetGuessQuestion extends GuessQuestion
{
    protected string $type = 'snippet-guess';
    protected string $snippet = '';

    /**
     * @return string
     */
    public function snippet(): string
    {
        return $this->snippet;
    }
    // fixme add posibility to do snippet questions

}
