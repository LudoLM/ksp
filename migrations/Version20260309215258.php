<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260309215258 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD is_archived TINYINT(1) NOT NULL, ADD archived_at DATETIME DEFAULT NULL, ADD is_deleted TINYINT(1) NOT NULL, ADD deleted_at DATETIME DEFAULT NULL, ADD anonymised_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE INDEX idx_user_is_archived ON user (is_archived)');
        $this->addSql('CREATE INDEX idx_user_anonymised_at ON user (anonymised_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_user_is_archived ON user');
        $this->addSql('DROP INDEX idx_user_anonymised_at ON user');
        $this->addSql('ALTER TABLE user DROP is_archived, DROP archived_at, DROP is_deleted, DROP deleted_at, DROP anonymised_at');
    }
}
