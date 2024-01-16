<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class GlobalMessageUpdate extends AbstractMigration
{
    public function up() {
        $this->execute("
            ALTER TABLE `system_storage`
                ADD COLUMN `maintenance_begin_time` INT NOT NULL,
                ADD COLUMN `maintenance_end_time` INT NOT NULL;
        ");
    }

    public function down() {
        $this->execute("
            ALTER TABLE `system_storage`
                DROP COLUMN `maintenance_begin_time`,
                DROP COLUMN `maintenance_end_time`;
        ");
    }
}
