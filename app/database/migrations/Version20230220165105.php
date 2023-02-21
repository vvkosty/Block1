<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230220165105 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE devices_tags_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('ALTER TABLE devices_tags DROP CONSTRAINT devices_tags_pkey;');
        $this->addSql('ALTER TABLE devices_tags ADD id INT NOT NULL');
        $this->addSql('ALTER TABLE devices_tags ALTER device_id DROP NOT NULL');
        $this->addSql('ALTER TABLE devices_tags ALTER tag_id DROP NOT NULL');
        $this->addSql('ALTER TABLE devices_tags ADD PRIMARY KEY (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE devices_tags_id_seq CASCADE');
        $this->addSql('DROP INDEX devices_tags_pkey');
        $this->addSql('ALTER TABLE devices_tags DROP id');
        $this->addSql('ALTER TABLE devices_tags ALTER device_id SET NOT NULL');
        $this->addSql('ALTER TABLE devices_tags ALTER tag_id SET NOT NULL');
        $this->addSql('ALTER TABLE devices_tags ADD PRIMARY KEY (device_id, tag_id)');
    }
}
