<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MultipleEffectsMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("
            -- ALTER TABLE `jutsu`
            ALTER TABLE `jutsu` ADD `effect2` VARCHAR(50) NOT NULL DEFAULT 'none';
            ALTER TABLE `jutsu` ADD `effect2_amount` FLOAT NOT NULL DEFAULT 0;
            ALTER TABLE `jutsu` ADD `effect2_length` TINYINT(4) NOT NULL DEFAULT 0;
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("
            -- ALTER TABLE `jutsu`
            ALTER TABLE `jutsu` DROP COLUMN `effect2`;
            ALTER TABLE `jutsu` DROP COLUMN `effect2_amount`;
            ALTER TABLE `jutsu` DROP COLUMN `effect2_length`;
        ");
    }
}
