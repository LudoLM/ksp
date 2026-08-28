<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260827204739 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE certificat_medical (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, uploaded_by_id INT DEFAULT NULL, certificate_filename VARCHAR(255) NOT NULL, status VARCHAR(20) NOT NULL, uploaded_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', valid_until DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', rejection_reason LONGTEXT DEFAULT NULL, INDEX IDX_3C705FDBA76ED395 (user_id), INDEX IDX_3C705FDBA2B28FE8 (uploaded_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE certificat_medical ADD CONSTRAINT FK_3C705FDBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE certificat_medical ADD CONSTRAINT FK_3C705FDBA2B28FE8 FOREIGN KEY (uploaded_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX idx_user_is_deleted ON user (is_deleted)');
        $this->addSql('CREATE INDEX idx_user_archived_at ON user (archived_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE certificat_medical DROP FOREIGN KEY FK_3C705FDBA76ED395');
        $this->addSql('ALTER TABLE certificat_medical DROP FOREIGN KEY FK_3C705FDBA2B28FE8');
        $this->addSql('DROP TABLE certificat_medical');
        $this->addSql('DROP INDEX idx_user_is_deleted ON user');
        $this->addSql('DROP INDEX idx_user_archived_at ON user');
    }
}
