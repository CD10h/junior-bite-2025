<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251113094512 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add relation between blocked IP and IP infos';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE saved_ipinfo (id INT AUTO_INCREMENT NOT NULL, ip VARCHAR(39) NOT NULL, type VARCHAR(4) NOT NULL, continent_code VARCHAR(2) NOT NULL, country_code VARCHAR(2) NOT NULL, region_code VARCHAR(10) NOT NULL, city VARCHAR(20) NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, last_updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_E9208AAEA5E3B32D (ip), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP TABLE saved_ip');
        $this->addSql('ALTER TABLE blocked_ip ADD blocked_ipinfo_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE blocked_ip ADD CONSTRAINT FK_3B25854B7BF1BAC4 FOREIGN KEY (blocked_ipinfo_id) REFERENCES saved_ipinfo (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3B25854B7BF1BAC4 ON blocked_ip (blocked_ipinfo_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE blocked_ip DROP FOREIGN KEY FK_3B25854B7BF1BAC4');
        $this->addSql('CREATE TABLE saved_ip (id INT AUTO_INCREMENT NOT NULL, ip VARCHAR(15) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, type VARCHAR(4) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, continent_code VARCHAR(2) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, country_code VARCHAR(2) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, region_code VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, city VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, last_updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_D10BB0A1A5E3B32D (ip), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE saved_ipinfo');
        $this->addSql('DROP INDEX UNIQ_3B25854B7BF1BAC4 ON blocked_ip');
        $this->addSql('ALTER TABLE blocked_ip DROP blocked_ipinfo_id');
    }
}
