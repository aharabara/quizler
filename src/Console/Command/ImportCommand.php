<?php

namespace Quiz\Console\Command;

use Quiz\Domain\Quiz;
use Quiz\ORM\Repository\DatabaseRepository;
use Quiz\ORM\Repository\YamlRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends Command
{
    /* the name of the command (the part after "bin/console")*/
    protected static $defaultName = 'import';

    public function __construct(string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->addOption('force', 'f', InputOption::VALUE_NEGATABLE, '', false);
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fileDriver = new YamlRepository();
        $dbDriver = new DatabaseRepository();
        foreach ($fileDriver->getList() as $name){
            $output->writeln("Exporting $name.");
            $dbDriver->save($fileDriver->loadBy(Quiz::class, ['name' => $name]), $input->getOption('force'));
        }
        return Command::SUCCESS;
    }

}
