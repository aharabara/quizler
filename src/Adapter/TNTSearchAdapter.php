<?php

namespace Quiz\Adapter;

use Quiz\ORM\Repository\DatabaseRepository;
use Quiz\ORM\Scheme\Definition\ColumnDefinition;
use Quiz\ORM\Scheme\Extractor\TableDefinitionExtractor;
use TeamTNT\TNTSearch\TNTSearch;

class TNTSearchAdapter
{
    private TNTSearch $search;
    private TableDefinitionExtractor $tableDefinitionExtractor;
    private DatabaseRepository $repository;

    public function __construct()
    {
        $this->repository = new DatabaseRepository();
        $this->tableDefinitionExtractor = new TableDefinitionExtractor();

        $this->search = new TNTSearch;
        $this->search->loadConfig([
            'driver'    => 'sqlite',
            'database'  => DB_PATH,
            'storage'   => STORAGE_FOLDER.'/indexes/',
            'stemmer'   => \TeamTNT\TNTSearch\Stemmer\PorterStemmer::class//optional
        ]);
    }

    /*fixme*/
    public function indexTable(string $class): void
    {

        $tableDef = $this->tableDefinitionExtractor->extract($class);

        $fields = $tableDef->getColumns()
            ->filter(fn (ColumnDefinition $column) => $column->isSearchable() || $column->isIdentityField())
            ->map(fn(ColumnDefinition $column) => $column->getName())
            ->toArray();


        $indexer = $this->search->createIndex("{$tableDef->getName()}.index");

        $fields = implode(',', $fields);
        $indexer->query("SELECT {$fields} FROM {$tableDef->getName()};");
        $indexer->run();
    }

    public function search(string $class, string $query): array
    {
        $tableDef = $this->tableDefinitionExtractor->extract($class);
        $this->search->asYouType = true;
        $this->search->selectIndex("{$tableDef->getName()}.index");

        $result = $this->search->searchBoolean($query);
        $ids = $result['ids'] ?? [];
        $ids = implode(',', $ids);

        $result = $this->repository->queryAll("SELECT * FROM {$tableDef->getName()} WHERE id IN ($ids)");
        return array_column($result, 'content', 'id');
    }

}