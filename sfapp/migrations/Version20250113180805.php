<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250113180805 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE note ADD batiment_id INT DEFAULT NULL, ADD conseil VARCHAR(255) DEFAULT NULL, CHANGE salle_id salle_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14D6F6891B FOREIGN KEY (batiment_id) REFERENCES batiment (id)');
        $this->addSql('CREATE INDEX IDX_CFBDFA14D6F6891B ON note (batiment_id)');
        $this->addSql('ALTER TABLE utilisateur CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE utilisateur CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE note DROP FOREIGN KEY FK_CFBDFA14D6F6891B');
        $this->addSql('DROP INDEX IDX_CFBDFA14D6F6891B ON note');
        $this->addSql('ALTER TABLE note DROP batiment_id, DROP conseil, CHANGE salle_id salle_id INT NOT NULL');
    }
}
