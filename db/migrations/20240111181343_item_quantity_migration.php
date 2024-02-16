<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ItemQuantityMigration extends AbstractMigration
{
    public function up() {
        $this->execute("
            -- Alter table items
            ALTER TABLE `items` ADD `max_quantity` INT(11) NOT NULL DEFAULT '1';
        ");
    }

    public function down() {
        $this->execute("
            -- Alter table items
            ALTER TABLE `items` DROP `max_quantity`;
        ");
    }
}
