<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241107100123 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE capteur (id INT AUTO_INCREMENT NOT NULL, sa_id INT NOT NULL, preval DOUBLE PRECISION NOT NULL, newval DOUBLE PRECISION NOT NULL, type VARCHAR(255) NOT NULL, etat VARCHAR(255) NOT NULL, INDEX IDX_5B4A169562CAE146 (sa_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etage (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(5) NOT NULL, nom_complet VARCHAR(25) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE salle (id INT AUTO_INCREMENT NOT NULL, etage_id INT NOT NULL, sa_id INT NOT NULL, nom VARCHAR(25) NOT NULL, INDEX IDX_4E977E5C984CE93F (etage_id), UNIQUE INDEX UNIQ_4E977E5C62CAE146 (sa_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE system_acquisition (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(25) NOT NULL, adresse VARCHAR(255) NOT NULL, etat VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE capteur ADD CONSTRAINT FK_5B4A169562CAE146 FOREIGN KEY (sa_id) REFERENCES system_acquisition (id)');
        $this->addSql('ALTER TABLE salle ADD CONSTRAINT FK_4E977E5C984CE93F FOREIGN KEY (etage_id) REFERENCES etage (id)');
        $this->addSql('ALTER TABLE salle ADD CONSTRAINT FK_4E977E5C62CAE146 FOREIGN KEY (sa_id) REFERENCES system_acquisition (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE capteur DROP FOREIGN KEY FK_5B4A169562CAE146');
        $this->addSql('ALTER TABLE salle DROP FOREIGN KEY FK_4E977E5C984CE93F');
        $this->addSql('ALTER TABLE salle DROP FOREIGN KEY FK_4E977E5C62CAE146');
        $this->addSql('DROP TABLE capteur');
        $this->addSql('DROP TABLE etage');
        $this->addSql('DROP TABLE salle');
        $this->addSql('DROP TABLE system_acquisition');
    }
}
