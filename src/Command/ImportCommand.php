<?php

namespace Quiz\Command;

use Quiz\StorageDriver\DBStorageDriver;
use Quiz\StorageDriver\YamlStorageDriver;
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
        $fileDriver = new YamlStorageDriver();
        $dbDriver = new DBStorageDriver();
        foreach ($fileDriver->getList() as $name){
            $output->writeln("Exporting $name.");
            $dbDriver->save($fileDriver->loadBy('name', $name), $input->getOption('force'));
        }
        return Command::SUCCESS;
    }

}
