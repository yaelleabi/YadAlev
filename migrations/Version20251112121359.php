<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251112121359 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE aid_request DROP other_request_reason, DROP current_situation, DROP financial_difficulties, CHANGE quittance_loyer quittance_loyer VARCHAR(255) DEFAULT NULL, CHANGE avis_charge avis_charge VARCHAR(255) DEFAULT NULL, CHANGE taxe_fonciere taxe_fonciere VARCHAR(255) DEFAULT NULL, CHANGE frais_scolarite frais_scolarite VARCHAR(255) DEFAULT NULL, CHANGE attestation_caf attestation_caf VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE aid_request ADD other_request_reason LONGTEXT DEFAULT NULL, ADD current_situation LONGTEXT NOT NULL, ADD financial_difficulties LONGTEXT NOT NULL, CHANGE quittance_loyer quittance_loyer VARCHAR(255) NOT NULL, CHANGE avis_charge avis_charge VARCHAR(255) NOT NULL, CHANGE taxe_fonciere taxe_fonciere VARCHAR(255) NOT NULL, CHANGE frais_scolarite frais_scolarite VARCHAR(255) NOT NULL, CHANGE attestation_caf attestation_caf VARCHAR(255) NOT NULL');
    }
}
