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
            ALTER TABLE `ai_opponents` ADD `difficulty_level` VARCHAR(20) NOT NULL DEFAULT 'none',
            ALTER TABLE `ai_opponents` ADD `arena_enabled` TINYINT(1) NOT NULL DEFAULT '0';
            ALTER TABLE `ai_opponents` ADD `is_patrol` TINYINT(1) NOT NULL DEFAULT '0';
            ALTER TABLE `ai_opponents` ADD `avatar_link` VARCHAR(100) NOT NULL;

            -- Alter table users
            ALTER TABLE `users` ADD `ai_cooldowns` VARCHAR(200) NOT NULL DEFAULT '[]';

            -- Alter table battles
            ALTER TABLE `battles` ADD `battle_background_link` VARCHAR(100) NOT NULL;

            -- Alter table regions
            ALTER TABLE `regions` ADD `battle_background_link` VARCHAR(100) NOT NULL;

            -- Alter table maps_locations
            ALTER TABLE `maps_locations` ADD `battle_background_link` VARCHAR(100) NOT NULL;
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
            ALTER TABLE `ai_opponents` DROP `difficulty_level`,
            ALTER TABLE `ai_opponents` DROP `arena_enabled`;
            ALTER TABLE `ai_opponents` DROP `is_patrol`;
            ALTER TABLE `ai_opponents` DROP `avatar_link`;

            -- Alter table users
            ALTER TABLE `users` DROP `ai_cooldowns`;

            -- Alter table battles
            ALTER TABLE `battles` DROP `battle_background_link`;

            -- Alter table regions
            ALTER TABLE `regions` DROP `battle_background_link`;

            -- Alter table maps_locations
            ALTER TABLE `maps_locations` DROP `battle_background_link`;
        ");
    }
}
