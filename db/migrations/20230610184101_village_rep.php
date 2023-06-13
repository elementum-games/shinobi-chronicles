<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class VillageRep extends AbstractMigration
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
            ADD COLUMN `village_rep` INT NOT NULL DEFAULT 0,
            ADD COLUMN `weekly_rep` INT NOT NULL DEFAULT 0,
            ADD COLUMN `mission_rep_cd` INT NOT NULL DEFAULT 0;");
    }

    public function down(): void {
        $this->execute("ALTER TABLE `users` 
            DROP COLUMN `village_rep`,
            DROP COLUMN `weekly_rep`,
            DROP COLUMN `mission_rep_cd`;");
    }
}
