<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251222094315 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE family_event_family (family_event_id INT NOT NULL, family_id INT NOT NULL, INDEX IDX_20028394B34D03F8 (family_event_id), INDEX IDX_20028394C35E566A (family_id), PRIMARY KEY(family_event_id, family_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE volunteer_event_volunteer (volunteer_event_id INT NOT NULL, volunteer_id INT NOT NULL, INDEX IDX_DCB91CDE592BB80A (volunteer_event_id), INDEX IDX_DCB91CDE8EFAB6B1 (volunteer_id), PRIMARY KEY(volunteer_event_id, volunteer_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE family_event_family ADD CONSTRAINT FK_20028394B34D03F8 FOREIGN KEY (family_event_id) REFERENCES family_event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE family_event_family ADD CONSTRAINT FK_20028394C35E566A FOREIGN KEY (family_id) REFERENCES family (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE volunteer_event_volunteer ADD CONSTRAINT FK_DCB91CDE592BB80A FOREIGN KEY (volunteer_event_id) REFERENCES volunteer_event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE volunteer_event_volunteer ADD CONSTRAINT FK_DCB91CDE8EFAB6B1 FOREIGN KEY (volunteer_id) REFERENCES volunteer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE family_aid_list DROP FOREIGN KEY FK_E95DAF5BFA642F99');
        $this->addSql('ALTER TABLE family_aid_list DROP FOREIGN KEY FK_E95DAF5BC35E566A');
        $this->addSql('DROP TABLE aid_list');
        $this->addSql('DROP TABLE family_aid_list');
        $this->addSql('ALTER TABLE event ADD title VARCHAR(255) NOT NULL, ADD description LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE family_event ADD quantity INT NOT NULL, DROP assigned_families');
        $this->addSql('ALTER TABLE volunteer_event DROP assigned_volunteers');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE aid_list (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE family_aid_list (family_id INT NOT NULL, aid_list_id INT NOT NULL, INDEX IDX_E95DAF5BC35E566A (family_id), INDEX IDX_E95DAF5BFA642F99 (aid_list_id), PRIMARY KEY(family_id, aid_list_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE family_aid_list ADD CONSTRAINT FK_E95DAF5BFA642F99 FOREIGN KEY (aid_list_id) REFERENCES aid_list (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE family_aid_list ADD CONSTRAINT FK_E95DAF5BC35E566A FOREIGN KEY (family_id) REFERENCES family (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE family_event_family DROP FOREIGN KEY FK_20028394B34D03F8');
        $this->addSql('ALTER TABLE family_event_family DROP FOREIGN KEY FK_20028394C35E566A');
        $this->addSql('ALTER TABLE volunteer_event_volunteer DROP FOREIGN KEY FK_DCB91CDE592BB80A');
        $this->addSql('ALTER TABLE volunteer_event_volunteer DROP FOREIGN KEY FK_DCB91CDE8EFAB6B1');
        $this->addSql('DROP TABLE family_event_family');
        $this->addSql('DROP TABLE volunteer_event_volunteer');
        $this->addSql('ALTER TABLE event DROP title, DROP description');
        $this->addSql('ALTER TABLE family_event ADD assigned_families LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', DROP quantity');
        $this->addSql('ALTER TABLE volunteer_event ADD assigned_volunteers LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\'');
    }
}
