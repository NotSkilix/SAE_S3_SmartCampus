<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250113134930 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE conseil_salle (conseil_id INT NOT NULL, salle_id INT NOT NULL, INDEX IDX_197CEA47668A3E03 (conseil_id), INDEX IDX_197CEA47DC304035 (salle_id), PRIMARY KEY(conseil_id, salle_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE conseil_salle ADD CONSTRAINT FK_197CEA47668A3E03 FOREIGN KEY (conseil_id) REFERENCES conseil (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conseil_salle ADD CONSTRAINT FK_197CEA47DC304035 FOREIGN KEY (salle_id) REFERENCES salle (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conseil DROP FOREIGN KEY FK_3F3F0681DC304035');
        $this->addSql('DROP INDEX IDX_3F3F0681DC304035 ON conseil');
        $this->addSql('ALTER TABLE conseil DROP salle_id');
        $this->addSql('ALTER TABLE utilisateur CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conseil_salle DROP FOREIGN KEY FK_197CEA47668A3E03');
        $this->addSql('ALTER TABLE conseil_salle DROP FOREIGN KEY FK_197CEA47DC304035');
        $this->addSql('DROP TABLE conseil_salle');
        $this->addSql('ALTER TABLE conseil ADD salle_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE conseil ADD CONSTRAINT FK_3F3F0681DC304035 FOREIGN KEY (salle_id) REFERENCES salle (id)');
        $this->addSql('CREATE INDEX IDX_3F3F0681DC304035 ON conseil (salle_id)');
        $this->addSql('ALTER TABLE utilisateur CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
    }
}
