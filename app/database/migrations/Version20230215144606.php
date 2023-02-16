<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230215144606 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE tags_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('ALTER TABLE devices_tags DROP id');
        $this->addSql('ALTER TABLE devices_tags ALTER device_id SET NOT NULL');
        $this->addSql('ALTER TABLE devices_tags ALTER tag_id SET NOT NULL');
        $this->addSql('ALTER TABLE devices_tags ADD PRIMARY KEY (device_id, tag_id)');
        $this->addSql('CREATE INDEX value_idx ON devices_tags (value)');
        $this->addSql('CREATE INDEX title_idx ON tags (title)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE tags_id_seq CASCADE');
        $this->addSql('DROP INDEX devices_tags_pkey');
        $this->addSql('ALTER TABLE devices_tags ADD id INT NOT NULL');
        $this->addSql('ALTER TABLE devices_tags ALTER device_id DROP NOT NULL');
        $this->addSql('ALTER TABLE devices_tags ALTER tag_id DROP NOT NULL');
        $this->addSql('ALTER TABLE devices_tags ADD PRIMARY KEY (id)');
        $this->addSql('DROP INDEX title_idx');
        $this->addSql('DROP INDEX value_idx');
    }
}
