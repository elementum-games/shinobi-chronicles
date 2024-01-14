<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class BattleLogRoundMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("
            -- Alter table battle_logs
            ALTER TABLE `battle_logs` ADD `round_number` SMALLINT(6) NOT NULL DEFAULT '1';
            ALTER TABLE `battle_logs` DROP INDEX `battle_logs_turn`;
            ALTER TABLE `battle_logs` ADD UNIQUE INDEX `battle_logs_turn` (`battle_id`, `turn_number`, `round_number`);
            ALTER TABLE `battle_logs` DROP INDEX `battle_logs_latest_turn`;
            ALTER TABLE `battle_logs` ADD INDEX `battle_logs_latest_turn` (`battle_id`, `turn_number`, `round_number`);

            -- Alter table battles
            ALTER TABLE `battles` MODIFY `rounds` SMALLINT(6) NOT NULL DEFAULT '1';
            ALTER TABLE `battles` MODIFY `round_count` SMALLINT(6) NOT NULL DEFAULT '1';
            ALTER TABLE `battles` MODIFY `team1_wins` SMALLINT(6) NOT NULL DEFAULT '0';
            ALTER TABLE `battles` MODIFY `team2_wins` SMALLINT(6) NOT NULL DEFAULT '0';
            UPDATE `battles` SET `round_count` = 1 WHERE `round_count` = 0;
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("
            -- Alter table battle_logs
            ALTER TABLE `battle_logs` DROP `round_number;
            ALTER TABLE `battle_logs` DROP INDEX `battle_logs_turn`;
            ALTER TABLE `battle_logs` ADD UNIQUE INDEX `battle_logs_turn` (`battle_id`, `turn_number`);
            ALTER TABLE `battle_logs` DROP INDEX `battle_logs_latest_turn`;
            ALTER TABLE `battle_logs` ADD INDEX `battle_logs_latest_turn` (`battle_id`, `turn_number`);

            -- Alter table battles
            ALTER TABLE `battles` MODIFY `rounds` INT(11) NOT NULL DEFAULT '1';
            ALTER TABLE `battles` MODIFY `round_count` INT(11) NOT NULL DEFAULT '0';
            ALTER TABLE `battles` MODIFY `team1_wins` INT(11) NOT NULL DEFAULT '0';
            ALTER TABLE `battles` MODIFY `team2_wins` INT(11) NOT NULL DEFAULT '0';
        ");
    }
}
