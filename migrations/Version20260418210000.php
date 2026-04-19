<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260418210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update v_contactgroupe view: remove organisation join (groupe is now linked to user, not organisation)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE OR REPLACE VIEW v_contactgroupe AS
            SELECT
                c.id,
                c.telephone,
                c.nom,
                c.postnom,
                c.adresse,
                c.fonction,
                g.designation AS groupe,
                c.user_id AS user
            FROM contact c
            LEFT JOIN contact_groupe cg ON c.id = cg.contact_id
            LEFT JOIN groupe g ON g.id = cg.groupe_id
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("CREATE OR REPLACE VIEW v_contactgroupe AS
            SELECT
                c.id,
                c.telephone,
                c.nom,
                c.postnom,
                c.adresse,
                c.fonction,
                g.designation AS groupe,
                o.designation AS organisation,
                c.user_id AS user
            FROM contact c
            LEFT JOIN contact_groupe cg ON c.id = cg.contact_id
            LEFT JOIN groupe g ON g.id = cg.groupe_id
            LEFT JOIN organisation o ON o.id = g.organisation_id
        ");
    }
}
