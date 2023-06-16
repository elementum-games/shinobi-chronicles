<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SettingsUpdateMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        // User Settings table, update column name and default
        $this->execute("
            ALTER TABLE `user_settings`
                CHANGE `allow_keybinds` `enable_keybinds` TINYINT(1) NOT NULL DEFAULT 0,
                CHANGE `allow_alerts` `enable_alerts` TINYINT(1) NOT NULL DEFAULT 0;
        ");

        // User Settings table, reset columns to default
        $this->execute("
            UPDATE TABLE `user_settings` 
                SET `enable_keybinds` = 0,
                `enable_alerts` = 0;
        ");
    }
}
