<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260828152015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create cours_wishes_form table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE cours_wishes_form (
              id INT AUTO_INCREMENT NOT NULL,
              user_id INT DEFAULT NULL,
              creneau_primaire_id INT DEFAULT NULL,
              creneau_secondaire_id INT DEFAULT NULL,
              pack_souhaite_id INT NOT NULL,
              validated_by_id INT DEFAULT NULL,
              email VARCHAR(180) NOT NULL,
              saison VARCHAR(20) NOT NULL,
              mode_reglement VARCHAR(20) NOT NULL,
              status VARCHAR(20) NOT NULL,
              correction_reason LONGTEXT DEFAULT NULL,
              submitted_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              validated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
              filled_by_admin TINYINT(1) NOT NULL,
              INDEX IDX_844C9BC2A76ED395 (user_id),
              INDEX IDX_844C9BC26AD941FE (creneau_primaire_id),
              INDEX IDX_844C9BC2293C92EC (creneau_secondaire_id),
              INDEX IDX_844C9BC2FAF77E71 (pack_souhaite_id),
              INDEX IDX_844C9BC2C69DE5E5 (validated_by_id),
              UNIQUE INDEX uniq_wishes_form_email_saison (email, saison),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              cours_wishes_form
            ADD
              CONSTRAINT FK_844C9BC2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              cours_wishes_form
            ADD
              CONSTRAINT FK_844C9BC26AD941FE FOREIGN KEY (creneau_primaire_id) REFERENCES cours_week_type (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              cours_wishes_form
            ADD
              CONSTRAINT FK_844C9BC2293C92EC FOREIGN KEY (creneau_secondaire_id) REFERENCES cours_week_type (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              cours_wishes_form
            ADD
              CONSTRAINT FK_844C9BC2FAF77E71 FOREIGN KEY (pack_souhaite_id) REFERENCES pack (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              cours_wishes_form
            ADD
              CONSTRAINT FK_844C9BC2C69DE5E5 FOREIGN KEY (validated_by_id) REFERENCES user (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cours_wishes_form DROP FOREIGN KEY FK_844C9BC2A76ED395');
        $this->addSql('ALTER TABLE cours_wishes_form DROP FOREIGN KEY FK_844C9BC26AD941FE');
        $this->addSql('ALTER TABLE cours_wishes_form DROP FOREIGN KEY FK_844C9BC2293C92EC');
        $this->addSql('ALTER TABLE cours_wishes_form DROP FOREIGN KEY FK_844C9BC2FAF77E71');
        $this->addSql('ALTER TABLE cours_wishes_form DROP FOREIGN KEY FK_844C9BC2C69DE5E5');
        $this->addSql('DROP TABLE cours_wishes_form');
    }
}
