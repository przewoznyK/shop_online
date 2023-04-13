<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230413132200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE order_product (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, buyer_id INT NOT NULL, product VARCHAR(255) NOT NULL, price VARCHAR(255) NOT NULL, ispaid TINYINT(1) NOT NULL, comment VARCHAR(255) DEFAULT NULL, delivery_type VARCHAR(255) NOT NULL, final_location VARCHAR(255) NOT NULL, INDEX IDX_2530ADE67E3C61F9 (owner_id), INDEX IDX_2530ADE66C755722 (buyer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE order_product ADD CONSTRAINT FK_2530ADE67E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE order_product ADD CONSTRAINT FK_2530ADE66C755722 FOREIGN KEY (buyer_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_product DROP FOREIGN KEY FK_2530ADE67E3C61F9');
        $this->addSql('ALTER TABLE order_product DROP FOREIGN KEY FK_2530ADE66C755722');
        $this->addSql('DROP TABLE order_product');
    }
}
