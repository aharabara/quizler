<?php

namespace Quiz\Command;

use Quiz\StorageDriver\DBStorageDriver;
use Quiz\StorageDriver\YamlStorageDriver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends Command
{
    /* the name of the command (the part after "bin/console")*/
    protected static $defaultName = 'export';
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
        $fileDriver = new YamlStorageDriver();
        $dbDriver = new DBStorageDriver();
        foreach ($dbDriver->getList() as $name){
            $output->writeln("Exporting $name.");
            $fileDriver->save($dbDriver->loadBy('name', $name));
        }
        return Command::SUCCESS;
    }

}
