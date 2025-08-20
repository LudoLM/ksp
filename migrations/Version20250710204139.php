<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250710204139 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cours_week_type (id INT AUTO_INCREMENT NOT NULL, type_cours_id INT NOT NULL, week_type_id INT NOT NULL, duree INT NOT NULL, nb_inscription_max INT NOT NULL, has_priority TINYINT(1) NOT NULL, has_limit_of_one_cours_per_week TINYINT(1) NOT NULL, special_note VARCHAR(255) DEFAULT NULL, day_selected INT NOT NULL, time_selected TIME NOT NULL, INDEX IDX_BE01625EB3305F4C (type_cours_id), INDEX IDX_BE01625E375AF6A4 (week_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE week_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cours_week_type ADD CONSTRAINT FK_BE01625EB3305F4C FOREIGN KEY (type_cours_id) REFERENCES type_cours (id)');
        $this->addSql('ALTER TABLE cours_week_type ADD CONSTRAINT FK_BE01625E375AF6A4 FOREIGN KEY (week_type_id) REFERENCES week_type (id)');
        $this->addSql('ALTER TABLE cours CHANGE special_note special_note VARCHAR(255) DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE launched_at launched_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cours_week_type DROP FOREIGN KEY FK_BE01625EB3305F4C');
        $this->addSql('ALTER TABLE cours_week_type DROP FOREIGN KEY FK_BE01625E375AF6A4');
        $this->addSql('DROP TABLE cours_week_type');
        $this->addSql('DROP TABLE week_type');
        $this->addSql('ALTER TABLE cours CHANGE special_note special_note VARCHAR(255) NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE launched_at launched_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
