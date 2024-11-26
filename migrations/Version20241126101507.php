<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241126101507 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mission_power (mission_id INT NOT NULL, power_id INT NOT NULL, INDEX IDX_3B1C5E9DBE6CAE90 (mission_id), INDEX IDX_3B1C5E9DAB4FC384 (power_id), PRIMARY KEY(mission_id, power_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mission_power ADD CONSTRAINT FK_3B1C5E9DBE6CAE90 FOREIGN KEY (mission_id) REFERENCES mission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mission_power ADD CONSTRAINT FK_3B1C5E9DAB4FC384 FOREIGN KEY (power_id) REFERENCES power (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mission_power DROP FOREIGN KEY FK_3B1C5E9DBE6CAE90');
        $this->addSql('ALTER TABLE mission_power DROP FOREIGN KEY FK_3B1C5E9DAB4FC384');
        $this->addSql('DROP TABLE mission_power');
    }
}
