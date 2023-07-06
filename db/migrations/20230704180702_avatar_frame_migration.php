<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AvatarFrameMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        // Modify user_settings table
        $this->execute("
            ALTER TABLE `user_settings`
                ADD `avatar_frame` VARCHAR(100) NOT NULL DEFAULT 'default';
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        // Modify user_settings table
        $this->execute("
            ALTER TABLE `user_settings`
                DROP `avatar_frame`;
        ");
    }
}
