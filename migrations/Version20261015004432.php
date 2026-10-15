<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261015004432 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidato ADD partido VARCHAR(100) DEFAULT NULL, ADD eleitor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE candidato ADD CONSTRAINT FK_2867675A3BD1A965 FOREIGN KEY (eleitor_id) REFERENCES eleitor (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2867675A3BD1A965 ON candidato (eleitor_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CANDIDATO_NUMERO ON candidato (numero)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidato DROP FOREIGN KEY FK_2867675A3BD1A965');
        $this->addSql('DROP INDEX UNIQ_2867675A3BD1A965 ON candidato');
        $this->addSql('DROP INDEX UNIQ_CANDIDATO_NUMERO ON candidato');
        $this->addSql('ALTER TABLE candidato DROP partido, DROP eleitor_id');
    }
}
