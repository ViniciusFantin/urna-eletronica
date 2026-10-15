<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261015012806 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE eleicao (id INT NOT NULL, status VARCHAR(20) NOT NULL, iniciada_em DATETIME DEFAULT NULL, encerrada_em DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE voto ADD conteudo_cifrado LONGTEXT NOT NULL, CHANGE candidato_id candidato_id INT DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_VOTO_TOKEN ON voto (token)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE eleicao');
        $this->addSql('DROP INDEX UNIQ_VOTO_TOKEN ON voto');
        $this->addSql('ALTER TABLE voto DROP conteudo_cifrado, CHANGE candidato_id candidato_id INT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
    }
}
