<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241127094140 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_IDENTIFIER_LOULOUBATEAU ON `admin`');
        $this->addSql('ALTER TABLE `admin` CHANGE louloubateau email VARCHAR(180) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON `admin` (email)');
        $this->addSql('ALTER TABLE user ADD is_activate TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP is_activate');
        $this->addSql('DROP INDEX UNIQ_IDENTIFIER_EMAIL ON `admin`');
        $this->addSql('ALTER TABLE `admin` CHANGE email louloubateau VARCHAR(180) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_LOULOUBATEAU ON `admin` (louloubateau)');
    }
}
