<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231120081917 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer ALTER author_id DROP DEFAULT');
        $this->addSql('ALTER TABLE question ALTER author_id DROP DEFAULT');
        $this->addSql('DROP INDEX uniq_a412fa921d775834');
        $this->addSql('ALTER TABLE quiz ADD source VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE question ALTER author_id SET DEFAULT 1');
        $this->addSql('ALTER TABLE quiz DROP source');
        $this->addSql('CREATE UNIQUE INDEX uniq_a412fa921d775834 ON quiz (value)');
        $this->addSql('ALTER TABLE answer ALTER author_id SET DEFAULT 1');
    }
}
