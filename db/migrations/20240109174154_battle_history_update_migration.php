<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class BattleHistoryUpdateMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("
            -- Alter table battle_logs
            ALTER TABLE `battle_logs` ADD `fighter_health` TEXT NOT NULL;
            ALTER TABLE `battle_logs` ADD `active_effects` TEXT NOT NULL;
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("
            -- Alter table battle_logs
            ALTER TABLE `battle_logs` DROP `fighter_health`;
            ALTER TABLE `battle_logs` DROP `active_effects`;
        ");
    }
}
