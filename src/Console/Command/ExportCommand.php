<?php

namespace Quiz\Console\Command;

use Quiz\ConsoleKernel;
use Quiz\Domain\Quiz;
use Quiz\ORM\Repository\DatabaseRepository;
use Quiz\ORM\Repository\YamlRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends Command
{
    /* the name of the command (the part after "bin/console")*/
    protected static $defaultName = 'export';

    public function __construct(protected ConsoleKernel $kernel, string $name = null)
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
        $fileRepo = new YamlRepository($this->kernel->getFileReplicaPath());
        $dbRepo = new DatabaseRepository($this->kernel->getDatabasePath());
        foreach ($dbRepo->getList() as $name){
            $output->writeln("Exporting $name.");
            $fileRepo->save($dbRepo->loadBy(Quiz::class, ['name' => $name]), $input->getOption('force'));
        }
        return Command::SUCCESS;
    }

}
