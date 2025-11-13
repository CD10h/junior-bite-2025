<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251111131658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Saved IP table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE saved_ip (id INT AUTO_INCREMENT NOT NULL, ip VARCHAR(39) NOT NULL, type VARCHAR(4) NOT NULL, continent_code VARCHAR(2) NOT NULL, country_code VARCHAR(2) NOT NULL, region_code VARCHAR(10) NOT NULL, city VARCHAR(20) NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, last_updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_D10BB0A1A5E3B32D (ip), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE saved_ip');
    }
}
