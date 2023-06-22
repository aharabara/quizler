<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230622050806 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE question RENAME COLUMN content TO question');
        $this->addSql('ALTER TABLE quiz RENAME COLUMN title TO name');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A412FA925E237E06 ON quiz (name)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_A412FA925E237E06');
        $this->addSql('ALTER TABLE quiz RENAME COLUMN name TO title');
        $this->addSql('ALTER TABLE question RENAME COLUMN question TO content');
    }
}
