<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250108150559 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE conseil (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE conseil_salle (conseil_id INT NOT NULL, salle_id INT NOT NULL, INDEX IDX_197CEA47668A3E03 (conseil_id), INDEX IDX_197CEA47DC304035 (salle_id), PRIMARY KEY(conseil_id, salle_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE conseil_salle ADD CONSTRAINT FK_197CEA47668A3E03 FOREIGN KEY (conseil_id) REFERENCES conseil (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conseil_salle ADD CONSTRAINT FK_197CEA47DC304035 FOREIGN KEY (salle_id) REFERENCES salle (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE etage DROP nom');
        $this->addSql('ALTER TABLE utilisateur CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conseil_salle DROP FOREIGN KEY FK_197CEA47668A3E03');
        $this->addSql('ALTER TABLE conseil_salle DROP FOREIGN KEY FK_197CEA47DC304035');
        $this->addSql('DROP TABLE conseil');
        $this->addSql('DROP TABLE conseil_salle');
        $this->addSql('ALTER TABLE utilisateur CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE etage ADD nom VARCHAR(5) NOT NULL');
    }
}
