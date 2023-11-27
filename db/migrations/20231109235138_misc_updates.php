<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MiscUpdates extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            ALTER TABLE `users` ADD COLUMN `blocked_notifications` TEXT NOT NULL;
        ");
    }

    public function down(): void {
        $this->execute("
            ALTER TABLE `users` DROP `blocked_notifications`;
        ");
    }
}
