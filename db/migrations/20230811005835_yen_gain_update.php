<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class YenGainUpdate extends AbstractMigration
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
        // Locked out accounts
       $this->execute("ALTER TABLE `users`
            ADD COLUMN `login_attempt_time` INT NOT NULL DEFAULT 0 AFTER `failed_logins`,
            ADD COLUMN `last_malicious_ip` TEXT AFTER `login_attempt_time`
        ");
        // Chat deletion
        $this->execute("ALTER TABLE `chat` ADD COLUMN `deleted` INT(1) NOT NULL DEFAULT 0");
    }
    public function down(): void
    {
        // Locked out accounts
        $this->execute("ALTER TABLE `users`
            DROP COLUMN `login_attempt_time`,
            DROP COLUMN `last_malicious_ip`
        ");
        // Chat deletion
        $this->execute("ALTER TABLE `chat` DROP COLUMN `deleted`");
    }
}
