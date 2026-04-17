<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260417100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'DB structure cleanup: remove Contact.groupe FK, fix lengths, add unique/indexes, timestamps, clean Recharge';
    }

    public function up(Schema $schema): void
    {
        // 1. Contact: supprimer la FK groupe_id (redondante avec contact_groupe)
        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');
        $this->addSql('ALTER TABLE contact DROP COLUMN IF EXISTS groupe_id');
        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');

        // 2. Contact: corriger les longueurs et ajouter created_at
        $this->addSql('ALTER TABLE contact
            MODIFY nom VARCHAR(50) DEFAULT NULL,
            MODIFY postnom VARCHAR(50) DEFAULT NULL,
            MODIFY adresse VARCHAR(100) DEFAULT NULL,
            MODIFY fonction VARCHAR(100) DEFAULT NULL,
            MODIFY telephone VARCHAR(20) DEFAULT NULL
        ');
        $this->addSql('ALTER TABLE contact ADD created_at DATETIME DEFAULT NULL');

        // 3. Index sur contact.user_id
        $this->addSql('CREATE INDEX idx_contact_user ON contact (user_id)');

        // 4. contact_groupe: contrainte unique (contact_id, groupe_id)
        $this->addSql('ALTER TABLE contact_groupe ADD UNIQUE INDEX uniq_contact_groupe (contact_id, groupe_id)');

        // 5. Historique: message en TEXT + index composite (user_id, date)
        $this->addSql('ALTER TABLE historique MODIFY message LONGTEXT DEFAULT NULL');
        $this->addSql('CREATE INDEX idx_historique_user_date ON historique (user_id, date)');

        // 6. User: Email unique + longueur 180
        $this->addSql('ALTER TABLE `user` MODIFY Email VARCHAR(180) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON `user` (Email)');
        $this->addSql('CREATE INDEX idx_user_email ON `user` (Email)');

        // 7. User: confirmer non-nullable avec valeur par défaut false
        $this->addSql('UPDATE `user` SET confirmer = 0 WHERE confirmer IS NULL');
        $this->addSql('ALTER TABLE `user` MODIFY confirmer TINYINT(1) NOT NULL DEFAULT 0');

        // 8. Recharge: supprimer les colonnes string redondantes
        $this->addSql('ALTER TABLE recharge DROP COLUMN IF EXISTS `user`');
        $this->addSql('ALTER TABLE recharge DROP COLUMN IF EXISTS client');

        // 9. Timestamps sur groupe, organisation, templatesms
        $this->addSql('ALTER TABLE groupe ADD created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE organisation ADD created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE templatesms ADD created_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');

        $this->addSql('ALTER TABLE contact ADD groupe_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contact
            MODIFY nom VARCHAR(10) DEFAULT NULL,
            MODIFY postnom VARCHAR(10) DEFAULT NULL,
            MODIFY adresse VARCHAR(50) DEFAULT NULL,
            MODIFY fonction VARCHAR(50) DEFAULT NULL,
            MODIFY telephone VARCHAR(15) DEFAULT NULL
        ');
        $this->addSql('ALTER TABLE contact DROP COLUMN created_at');
        $this->addSql('DROP INDEX idx_contact_user ON contact');

        $this->addSql('DROP INDEX uniq_contact_groupe ON contact_groupe');

        $this->addSql('ALTER TABLE historique MODIFY message VARCHAR(200) DEFAULT NULL');
        $this->addSql('DROP INDEX idx_historique_user_date ON historique');

        $this->addSql('ALTER TABLE `user` MODIFY Email VARCHAR(50) DEFAULT NULL');
        $this->addSql('DROP INDEX UNIQ_IDENTIFIER_EMAIL ON `user`');
        $this->addSql('DROP INDEX idx_user_email ON `user`');
        $this->addSql('ALTER TABLE `user` MODIFY confirmer TINYINT(1) DEFAULT NULL');

        $this->addSql('ALTER TABLE recharge ADD `user` VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE recharge ADD client VARCHAR(20) DEFAULT NULL');

        $this->addSql('ALTER TABLE groupe DROP COLUMN created_at');
        $this->addSql('ALTER TABLE organisation DROP COLUMN created_at');
        $this->addSql('ALTER TABLE templatesms DROP COLUMN created_at');

        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');
    }
}
