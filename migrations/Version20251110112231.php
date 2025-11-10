<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251110112231 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE aid_request ADD quittance_loyer VARCHAR(255) NOT NULL, ADD avis_charge VARCHAR(255) NOT NULL, ADD taxe_fonciere VARCHAR(255) NOT NULL, ADD frais_scolarité VARCHAR(255) NOT NULL, ADD attestation_caf VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE aid_request DROP quittance_loyer, DROP avis_charge, DROP taxe_fonciere, DROP frais_scolarité, DROP attestation_caf');
    }
}
