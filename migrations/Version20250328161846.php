<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250328161846 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cours CHANGE duree duree INT DEFAULT NULL, CHANGE special_note special_note VARCHAR(255) DEFAULT NULL, CHANGE nb_inscription_max nb_inscription_max INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD reset_password_token VARCHAR(255) DEFAULT NULL, CHANGE nom nom VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE users_cours CHANGE cours_id cours_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cours CHANGE duree duree INT NOT NULL, CHANGE special_note special_note VARCHAR(255) NOT NULL, CHANGE nb_inscription_max nb_inscription_max INT NOT NULL');
        $this->addSql('ALTER TABLE users_cours CHANGE cours_id cours_id INT NOT NULL');
        $this->addSql('ALTER TABLE user DROP reset_password_token, CHANGE nom nom VARCHAR(255) NOT NULL');
    }
}
