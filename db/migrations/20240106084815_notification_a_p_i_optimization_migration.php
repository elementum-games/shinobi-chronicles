<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class NotificationAPIOptimizationMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("
            -- Alter table operations
            CREATE INDEX `user_id` ON operations (user_id);
            CREATE INDEX `last_update_ms` ON operations (last_update_ms);
            CREATE INDEX `status` ON operations (status);
            CREATE INDEX `type` ON operations (type);
            CREATE INDEX `target_id` ON operations (target_id);

            -- Alter table loot
            CREATE INDEX `user_id` ON loot (user_id);
            CREATE INDEX `battle_id` ON loot (battle_id);
            CREATE INDEX `resource_id` ON loot (resource_id);
            CREATE INDEX `target_village_id` ON loot (target_village_id);
            CREATE INDEX `claimed_village_id` ON loot (claimed_village_id);
            CREATE INDEX `claimed_time` ON loot (claimed_time);
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("
            -- Alter table operations
            DROP INDEX `user_id` ON operations;
            DROP INDEX `last_update_ms` ON operations;
            DROP INDEX `status` ON operations;
            DROP INDEX `type` ON operations;
            DROP INDEX `target_id` ON operations;

            -- Alter table loot
            DROP INDEX `user_id` ON loot (user_id);
            DROP INDEX `battle_id` ON loot (battle_id);
            DROP INDEX `resource_id` ON loot (resource_id);
            DROP INDEX `target_village_id` ON loot (target_village_id);
            DROP INDEX `claimed_village_id` ON loot (claimed_village_id);
            DROP INDEX `claimed_time` ON loot (claimed_time);
        ");
    }
}
