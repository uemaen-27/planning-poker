<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241114122840 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE session ADD active_pbi_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D41905F5F3 FOREIGN KEY (active_pbi_id) REFERENCES product_backlog_item (id)');
        $this->addSql('CREATE INDEX IDX_D044D5D41905F5F3 ON session (active_pbi_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D41905F5F3');
        $this->addSql('DROP INDEX IDX_D044D5D41905F5F3 ON session');
        $this->addSql('ALTER TABLE session DROP active_pbi_id');
    }
}
