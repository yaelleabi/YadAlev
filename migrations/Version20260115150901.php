<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260115150901 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE family_event_request (id INT AUTO_INCREMENT NOT NULL, family_id INT NOT NULL, event_id INT NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_30C9B41CC35E566A (family_id), INDEX IDX_30C9B41C71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE family_event_request ADD CONSTRAINT FK_30C9B41CC35E566A FOREIGN KEY (family_id) REFERENCES family (id)');
        $this->addSql('ALTER TABLE family_event_request ADD CONSTRAINT FK_30C9B41C71F7E88B FOREIGN KEY (event_id) REFERENCES family_event (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE family_event_request DROP FOREIGN KEY FK_30C9B41CC35E566A');
        $this->addSql('ALTER TABLE family_event_request DROP FOREIGN KEY FK_30C9B41C71F7E88B');
        $this->addSql('DROP TABLE family_event_request');
    }
}
