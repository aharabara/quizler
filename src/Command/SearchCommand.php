<?php

namespace Quiz\Command;

use Quiz\Adapter\TNTSearchAdapter;
use Quiz\Answer;
use Quiz\Question;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SearchCommand extends Command
{
    /* the name of the command (the part after "bin/console")*/
    protected static $defaultName = 'search';

    public function __construct(string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Search through answers');
        $this->addArgument('model', InputArgument::OPTIONAL, 'which model to search through', 'answer');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        $adapter = new TNTSearchAdapter();

        $entityClass = match ($input->getArgument('model')){
            'answer' => Answer::class,
            'question' => Question::class,
            default => null
        };
        if(!$entityClass){
            $style->error('Entity not supported');
            return Command::FAILURE;
        }

        $adapter->indexTable($entityClass);
//        while($t = readline(">")){
        system('stty cbreak');
        $query = '';
        while(true){
            if($char = fread(STDIN, 1)) {
//                system('clear');
                if ($char === "\x7F"){
                    $query = substr($query, 0, strlen($query) - 1);
                }elseif(ctype_print($char)){
                    $query .= $char;
                }

                $rows = $adapter->search($entityClass, trim($query, " "));
                $output->writeln("<info>-----</info>");
                print "\033c";
                $style->writeln("<info>query:</info> ".$query);
                $words = array_filter(explode(" ", $query), fn($word) => !in_array($word, ['and', 'or']));
                $replacement = array_map(function ($word){
                    return "<comment>" . trim($word, " -") . "</comment>";
                }, $words);
                foreach (array_slice($rows, 0, 5) as $row) {
                    $output->writeln(" - ".str_replace($words, $replacement, $row));
                }
            }
        }
//        }

        return Command::SUCCESS;
    }

}
