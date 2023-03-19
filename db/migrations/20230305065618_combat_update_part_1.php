<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CombatUpdatePart1 extends AbstractMigration {
    public function up(): void {
        $this->execute("
            ALTER TABLE `battles` ADD COLUMN `turn_type` VARCHAR(128) default 'movement' not null AFTER `turn_count`;
            alter table `jutsu` add column `target_type` varchar(64) default 'tile' after `use_type`;
            alter table `battle_logs` add `fighter_action_logs` text null after content;
            alter table `battle_logs` add `turn_phase` varchar(128) default 'movement' not null;
        ");
    }

    public function down(): void {
        $this->execute("
            ALTER TABLE `battles` drop column `turn_type`;
            alter table `jutsu` drop column `target_type`;
            alter table `battle_logs` drop column `fighter_action_logs`;
            alter table `battle_logs` drop column `turn_phase`;
        ");
    }
}
