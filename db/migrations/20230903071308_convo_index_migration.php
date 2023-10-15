<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ConvoIndexMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        // Start Transaction
        $this->execute("START TRANSACTION");

        // Modify Convo_Users table
        $this->execute("ALTER TABLE `convos_users` ADD INDEX(`user_id`)");
        $this->execute("ALTER TABLE `convos_users` ADD INDEX(`last_read`)");
        $this->execute("ALTER TABLE `convos_users` ADD INDEX(`muted`)");
        $this->execute("ALTER TABLE `convos_users` ADD INDEX(`convo_id`)");

        // Modify Convo_Messages table
        $this->execute("ALTER TABLE `convos_messages` ADD INDEX(`time`)");
        $this->execute("ALTER TABLE `convos_messages` ADD INDEX(`convo_id`)");

        // Commit Transaction
        $this->execute("COMMIT");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        // Start Transaction
        $this->execute("START TRANSACTION");

        // Modify Convo_Users table
        $this->execute("DROP INDEX `user_id` ON `convos_users`");
        $this->execute("DROP INDEX `last_read` ON `convos_users`");
        $this->execute("DROP INDEX `muted` ON `convos_users`");
        $this->execute("DROP INDEX `convo_id` ON `convos_users`");

        // Modify Convo_Messages table
        $this->execute("DROP INDEX `time` ON `convos_messages`");
        $this->execute("DROP INDEX `convo_id` ON `convos_messages`");

        // Commit Transaction
        $this->execute("COMMIT");
    }
}
