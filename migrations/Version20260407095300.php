<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260407095300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "order" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, customer_email VARCHAR(180) NOT NULL, subtotal DOUBLE PRECISION NOT NULL, discount_amount DOUBLE PRECISION NOT NULL, total DOUBLE PRECISION NOT NULL, promo_code VARCHAR(80) DEFAULT NULL, status VARCHAR(40) NOT NULL, created_at DATETIME NOT NULL, product_id INTEGER NOT NULL, CONSTRAINT FK_F52993984584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_F52993984584665A ON "order" (product_id)');
        $this->addSql('CREATE TABLE product (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, sku VARCHAR(64) NOT NULL, description CLOB DEFAULT NULL, price DOUBLE PRECISION NOT NULL, is_draft BOOLEAN NOT NULL, updated_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D34A04ADF9038C4 ON product (sku)');
        $this->addSql('CREATE TABLE promo_code (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, code VARCHAR(80) NOT NULL, percentage DOUBLE PRECISION NOT NULL, active BOOLEAN NOT NULL, expires_at DATETIME DEFAULT NULL, usage_limit INTEGER DEFAULT NULL, times_used INTEGER NOT NULL, notes CLOB DEFAULT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3D8C939E77153098 ON promo_code (code)');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL, display_name VARCHAR(100) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE "order"');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE promo_code');
        $this->addSql('DROP TABLE user');
    }
}
