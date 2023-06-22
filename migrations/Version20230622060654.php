<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230622060654 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quiz RENAME COLUMN name TO value');
        $this->addSql('ALTER TABLE question RENAME COLUMN question TO value');
        $this->addSql('ALTER TABLE answer RENAME COLUMN content TO value');
        $this->addSql('ALTER TABLE answer RENAME COLUMN is_correct TO correct');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A412FA921D775834 ON quiz (value)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_A412FA921D775834');
        $this->addSql('ALTER TABLE answer RENAME COLUMN value TO content');
        $this->addSql('ALTER TABLE answer RENAME COLUMN correct TO is_correct');
        $this->addSql('ALTER TABLE question RENAME COLUMN value TO question');
        $this->addSql('ALTER TABLE quiz RENAME COLUMN value TO name');
    }
}
