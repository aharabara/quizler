<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231005070928 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON "user" (username)');
        $this->addSql("INSERT INTO \"user\" (id, username, roles, password) VALUES (1, 'aharabara', '[\"ROLE_USER\"]', 'TEST')");
        $this->addSql('ALTER TABLE answer ADD author_id INT NOT NULL DEFAULT 1');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A25F675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_DADD4A25F675F31B ON answer (author_id)');
        $this->addSql('ALTER TABLE question ADD author_id INT NOT NULL DEFAULT 1');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494EF675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_B6F7494EF675F31B ON question (author_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer DROP CONSTRAINT FK_DADD4A25F675F31B');
        $this->addSql('ALTER TABLE question DROP CONSTRAINT FK_B6F7494EF675F31B');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP INDEX IDX_DADD4A25F675F31B');
        $this->addSql('ALTER TABLE answer DROP author_id');
        $this->addSql('DROP INDEX IDX_B6F7494EF675F31B');
        $this->addSql('ALTER TABLE question DROP author_id');
    }
}
