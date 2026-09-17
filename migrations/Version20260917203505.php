<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260917203505 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cours_wishes_form DROP FOREIGN KEY FK_844C9BC2C69DE5E5');
        $this->addSql('DROP INDEX IDX_844C9BC2C69DE5E5 ON cours_wishes_form');
        $this->addSql('ALTER TABLE cours_wishes_form ADD registration_token_hash VARCHAR(64) DEFAULT NULL, ADD registration_token_expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD correction_token_hash VARCHAR(64) DEFAULT NULL, ADD correction_token_expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE validated_by_id reviewed_by_id INT DEFAULT NULL, CHANGE validated_at reviewed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE cours_wishes_form ADD CONSTRAINT FK_844C9BC2FC6B21F1 FOREIGN KEY (reviewed_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_844C9BC2FC6B21F1 ON cours_wishes_form (reviewed_by_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cours_wishes_form DROP FOREIGN KEY FK_844C9BC2FC6B21F1');
        $this->addSql('DROP INDEX IDX_844C9BC2FC6B21F1 ON cours_wishes_form');
        $this->addSql('ALTER TABLE cours_wishes_form ADD validated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP reviewed_at, DROP registration_token_hash, DROP registration_token_expires_at, DROP correction_token_hash, DROP correction_token_expires_at, CHANGE reviewed_by_id validated_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE cours_wishes_form ADD CONSTRAINT FK_844C9BC2C69DE5E5 FOREIGN KEY (validated_by_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_844C9BC2C69DE5E5 ON cours_wishes_form (validated_by_id)');
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74 ON user');
    }
}
