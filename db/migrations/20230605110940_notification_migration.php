<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class NotificationMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        // Notification table
        $this->execute("
            CREATE TABLE `notifications` (
              `notification_id` int(11) NOT NULL,
              `user_id` int(11) NOT NULL,
              `type` varchar(50) NOT NULL,
              `message` varchar(200) NOT NULL,
              `alert` tinyint(1) NOT NULL DEFAULT 0,
              `created` int(11) NOT NULL,
              `duration` int(11) DEFAULT NULL
            )
        ");

        // Set primary key
        $this->execute("
            ALTER TABLE `notifications`
                ADD PRIMARY KEY (`notification_id`);
        ");

        // Set auto increment
        $this->execute("
            ALTER TABLE `notifications`
                MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;
        ");
    }
}
