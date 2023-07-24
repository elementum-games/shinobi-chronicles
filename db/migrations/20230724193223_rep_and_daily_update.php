<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RepAndDailyUpdate extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up(): void {
        $this->execute("ALTER TABLE `users` 
            ADD COLUMN `pvp_rep` INT DEFAULT 0 AFTER `weekly_rep`,
            ADD COLUMN `last_pvp_rep_reset` INT DEFAULT 0 AFTER `pvp_rep`,
            ADD COLUMN `recent_killer_ids` TEXT DEFAULT NULL AFTER `last_pvp_rep_reset`;");
    }

    public function down(): void {
        $this->execute("ALTER TABLE `users` 
            DROP COLUMN `pvp_rep`,
            DROP COLUMN `last_pvp_rep_reset`,
            DROP COLUMN `recent_killer_ids`;");
    }
}
