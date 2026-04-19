<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260418201121 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Supprimer les groupes dont user_id est NULL ou ne référence aucun utilisateur existant
        $this->addSql('DELETE FROM contact_groupe WHERE groupe_id IN (SELECT id FROM groupe WHERE user_id IS NULL OR user_id NOT IN (SELECT id FROM user))');
        $this->addSql('DELETE FROM groupe WHERE user_id IS NULL OR user_id NOT IN (SELECT id FROM user)');

        $this->addSql('ALTER TABLE groupe ADD CONSTRAINT FK_4B98C21A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_4B98C21A76ED395 ON groupe (user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE groupe DROP FOREIGN KEY FK_4B98C21A76ED395');
        $this->addSql('DROP INDEX IDX_4B98C21A76ED395 ON groupe');
    }
}
