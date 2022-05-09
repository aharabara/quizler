<?php

namespace Quiz\Adapter;

use Quiz\ORM\Builder\SchemeBuilder\ColumnDefinition;
use Quiz\ORM\Builder\TableDefinitionExtractor;
use Quiz\ORM\StorageDriver\DBStorageDriver;
use TeamTNT\TNTSearch\TNTSearch;

class TNTSearchAdapter
{
    private TNTSearch $search;
    private TableDefinitionExtractor $tableDefinitionExtractor;
    private DBStorageDriver $storageDriver;

    public function __construct()
    {
        $this->storageDriver = new DBStorageDriver();
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

        $fields = array_map(function (ColumnDefinition $columnDef){
            if ($columnDef->isSearchable() || $columnDef->isIdentityField()){
                return $columnDef->getName();
            }
            return null;
        }, $tableDef->getColumns());

        $fields = array_filter($fields);


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

        $result = $this->storageDriver->queryAll("SELECT * FROM {$tableDef->getName()} WHERE id IN ($ids)");
        return array_column($result, 'content', 'id');
    }

}