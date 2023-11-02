<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class BlacklistMiscChanges extends AbstractMigration
{
    public function up(): void {
        $this->execute("
            ALTER TABLE `chat` 
                ADD `user_id` INT(11) NOT NULL DEFAULT 0 AFTER `post_id`,
                ADD `deleted` SMALLINT(2) NOT NULL DEFAULT 0 AFTER `edited`;

            ALTER TABLE `users`
                ADD `last_login_attempt` INT(11) NOT NULL DEFAULT 0 AFTER `last_login`;
        ");
    }

    public function down(): void {
        $this->execute("
            ALTER TABLE `chat`
                DROP COLUMN `user_id`,
                DROP COLUMN `deleted`;
                     
            ALTER TABLE `users`
                DROP COLUMN `last_login_attempt`;
        ");
    }
}
