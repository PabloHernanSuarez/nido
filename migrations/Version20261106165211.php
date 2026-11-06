<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261106165211 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE jardin (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(100) NOT NULL, direccion VARCHAR(150) DEFAULT NULL, telefono VARCHAR(30) DEFAULT NULL, color VARCHAR(7) NOT NULL, logo VARCHAR(255) DEFAULT NULL, activo TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_JARDIN_NOMBRE (nombre), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE jardin_audit (id INT UNSIGNED AUTO_INCREMENT NOT NULL, type VARCHAR(10) NOT NULL, object_id VARCHAR(255) NOT NULL, discriminator VARCHAR(255) DEFAULT NULL, transaction_hash VARCHAR(40) DEFAULT NULL, diffs JSON DEFAULT NULL, blame_id VARCHAR(255) DEFAULT NULL, blame_user VARCHAR(255) DEFAULT NULL, blame_user_fqdn VARCHAR(255) DEFAULT NULL, blame_user_firewall VARCHAR(100) DEFAULT NULL, ip VARCHAR(45) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX type_392805d79cb20e7e2667d459b414c4fa_idx (type), INDEX object_id_392805d79cb20e7e2667d459b414c4fa_idx (object_id), INDEX discriminator_392805d79cb20e7e2667d459b414c4fa_idx (discriminator), INDEX transaction_hash_392805d79cb20e7e2667d459b414c4fa_idx (transaction_hash), INDEX blame_id_392805d79cb20e7e2667d459b414c4fa_idx (blame_id), INDEX created_at_392805d79cb20e7e2667d459b414c4fa_idx (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request_audit (id INT UNSIGNED AUTO_INCREMENT NOT NULL, type VARCHAR(10) NOT NULL, object_id VARCHAR(255) NOT NULL, discriminator VARCHAR(255) DEFAULT NULL, transaction_hash VARCHAR(40) DEFAULT NULL, diffs JSON DEFAULT NULL, blame_id VARCHAR(255) DEFAULT NULL, blame_user VARCHAR(255) DEFAULT NULL, blame_user_fqdn VARCHAR(255) DEFAULT NULL, blame_user_firewall VARCHAR(100) DEFAULT NULL, ip VARCHAR(45) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX type_8f99c9c902668eaf9b034bbdd8bce9c2_idx (type), INDEX object_id_8f99c9c902668eaf9b034bbdd8bce9c2_idx (object_id), INDEX discriminator_8f99c9c902668eaf9b034bbdd8bce9c2_idx (discriminator), INDEX transaction_hash_8f99c9c902668eaf9b034bbdd8bce9c2_idx (transaction_hash), INDEX blame_id_8f99c9c902668eaf9b034bbdd8bce9c2_idx (blame_id), INDEX created_at_8f99c9c902668eaf9b034bbdd8bce9c2_idx (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuario (id INT AUTO_INCREMENT NOT NULL, jardin_id INT DEFAULT NULL, email VARCHAR(100) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nombre VARCHAR(50) NOT NULL, apellido VARCHAR(50) NOT NULL, is_active TINYINT(1) NOT NULL, password_changed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_USUARIO_EMAIL (email), INDEX IDX_2265B05DD4CBAD1 (jardin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuario_audit (id INT UNSIGNED AUTO_INCREMENT NOT NULL, type VARCHAR(10) NOT NULL, object_id VARCHAR(255) NOT NULL, discriminator VARCHAR(255) DEFAULT NULL, transaction_hash VARCHAR(40) DEFAULT NULL, diffs JSON DEFAULT NULL, blame_id VARCHAR(255) DEFAULT NULL, blame_user VARCHAR(255) DEFAULT NULL, blame_user_fqdn VARCHAR(255) DEFAULT NULL, blame_user_firewall VARCHAR(100) DEFAULT NULL, ip VARCHAR(45) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX type_40eb26316224b7482390ed5d2436bdf7_idx (type), INDEX object_id_40eb26316224b7482390ed5d2436bdf7_idx (object_id), INDEX discriminator_40eb26316224b7482390ed5d2436bdf7_idx (discriminator), INDEX transaction_hash_40eb26316224b7482390ed5d2436bdf7_idx (transaction_hash), INDEX blame_id_40eb26316224b7482390ed5d2436bdf7_idx (blame_id), INDEX created_at_40eb26316224b7482390ed5d2436bdf7_idx (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES usuario (id)');
        $this->addSql('ALTER TABLE usuario ADD CONSTRAINT FK_2265B05DD4CBAD1 FOREIGN KEY (jardin_id) REFERENCES jardin (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE usuario DROP FOREIGN KEY FK_2265B05DD4CBAD1');
        $this->addSql('DROP TABLE jardin');
        $this->addSql('DROP TABLE jardin_audit');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE reset_password_request_audit');
        $this->addSql('DROP TABLE usuario');
        $this->addSql('DROP TABLE usuario_audit');
    }
}
