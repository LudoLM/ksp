<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241001100356 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE historique_paiement (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, pack_id INT DEFAULT NULL, checkout_id VARCHAR(255) NOT NULL, date DATETIME NOT NULL, INDEX IDX_710402ECA76ED395 (user_id), INDEX IDX_710402EC1919B217 (pack_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE historique_paiement ADD CONSTRAINT FK_710402ECA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE historique_paiement ADD CONSTRAINT FK_710402EC1919B217 FOREIGN KEY (pack_id) REFERENCES pack (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE historique_paiement DROP FOREIGN KEY FK_710402ECA76ED395');
        $this->addSql('ALTER TABLE historique_paiement DROP FOREIGN KEY FK_710402EC1919B217');
        $this->addSql('DROP TABLE historique_paiement');
    }
}
