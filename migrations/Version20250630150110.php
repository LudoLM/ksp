<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250630150110 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE cours
            ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '(DC2Type:datetime_immutable)',
            ADD launched_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
            ADD updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
            ADD has_priority TINYINT(1) NOT NULL,
            ADD has_limit_of_one_cours_per_week TINYINT(1) NOT NULL,
            CHANGE duree duree INT NOT NULL,
            CHANGE special_note special_note VARCHAR(255) NOT NULL,
            CHANGE nb_inscription_max nb_inscription_max INT NOT NULL
        ");
        $this->addSql('ALTER TABLE user ADD is_prioritized TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cours
            DROP created_at,
            DROP launched_at,
            DROP updated_at,
            DROP has_priority,
            DROP has_limit_of_one_cours_per_week,
            CHANGE duree duree INT DEFAULT NULL,
            CHANGE special_note special_note VARCHAR(255) DEFAULT NULL,
            CHANGE nb_inscription_max nb_inscription_max INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user DROP is_prioritized');
    }
}
