<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251110094906 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE aid_request ADD family_id INT NOT NULL');
        $this->addSql('ALTER TABLE aid_request ADD CONSTRAINT FK_FC5DB68DC35E566A FOREIGN KEY (family_id) REFERENCES family (id)');
        $this->addSql('CREATE INDEX IDX_FC5DB68DC35E566A ON aid_request (family_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE aid_request DROP FOREIGN KEY FK_FC5DB68DC35E566A');
        $this->addSql('DROP INDEX IDX_FC5DB68DC35E566A ON aid_request');
        $this->addSql('ALTER TABLE aid_request DROP family_id');
    }
}
