<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251129163819 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX IDX_710402ECAA9E377A ON historique_paiement (date)');
        $this->addSql('CREATE INDEX idx_date_user ON historique_paiement (date, user_id)');
        $this->addSql('ALTER TABLE users_cours CHANGE unsubscribed_at unsubscribed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE INDEX idx_created_user ON users_cours (created_at, user_id)');
        $this->addSql('CREATE INDEX idx_unsubscribed_user ON users_cours (unsubscribed_at, user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_710402ECAA9E377A ON historique_paiement');
        $this->addSql('DROP INDEX idx_date_user ON historique_paiement');
        $this->addSql('DROP INDEX idx_created_user ON users_cours');
        $this->addSql('DROP INDEX idx_unsubscribed_user ON users_cours');
        $this->addSql('ALTER TABLE users_cours CHANGE unsubscribed_at unsubscribed_at DATETIME DEFAULT NULL');
    }
}
