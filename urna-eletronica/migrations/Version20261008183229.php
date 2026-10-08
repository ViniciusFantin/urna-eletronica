<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261008183229 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE audit_log (id INT AUTO_INCREMENT NOT NULL, event VARCHAR(100) NOT NULL, data LONGTEXT NOT NULL, previous_hash VARCHAR(64) DEFAULT NULL, current_hash VARCHAR(64) NOT NULL, signature LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE candidato (id INT AUTO_INCREMENT NOT NULL, nome VARCHAR(150) NOT NULL, numero INT NOT NULL, cargo VARCHAR(100) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE eleitor (id INT AUTO_INCREMENT NOT NULL, titulo_eleitor VARCHAR(20) NOT NULL, nome VARCHAR(150) NOT NULL, senha VARCHAR(255) NOT NULL, otp VARCHAR(10) DEFAULT NULL, has_voted TINYINT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE voto (id INT AUTO_INCREMENT NOT NULL, token VARCHAR(64) NOT NULL, candidato_id INT NOT NULL, created_at DATETIME NOT NULL, assinatura LONGTEXT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE audit_log');
        $this->addSql('DROP TABLE candidato');
        $this->addSql('DROP TABLE eleitor');
        $this->addSql('DROP TABLE voto');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
