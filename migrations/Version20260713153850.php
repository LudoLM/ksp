<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260713153850 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE certificat_medical (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, certificate_filename VARCHAR(255) NOT NULL, status VARCHAR(20) NOT NULL, uploaded_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', valid_until DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_3C705FDBA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE certificat_medical ADD CONSTRAINT FK_3C705FDBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX idx_user_is_deleted ON user (is_deleted)');
        $this->addSql('CREATE INDEX idx_user_archived_at ON user (archived_at)');
        $this->addSql('ALTER TABLE certificat_medical ADD rejection_reason LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE certificat_medical DROP FOREIGN KEY FK_3C705FDBA76ED395');
        $this->addSql('DROP TABLE certificat_medical');
        $this->addSql('DROP INDEX idx_user_is_deleted ON user');
        $this->addSql('DROP INDEX idx_user_archived_at ON user');
        $this->addSql('ALTER TABLE certificat_medical DROP rejection_reason');
    }
}
