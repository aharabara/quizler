<?php

namespace Quiz\Console\Command;

use Quiz\ORM\StorageDriver\DBStorageDriver;
use Quiz\ORM\StorageDriver\YamlStorageDriver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends Command
{
    /* the name of the command (the part after "bin/console")*/
    protected static $defaultName = 'export';

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
        foreach ($dbDriver->getList() as $name){
            $output->writeln("Exporting $name.");
            $fileDriver->save($dbDriver->loadBy('name', $name), $input->getOption('force'));
        }
        return Command::SUCCESS;
    }

}
