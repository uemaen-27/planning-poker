<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241113161444 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE session_card ADD session_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE session_card ADD CONSTRAINT FK_D06598F9613FECDF FOREIGN KEY (session_id) REFERENCES session (id)');
        $this->addSql('CREATE INDEX IDX_D06598F9613FECDF ON session_card (session_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE session_card DROP FOREIGN KEY FK_D06598F9613FECDF');
        $this->addSql('DROP INDEX IDX_D06598F9613FECDF ON session_card');
        $this->addSql('ALTER TABLE session_card DROP session_id');
    }
}
