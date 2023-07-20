<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class PvpRepUpdate extends AbstractMigration
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
            ADD COLUMN `recent_players_killed_ids` TEXT DEFAULT NULL AFTER `mission_rep_cd`;");
    }

    public function down(): void {
        $this->execute("ALTER TABLE `users` 
            DROP COLUMN `recent_players_killed_ids`;");
    }
}
