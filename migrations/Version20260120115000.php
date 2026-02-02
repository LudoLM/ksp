<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration for KSP-11: Add password reset token expiration field.
 */
final class Version20260120115000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add reset_password_token_expires_at column to user table for KSP-11 security fix';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD reset_password_token_expires_at DATETIME NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP reset_password_token_expires_at');
    }
}
