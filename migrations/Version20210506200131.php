<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210506200131 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cards DROP FOREIGN KEY manufacturer');
        $this->addSql('ALTER TABLE cards DROP FOREIGN KEY `order`');
        $this->addSql('ALTER TABLE cards CHANGE order_id order_id INT DEFAULT NULL, CHANGE manufacturer_id manufacturer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE cards ADD CONSTRAINT FK_4C258FDA23B42D FOREIGN KEY (manufacturer_id) REFERENCES manufacturers (manufacturer_id)');
        $this->addSql('ALTER TABLE cards ADD CONSTRAINT FK_4C258FD8D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (order_id)');
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY user');
        $this->addSql('ALTER TABLE orders CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEEA76ED395 FOREIGN KEY (user_id) REFERENCES users (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cards DROP FOREIGN KEY FK_4C258FDA23B42D');
        $this->addSql('ALTER TABLE cards DROP FOREIGN KEY FK_4C258FD8D9F6D38');
        $this->addSql('ALTER TABLE cards CHANGE manufacturer_id manufacturer_id INT NOT NULL, CHANGE order_id order_id INT NOT NULL');
        $this->addSql('ALTER TABLE cards ADD CONSTRAINT manufacturer FOREIGN KEY (manufacturer_id) REFERENCES manufacturers (manufacturer_id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cards ADD CONSTRAINT `order` FOREIGN KEY (order_id) REFERENCES orders (order_id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEEA76ED395');
        $this->addSql('ALTER TABLE orders CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT user FOREIGN KEY (user_id) REFERENCES users (user_id) ON UPDATE CASCADE ON DELETE CASCADE');
    }
}
