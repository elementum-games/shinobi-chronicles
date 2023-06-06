<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class NotificationAttributesMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        // add new columns
        $this->execute("
            ALTER TABLE `notifications`
                ADD COLUMN `attributes` VARCHAR(200) NOT NULL DEFAULT '[]'
        ");
    }
}
