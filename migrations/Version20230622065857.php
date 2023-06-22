<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230622065857 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER INDEX uniq_b6f7494e853cd175b6f7494e RENAME TO UNIQ_B6F7494E853CD1751D775834');
        $this->addSql('DROP INDEX uniq_a412fa925e237e06');
        $this->addSql('ALTER TABLE quiz ADD answered INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE quiz ADD total INT NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE quiz DROP answered');
        $this->addSql('ALTER TABLE quiz DROP total');
        $this->addSql('CREATE UNIQUE INDEX uniq_a412fa925e237e06 ON quiz (value)');
        $this->addSql('ALTER INDEX uniq_b6f7494e853cd1751d775834 RENAME TO uniq_b6f7494e853cd175b6f7494e');
    }
}
