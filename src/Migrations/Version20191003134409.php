<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191003134409 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE formation ADD produit_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE formation ADD CONSTRAINT FK_404021BFF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
        $this->addSql('CREATE INDEX IDX_404021BFF347EFB ON formation (produit_id)');
        $this->addSql('ALTER TABLE inscription ADD visiteur_id INT DEFAULT NULL, ADD formation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D67F72333D FOREIGN KEY (visiteur_id) REFERENCES visiteur (id)');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D65200282E FOREIGN KEY (formation_id) REFERENCES formation (id)');
        $this->addSql('CREATE INDEX IDX_5E90F6D67F72333D ON inscription (visiteur_id)');
        $this->addSql('CREATE INDEX IDX_5E90F6D65200282E ON inscription (formation_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE formation DROP FOREIGN KEY FK_404021BFF347EFB');
        $this->addSql('DROP INDEX IDX_404021BFF347EFB ON formation');
        $this->addSql('ALTER TABLE formation DROP produit_id');
        $this->addSql('ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D67F72333D');
        $this->addSql('ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D65200282E');
        $this->addSql('DROP INDEX IDX_5E90F6D67F72333D ON inscription');
        $this->addSql('DROP INDEX IDX_5E90F6D65200282E ON inscription');
        $this->addSql('ALTER TABLE inscription DROP visiteur_id, DROP formation_id');
    }
}
