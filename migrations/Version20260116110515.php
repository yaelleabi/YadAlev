<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260116110515 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE volunteer_event_request (id INT AUTO_INCREMENT NOT NULL, volunteer_id INT NOT NULL, event_id INT NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_2085D24E8EFAB6B1 (volunteer_id), INDEX IDX_2085D24E71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE volunteer_event_request ADD CONSTRAINT FK_2085D24E8EFAB6B1 FOREIGN KEY (volunteer_id) REFERENCES volunteer (id)');
        $this->addSql('ALTER TABLE volunteer_event_request ADD CONSTRAINT FK_2085D24E71F7E88B FOREIGN KEY (event_id) REFERENCES volunteer_event (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE volunteer_event_request DROP FOREIGN KEY FK_2085D24E8EFAB6B1');
        $this->addSql('ALTER TABLE volunteer_event_request DROP FOREIGN KEY FK_2085D24E71F7E88B');
        $this->addSql('DROP TABLE volunteer_event_request');
    }
}
