<?php

namespace Quiz\Command;

use Quiz\StorageDriver\YamlStorageDriver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExportCommand extends Command
{
    /* the name of the command (the part after "bin/console")*/
    protected static $defaultName = 'todo';
    private YamlStorageDriver $quizLoader;

    public function __construct(string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure()
    {
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return Command::SUCCESS;
    }

}
