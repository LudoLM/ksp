<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260908131034 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create season_planning, season_planning_slot and their many-to-many with type_cours';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE season_planning (id INT AUTO_INCREMENT NOT NULL, saison VARCHAR(20) NOT NULL, UNIQUE INDEX uniq_season_planning_saison (saison), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE season_planning_slot (id INT AUTO_INCREMENT NOT NULL, season_planning_id INT NOT NULL, day_selected INT NOT NULL, time_selected TIME NOT NULL, INDEX IDX_7DAD75475045B97 (season_planning_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE season_planning_slot_type_cours (season_planning_slot_id INT NOT NULL, type_cours_id INT NOT NULL, INDEX IDX_6ECB033BA58B2B49 (season_planning_slot_id), INDEX IDX_6ECB033BB3305F4C (type_cours_id), PRIMARY KEY(season_planning_slot_id, type_cours_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE season_planning_slot ADD CONSTRAINT FK_7DAD75475045B97 FOREIGN KEY (season_planning_id) REFERENCES season_planning (id)');
        $this->addSql('ALTER TABLE season_planning_slot_type_cours ADD CONSTRAINT FK_6ECB033BA58B2B49 FOREIGN KEY (season_planning_slot_id) REFERENCES season_planning_slot (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE season_planning_slot_type_cours ADD CONSTRAINT FK_6ECB033BB3305F4C FOREIGN KEY (type_cours_id) REFERENCES type_cours (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE season_planning_slot DROP FOREIGN KEY FK_7DAD75475045B97');
        $this->addSql('ALTER TABLE season_planning_slot_type_cours DROP FOREIGN KEY FK_6ECB033BA58B2B49');
        $this->addSql('ALTER TABLE season_planning_slot_type_cours DROP FOREIGN KEY FK_6ECB033BB3305F4C');
        $this->addSql('DROP TABLE season_planning');
        $this->addSql('DROP TABLE season_planning_slot');
        $this->addSql('DROP TABLE season_planning_slot_type_cours');
    }
}
