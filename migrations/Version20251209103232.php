<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251209103232 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE aid_request CHANGE adress_postal_code adress_postal_code VARCHAR(255) DEFAULT NULL, CHANGE adress_street adress_street VARCHAR(255) DEFAULT NULL, CHANGE adress_city adress_city VARCHAR(255) DEFAULT NULL, CHANGE adress_street_number adress_street_number VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE family CHANGE adress_postal_code adress_postal_code VARCHAR(255) DEFAULT NULL, CHANGE adress_street adress_street VARCHAR(255) DEFAULT NULL, CHANGE adress_city adress_city VARCHAR(255) DEFAULT NULL, CHANGE adress_street_number adress_street_number VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE aid_request CHANGE adress_postal_code adress_postal_code VARCHAR(255) NOT NULL, CHANGE adress_street adress_street VARCHAR(255) NOT NULL, CHANGE adress_city adress_city VARCHAR(255) NOT NULL, CHANGE adress_street_number adress_street_number VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE family CHANGE adress_postal_code adress_postal_code VARCHAR(255) NOT NULL, CHANGE adress_street adress_street VARCHAR(255) NOT NULL, CHANGE adress_city adress_city VARCHAR(255) NOT NULL, CHANGE adress_street_number adress_street_number VARCHAR(255) NOT NULL');
    }
}
