<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230209135450 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE devices (id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN devices.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE devices_tags (id INT NOT NULL, device_id INT DEFAULT NULL, tag_id INT DEFAULT NULL, value VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8472C11794A4C7D4 ON devices_tags (device_id)');
        $this->addSql('CREATE INDEX IDX_8472C117BAD26311 ON devices_tags (tag_id)');
        $this->addSql('CREATE TABLE tags (id INT NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE devices_tags ADD CONSTRAINT FK_8472C11794A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE devices_tags ADD CONSTRAINT FK_8472C117BAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE devices_tags DROP CONSTRAINT FK_8472C11794A4C7D4');
        $this->addSql('ALTER TABLE devices_tags DROP CONSTRAINT FK_8472C117BAD26311');
        $this->addSql('DROP TABLE devices');
        $this->addSql('DROP TABLE devices_tags');
        $this->addSql('DROP TABLE tags');
    }
}
