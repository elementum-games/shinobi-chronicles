<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class LinkedJutsuMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("
            -- Alter jutsu table
            ALTER TABLE `jutsu` ADD `linked_jutsu_id` INT(11) NOT NULL DEFAULT 0;
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("
            -- Alter table jutsu
            ALTER TABLE `jutsu` DROP COLUMN `linked_jutsu_id`;
        ");
    }
}
