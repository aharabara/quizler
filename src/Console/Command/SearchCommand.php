<?php

namespace Quiz\Console\Command;

use Quiz\Adapter\TNTSearchAdapter;
use Quiz\Domain\Answer;
use Quiz\Domain\Question;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SearchCommand extends Command
{
    protected static $defaultName = 'search';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription('Search through answers');
        $this->addArgument('model', InputArgument::OPTIONAL, 'which model to search through', 'answer');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        $adapter = new TNTSearchAdapter();

        $entityClass = match ($input->getArgument('model')) {
            'answer' => Answer::class,
            'question' => Question::class,
            default => null
        };

        if (!$entityClass) {
            $style->error('Entity not supported');

            return Command::FAILURE;
        }

        $adapter->indexTable($entityClass);

        system('stty cbreak');

        while (true) {
            $query = '';

            if ($char = fread(STDIN, 1)) {
                if ($char === "\x7F") {
                    $query = substr($query, 0, -1);
                } elseif (\ctype_print($char)) {
                    $query .= $char;
                }

                $rows = $adapter->search($entityClass, trim($query, " "));

                $output->writeln("<info>-----</info>");

                print "\033c";

                $style->writeln("<info>query:</info> ".$query);

                $words = array_filter(explode(" ", $query), static fn ($word) => !in_array($word, ['and', 'or']));

                $replacement = array_map(static fn ($word) => "<comment>".trim($word, " -")."</comment>", $words);

                foreach (array_slice($rows, 0, 5) as $row) {
                    $output->writeln(" - ".str_replace($words, $replacement, $row));
                }
            }
        }

        /** TODO this part should be fixed because this return will never be used */
        return Command::SUCCESS;
    }
}
