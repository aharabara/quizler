<?php

namespace Quiz\Console\Command;

use Quiz\Console\OutputStyle\QuizStyle;
use Quiz\ConsoleKernel;
use Quiz\ORM\Repository\DatabaseRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AnswerStatsCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'stats';

    public function __construct(protected ConsoleKernel $kernel, string $name = null)
    {
        parent::__construct($name);
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setDescription("...");
    }

    /**
     * @return int
     *
     * @psalm-return 0|1
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repository = new DatabaseRepository($this->kernel->getDatabasePath());
        $style = new QuizStyle($input, $output);

        $stats = $repository->getStats();

        $style->table(array_keys(reset($stats)), $stats);
        return Command::SUCCESS;
    }
}
