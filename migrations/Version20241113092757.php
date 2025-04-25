<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241113092757 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE estimate (id INT AUTO_INCREMENT NOT NULL, participant_id INT DEFAULT NULL, session_id INT DEFAULT NULL, product_backlog_item_id INT DEFAULT NULL, value INT DEFAULT NULL, revealed TINYINT(1) NOT NULL, INDEX IDX_D2EA46079D1C3019 (participant_id), INDEX IDX_D2EA4607613FECDF (session_id), INDEX IDX_D2EA4607B5EE39E4 (product_backlog_item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_backlog_item (id INT AUTO_INCREMENT NOT NULL, session_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, INDEX IDX_2CF3EB63613FECDF (session_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE session (id INT AUTO_INCREMENT NOT NULL, host_id INT DEFAULT NULL, session_key VARCHAR(255) NOT NULL, estimation_type VARCHAR(255) NOT NULL, custom_hours LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', reveal_mode VARCHAR(255) NOT NULL, INDEX IDX_D044D5D41FB8D185 (host_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE session_user (session_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_4BE2D663613FECDF (session_id), INDEX IDX_4BE2D663A76ED395 (user_id), PRIMARY KEY(session_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, is_host TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE estimate ADD CONSTRAINT FK_D2EA46079D1C3019 FOREIGN KEY (participant_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE estimate ADD CONSTRAINT FK_D2EA4607613FECDF FOREIGN KEY (session_id) REFERENCES session (id)');
        $this->addSql('ALTER TABLE estimate ADD CONSTRAINT FK_D2EA4607B5EE39E4 FOREIGN KEY (product_backlog_item_id) REFERENCES product_backlog_item (id)');
        $this->addSql('ALTER TABLE product_backlog_item ADD CONSTRAINT FK_2CF3EB63613FECDF FOREIGN KEY (session_id) REFERENCES session (id)');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D41FB8D185 FOREIGN KEY (host_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE session_user ADD CONSTRAINT FK_4BE2D663613FECDF FOREIGN KEY (session_id) REFERENCES session (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE session_user ADD CONSTRAINT FK_4BE2D663A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE estimate DROP FOREIGN KEY FK_D2EA46079D1C3019');
        $this->addSql('ALTER TABLE estimate DROP FOREIGN KEY FK_D2EA4607613FECDF');
        $this->addSql('ALTER TABLE estimate DROP FOREIGN KEY FK_D2EA4607B5EE39E4');
        $this->addSql('ALTER TABLE product_backlog_item DROP FOREIGN KEY FK_2CF3EB63613FECDF');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D41FB8D185');
        $this->addSql('ALTER TABLE session_user DROP FOREIGN KEY FK_4BE2D663613FECDF');
        $this->addSql('ALTER TABLE session_user DROP FOREIGN KEY FK_4BE2D663A76ED395');
        $this->addSql('DROP TABLE estimate');
        $this->addSql('DROP TABLE product_backlog_item');
        $this->addSql('DROP TABLE session');
        $this->addSql('DROP TABLE session_user');
        $this->addSql('DROP TABLE `user`');
    }
}
