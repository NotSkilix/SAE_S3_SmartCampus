<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241217100052 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F5FAB00C6C6E55B5 ON batiment (nom)');
        $this->addSql('ALTER TABLE plan CHANGE salle_id salle_id INT DEFAULT NULL, CHANGE sa_id sa_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_F5FAB00C6C6E55B5 ON batiment');
        $this->addSql('ALTER TABLE plan CHANGE salle_id salle_id INT NOT NULL, CHANGE sa_id sa_id INT NOT NULL');
    }
}
