<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241121092119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE batiment (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(25) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE details_salle (id INT AUTO_INCREMENT NOT NULL, salle_id INT NOT NULL, superficie INT DEFAULT NULL, radiateur INT DEFAULT NULL, fenetre INT DEFAULT NULL, exposition VARCHAR(5) DEFAULT NULL, porte INT DEFAULT NULL, frequentation VARCHAR(25) DEFAULT NULL, date_de_creation DATE NOT NULL, date_derniere_modification DATE DEFAULT NULL, UNIQUE INDEX UNIQ_DE78D8B3DC304035 (salle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE plan (id INT AUTO_INCREMENT NOT NULL, salle_id INT NOT NULL, sa_id INT NOT NULL, date_association DATE NOT NULL, INDEX IDX_DD5A5B7DDC304035 (salle_id), UNIQUE INDEX UNIQ_DD5A5B7D62CAE146 (sa_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE details_salle ADD CONSTRAINT FK_DE78D8B3DC304035 FOREIGN KEY (salle_id) REFERENCES salle (id)');
        $this->addSql('ALTER TABLE plan ADD CONSTRAINT FK_DD5A5B7DDC304035 FOREIGN KEY (salle_id) REFERENCES salle (id)');
        $this->addSql('ALTER TABLE plan ADD CONSTRAINT FK_DD5A5B7D62CAE146 FOREIGN KEY (sa_id) REFERENCES system_acquisition (id)');
        $this->addSql('ALTER TABLE capteur ADD valeur DOUBLE PRECISION DEFAULT NULL, DROP preval, DROP newval');
        $this->addSql('ALTER TABLE etage ADD batiment_id INT NOT NULL');
        $this->addSql('ALTER TABLE etage ADD CONSTRAINT FK_2DDCF14BD6F6891B FOREIGN KEY (batiment_id) REFERENCES batiment (id)');
        $this->addSql('CREATE INDEX IDX_2DDCF14BD6F6891B ON etage (batiment_id)');
        $this->addSql('ALTER TABLE salle DROP FOREIGN KEY FK_4E977E5C62CAE146');
        $this->addSql('DROP INDEX UNIQ_4E977E5C62CAE146 ON salle');
        $this->addSql('ALTER TABLE salle DROP sa_id');
        $this->addSql('ALTER TABLE system_acquisition ADD description VARCHAR(255) DEFAULT NULL, ADD date_creation DATE NOT NULL, ADD date_derniere_modification DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE etage DROP FOREIGN KEY FK_2DDCF14BD6F6891B');
        $this->addSql('ALTER TABLE details_salle DROP FOREIGN KEY FK_DE78D8B3DC304035');
        $this->addSql('ALTER TABLE historique_sa DROP FOREIGN KEY FK_A16F7D0A62CAE146');
        $this->addSql('ALTER TABLE historique_salle DROP FOREIGN KEY FK_17A42FDC304035');
        $this->addSql('ALTER TABLE plan DROP FOREIGN KEY FK_DD5A5B7DDC304035');
        $this->addSql('ALTER TABLE plan DROP FOREIGN KEY FK_DD5A5B7D62CAE146');
        $this->addSql('DROP TABLE batiment');
        $this->addSql('DROP TABLE details_salle');
        $this->addSql('DROP TABLE historique_sa');
        $this->addSql('DROP TABLE historique_salle');
        $this->addSql('DROP TABLE plan');
        $this->addSql('ALTER TABLE salle ADD sa_id INT NOT NULL');
        $this->addSql('ALTER TABLE salle ADD CONSTRAINT FK_4E977E5C62CAE146 FOREIGN KEY (sa_id) REFERENCES system_acquisition (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4E977E5C62CAE146 ON salle (sa_id)');
        $this->addSql('ALTER TABLE capteur ADD newval DOUBLE PRECISION DEFAULT NULL, CHANGE valeur preval DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('DROP INDEX IDX_2DDCF14BD6F6891B ON etage');
        $this->addSql('ALTER TABLE etage DROP batiment_id');
        $this->addSql('ALTER TABLE system_acquisition DROP description, DROP date_creation, DROP date_derniere_modification');
    }
}
