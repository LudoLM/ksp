<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260202205518 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX idx_cours_status_date ON cours (status_cours_id, date_cours)');
        $this->addSql('CREATE INDEX idx_users_cours_cours_waiting ON users_cours (cours_id, is_on_waiting_list)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_cours_status_date ON cours');
        $this->addSql('DROP INDEX idx_users_cours_cours_waiting ON users_cours');
    }
}
