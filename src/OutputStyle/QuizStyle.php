<?php

namespace Quiz\OutputStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

class QuizStyle extends SymfonyStyle
{
    public function clear(): void
    {
        $this->getErrorOutput()->write(sprintf("\033\143"));
    }
}