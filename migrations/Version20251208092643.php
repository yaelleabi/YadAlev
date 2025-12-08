<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251208092643 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE family DROP FOREIGN KEY FK_A5E6215B28E96968');
        $this->addSql('DROP INDEX UNIQ_A5E6215B28E96968 ON family');
        $this->addSql('ALTER TABLE family ADD housing_status VARCHAR(50) DEFAULT NULL, ADD marital_status VARCHAR(50) DEFAULT NULL, ADD employment_status VARCHAR(50) DEFAULT NULL, ADD monthly_income NUMERIC(10, 2) DEFAULT NULL, ADD spouse_employment_status VARCHAR(50) DEFAULT NULL, ADD spouse_monthly_income NUMERIC(10, 2) DEFAULT NULL, ADD family_allowance_amount NUMERIC(10, 2) DEFAULT NULL, ADD alimony_amount NUMERIC(10, 2) DEFAULT NULL, ADD rent_amount_net_aide NUMERIC(10, 2) DEFAULT NULL, ADD other_need LONGTEXT DEFAULT NULL, ADD other_comment LONGTEXT DEFAULT NULL, ADD identity_proof_filename VARCHAR(255) DEFAULT NULL, ADD income_proof_filename VARCHAR(255) DEFAULT NULL, ADD tax_notice_filename VARCHAR(255) DEFAULT NULL, ADD other_document_filename VARCHAR(255) DEFAULT NULL, ADD quittance_loyer VARCHAR(255) DEFAULT NULL, ADD avis_charge VARCHAR(255) DEFAULT NULL, ADD taxe_fonciere VARCHAR(255) DEFAULT NULL, ADD frais_scolarite VARCHAR(255) DEFAULT NULL, ADD attestation_caf VARCHAR(255) DEFAULT NULL, ADD adress_postal_code VARCHAR(255) NOT NULL, ADD adress_street VARCHAR(255) NOT NULL, ADD adress_city VARCHAR(255) NOT NULL, ADD adress_street_number VARCHAR(255) NOT NULL, CHANGE aid_request_id dependants_count INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE family DROP housing_status, DROP marital_status, DROP employment_status, DROP monthly_income, DROP spouse_employment_status, DROP spouse_monthly_income, DROP family_allowance_amount, DROP alimony_amount, DROP rent_amount_net_aide, DROP other_need, DROP other_comment, DROP identity_proof_filename, DROP income_proof_filename, DROP tax_notice_filename, DROP other_document_filename, DROP quittance_loyer, DROP avis_charge, DROP taxe_fonciere, DROP frais_scolarite, DROP attestation_caf, DROP adress_postal_code, DROP adress_street, DROP adress_city, DROP adress_street_number, CHANGE dependants_count aid_request_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE family ADD CONSTRAINT FK_A5E6215B28E96968 FOREIGN KEY (aid_request_id) REFERENCES aid_request (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A5E6215B28E96968 ON family (aid_request_id)');
    }
}
