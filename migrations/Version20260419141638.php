<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260419141638 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Clean orphaned rows, add missing FK constraints, drop compte_sms';
    }

    public function up(Schema $schema): void
    {
        // --- Nettoyage des lignes orphelines (user_id invalide) ---
        $this->addSql('DELETE FROM commande       WHERE user_id IS NOT NULL AND user_id NOT IN (SELECT id FROM user)');
        $this->addSql('DELETE FROM contact        WHERE user_id IS NOT NULL AND user_id NOT IN (SELECT id FROM user)');
        $this->addSql('DELETE FROM historique     WHERE user_id IS NOT NULL AND user_id NOT IN (SELECT id FROM user)');
        $this->addSql('DELETE FROM organisation   WHERE user_id IS NOT NULL AND user_id NOT IN (SELECT id FROM user)');
        $this->addSql('DELETE FROM pwd_resetting  WHERE user_id IS NOT NULL AND user_id NOT IN (SELECT id FROM user)');
        $this->addSql('DELETE FROM recharge       WHERE utilisateur_id IS NOT NULL AND utilisateur_id NOT IN (SELECT id FROM user)');
        $this->addSql('DELETE FROM recharge       WHERE clientid_id IS NOT NULL AND clientid_id NOT IN (SELECT id FROM user)');
        $this->addSql('DELETE FROM templatesms    WHERE user_id IS NOT NULL AND user_id NOT IN (SELECT id FROM user)');

        // contact_groupe : orphelins via contact ou groupe supprimés
        $this->addSql('DELETE FROM contact_groupe WHERE contact_id NOT IN (SELECT id FROM contact)');
        $this->addSql('DELETE FROM contact_groupe WHERE groupe_id  NOT IN (SELECT id FROM groupe)');

        // --- Drop index user email si présent ---
        $this->addSql('DROP INDEX IF EXISTS idx_user_email ON user');

        // --- FK constraints (drop si existante, puis recréer) ---
        $fks = [
            ['commande',       'FK_6EEAA67DA76ED395',  'user_id',        'user',              'id'],
            ['contact_groupe', 'FK_942BBBA0E7A1254A',  'contact_id',     'contact',           'id'],
            ['contact_groupe', 'FK_942BBBA07A45358C',  'groupe_id',      'groupe',            'id'],
            ['historique',     'FK_EDBFD5ECA76ED395',  'user_id',        'user',              'id'],
            ['organisation',   'FK_E6E132B4C54C8C93',  'type_id',        'type_organisation', 'id'],
            ['organisation',   'FK_E6E132B4A76ED395',  'user_id',        'user',              'id'],
            ['pwd_resetting',  'FK_9064868A76ED395',   'user_id',        'user',              'id'],
            ['recharge',       'FK_B6702F51FB88E14F',  'utilisateur_id', 'user',              'id'],
            ['recharge',       'FK_B6702F51F3FD2D2E',  'clientid_id',    'user',              'id'],
            ['templatesms',    'FK_D332F0FFA76ED395',  'user_id',        'user',              'id'],
        ];

        foreach ($fks as [$table, $fkName, $col, $ref, $refCol]) {
            $this->addSql("ALTER TABLE `{$table}` DROP FOREIGN KEY IF EXISTS `{$fkName}`");
            $this->addSql("ALTER TABLE `{$table}` ADD CONSTRAINT `{$fkName}` FOREIGN KEY (`{$col}`) REFERENCES `{$ref}` (`{$refCol}`)");
        }

        // contact : recréer FK user_id
        $this->addSql('ALTER TABLE contact DROP FOREIGN KEY IF EXISTS FK_4C62E638A76ED395');
        $this->addSql('DROP INDEX IF EXISTS IDX_4C62E638A76ED395 ON contact');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E638A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE compte_sms (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, sms_total INT DEFAULT NULL, sms_used INT DEFAULT NULL, is_active TINYINT(1) DEFAULT NULL, INDEX IDX_E0E1B74AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE compte_sms ADD CONSTRAINT FK_E0E1B74AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }
}
