<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddStatTransferColumns extends AbstractMigration
{
    public function up() {
        $this->execute("
            ALTER TABLE `users` add column `stat_transfer_amount` int DEFAULT 0;
            ALTER TABLE `users` add column `stat_transfer_completion_time` INT DEFAULT 0;
            ALTER TABLE `users` add column `stat_transfer_target_stat` VARCHAR(128) DEFAULT '';
        ");
    }

    public function down() {
        $this->execute("
            ALTER TABLE `users` drop column `stat_transfer_amount`;
            ALTER TABLE `users` drop column `stat_transfer_completion_time`;
            ALTER TABLE `users` drop column `stat_transfer_target_stat`;
        ");
    }
}
