<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251117100350 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE aid_list (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE aid_request (id INT AUTO_INCREMENT NOT NULL, family_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_name VARCHAR(100) NOT NULL, first_name VARCHAR(100) NOT NULL, date_of_birth DATE NOT NULL, email VARCHAR(255) NOT NULL, phone_number VARCHAR(20) NOT NULL, housing_status VARCHAR(50) NOT NULL, marital_status VARCHAR(50) NOT NULL, dependants_count INT NOT NULL, employment_status VARCHAR(50) NOT NULL, monthly_income NUMERIC(10, 2) DEFAULT NULL, spouse_employment_status VARCHAR(50) DEFAULT NULL, spouse_monthly_income NUMERIC(10, 2) DEFAULT NULL, family_allowance_amount NUMERIC(10, 2) DEFAULT NULL, alimony_amount NUMERIC(10, 2) DEFAULT NULL, rent_amount_net_aide NUMERIC(10, 2) DEFAULT NULL, status VARCHAR(255) NOT NULL, request_type VARCHAR(50) NOT NULL, request_duration VARCHAR(50) NOT NULL, other_request_duration LONGTEXT DEFAULT NULL, request_reason LONGTEXT NOT NULL, urgency_explanation LONGTEXT NOT NULL, urgency_level SMALLINT NOT NULL, other_need LONGTEXT DEFAULT NULL, other_comment LONGTEXT DEFAULT NULL, privacy_consent TINYINT(1) NOT NULL, identity_proof_filename VARCHAR(255) DEFAULT NULL, income_proof_filename VARCHAR(255) DEFAULT NULL, tax_notice_filename VARCHAR(255) DEFAULT NULL, other_document_filename VARCHAR(255) DEFAULT NULL, quittance_loyer VARCHAR(255) DEFAULT NULL, avis_charge VARCHAR(255) DEFAULT NULL, taxe_fonciere VARCHAR(255) DEFAULT NULL, frais_scolarite VARCHAR(255) DEFAULT NULL, attestation_caf VARCHAR(255) DEFAULT NULL, adress_postal_code VARCHAR(255) NOT NULL, adress_street VARCHAR(255) NOT NULL, adress_city VARCHAR(255) NOT NULL, adress_street_number VARCHAR(255) NOT NULL, INDEX IDX_FC5DB68DC35E566A (family_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE delivery (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, date DATETIME NOT NULL, status VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE delivery_assignment (id INT AUTO_INCREMENT NOT NULL, delivery_id INT DEFAULT NULL, city VARCHAR(255) NOT NULL, total_packages INT NOT NULL, reserved_packages INT NOT NULL, delivery_status VARCHAR(255) NOT NULL, INDEX IDX_2E80D39E12136921 (delivery_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, is_visible TINYINT(1) NOT NULL, discr VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE family (id INT NOT NULL, aid_request_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_A5E6215B28E96968 (aid_request_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE family_aid_list (family_id INT NOT NULL, aid_list_id INT NOT NULL, INDEX IDX_E95DAF5BC35E566A (family_id), INDEX IDX_E95DAF5BFA642F99 (aid_list_id), PRIMARY KEY(family_id, aid_list_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE family_event (id INT NOT NULL, assigned_families LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, phone_number VARCHAR(255) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', discr VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE volunteer (id INT NOT NULL, assigned_projects VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE volunteer_event (id INT NOT NULL, assigned_volunteers LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE volunteer_request (id INT AUTO_INCREMENT NOT NULL, delivery_assignment_id INT DEFAULT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_E57F90A7D6614518 (delivery_assignment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE aid_request ADD CONSTRAINT FK_FC5DB68DC35E566A FOREIGN KEY (family_id) REFERENCES family (id)');
        $this->addSql('ALTER TABLE delivery_assignment ADD CONSTRAINT FK_2E80D39E12136921 FOREIGN KEY (delivery_id) REFERENCES delivery (id)');
        $this->addSql('ALTER TABLE family ADD CONSTRAINT FK_A5E6215B28E96968 FOREIGN KEY (aid_request_id) REFERENCES aid_request (id)');
        $this->addSql('ALTER TABLE family ADD CONSTRAINT FK_A5E6215BBF396750 FOREIGN KEY (id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE family_aid_list ADD CONSTRAINT FK_E95DAF5BC35E566A FOREIGN KEY (family_id) REFERENCES family (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE family_aid_list ADD CONSTRAINT FK_E95DAF5BFA642F99 FOREIGN KEY (aid_list_id) REFERENCES aid_list (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE family_event ADD CONSTRAINT FK_7D999D4BF396750 FOREIGN KEY (id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE volunteer ADD CONSTRAINT FK_5140DEDBBF396750 FOREIGN KEY (id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE volunteer_event ADD CONSTRAINT FK_9C0D755BF396750 FOREIGN KEY (id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE volunteer_request ADD CONSTRAINT FK_E57F90A7D6614518 FOREIGN KEY (delivery_assignment_id) REFERENCES delivery_assignment (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE aid_request DROP FOREIGN KEY FK_FC5DB68DC35E566A');
        $this->addSql('ALTER TABLE delivery_assignment DROP FOREIGN KEY FK_2E80D39E12136921');
        $this->addSql('ALTER TABLE family DROP FOREIGN KEY FK_A5E6215B28E96968');
        $this->addSql('ALTER TABLE family DROP FOREIGN KEY FK_A5E6215BBF396750');
        $this->addSql('ALTER TABLE family_aid_list DROP FOREIGN KEY FK_E95DAF5BC35E566A');
        $this->addSql('ALTER TABLE family_aid_list DROP FOREIGN KEY FK_E95DAF5BFA642F99');
        $this->addSql('ALTER TABLE family_event DROP FOREIGN KEY FK_7D999D4BF396750');
        $this->addSql('ALTER TABLE volunteer DROP FOREIGN KEY FK_5140DEDBBF396750');
        $this->addSql('ALTER TABLE volunteer_event DROP FOREIGN KEY FK_9C0D755BF396750');
        $this->addSql('ALTER TABLE volunteer_request DROP FOREIGN KEY FK_E57F90A7D6614518');
        $this->addSql('DROP TABLE aid_list');
        $this->addSql('DROP TABLE aid_request');
        $this->addSql('DROP TABLE delivery');
        $this->addSql('DROP TABLE delivery_assignment');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE family');
        $this->addSql('DROP TABLE family_aid_list');
        $this->addSql('DROP TABLE family_event');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE volunteer');
        $this->addSql('DROP TABLE volunteer_event');
        $this->addSql('DROP TABLE volunteer_request');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
