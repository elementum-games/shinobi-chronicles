<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UserSettingsMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        // User Settings table
        $this->execute("
            CREATE TABLE `user_settings` (
              `user_id` int(11) NOT NULL,
              `action_keybinds` varchar(2000) NOT NULL DEFAULT '[]',
              `jutsu_keybinds` varchar(2000) NOT NULL DEFAULT '[]',
              `avatar_style` varchar(100) NOT NULL DEFAULT 'round',
              `sidebar_position` varchar(50) NOT NULL DEFAULT 'left',
              `allow_keybinds` tinyint(1) NOT NULL DEFAULT 1,
              `allow_alerts` tinyint(1) NOT NULL DEFAULT 1
            )
        ");

        // Set primary key
        $this->execute("
            ALTER TABLE `user_settings`
                ADD PRIMARY KEY (`user_id`);
        ");
    }
}

