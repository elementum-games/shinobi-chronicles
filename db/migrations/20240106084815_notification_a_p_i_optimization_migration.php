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
        ");
    }
}
