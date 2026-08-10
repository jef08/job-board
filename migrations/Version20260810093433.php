<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260810093433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE freelancer ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE freelancer ADD CONSTRAINT FK_4C2ED1E812469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('CREATE INDEX IDX_4C2ED1E812469DE2 ON freelancer (category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE freelancer DROP FOREIGN KEY FK_4C2ED1E812469DE2');
        $this->addSql('DROP INDEX IDX_4C2ED1E812469DE2 ON freelancer');
        $this->addSql('ALTER TABLE freelancer DROP category_id');
    }
}
