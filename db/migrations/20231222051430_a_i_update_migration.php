<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AIUpdateMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("
            -- Alter table ai_opponents
            ALTER TABLE `ai_opponents` ADD `shop_jutsu` VARCHAR(100) NOT NULL DEFAULT '0';
            ALTER TABLE `ai_opponents` ADD `shop_jutsu_priority` VARCHAR(100) NOT NULL DEFAULT '0';
            ALTER TABLE `ai_opponents` ADD `battle_iq` INT(11) NOT NULL DEFAULT '0';
            ALTER TABLE `ai_opponents` ADD `scaling` TINYINT(1) NOT NULL DEFAULT '0';
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("
            -- Alter table ai_opponents
            ALTER TABLE `ai_opponents` DROP `shop_jutsu`;
            ALTER TABLE `ai_opponents` DROP `shop_jutsu_priority`;
            ALTER TABLE `ai_opponents` DROP `battle_iq`;
            ALTER TABLE `ai_opponents` DROP `scaling`;
        ");
    }
}
