<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUserIdIndexToChat extends AbstractMigration {
    public function up() {
        $this->execute("ALTER TABLE `chat` ADD INDEX (`user_id`);");
    }

    public function down() {
        $this->execute("DROP INDEX `user_id` ON `chat`;");
    }
}
