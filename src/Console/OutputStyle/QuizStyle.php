<?php

namespace Quiz\Console\OutputStyle;

use Symfony\Component\Console\Style\SymfonyStyle;

class QuizStyle extends SymfonyStyle
{
    /**
     * @return void
     */
    public function clear(): void
    {
        $this->getErrorOutput()->write("\033\143");
    }
}
