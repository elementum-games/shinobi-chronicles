<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ModStats extends AbstractMigration
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
    public function up(): void
    {
        $this->execute("
            ALTER TABLE `staff_logs` ADD `staff_id` INT NOT NULL AFTER `log_id`;
            ALTER TABLE `staff_logs` ADD INDEX (`staff_id`, `time`);
        ");
    }

    public function down(): void
    {
        $this->execute("
            DROP INDEX `time` ON `staff_logs`;
            ALTER TABLE `staff_logs` DROP `staff_id`;
        ");
    }
}
