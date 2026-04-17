<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260417000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add confirmation_token to user table for secure email confirmation';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD confirmation_token VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP COLUMN confirmation_token');
    }
}
