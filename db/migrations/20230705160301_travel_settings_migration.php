<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class TravelSettingsMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        // Modify user_settings table
        $this->execute("
            ALTER TABLE `user_settings`
                ADD `travel_animation` VARCHAR(50) NOT NULL DEFAULT 'smooth',
                ADD `travel_grid` VARCHAR(50) NOT NULL DEFAULT 'visible',
                ADD `card_image` varchar(100) NOT NULL DEFAULT './images/default_avatar.png',
                ADD `banner_image` varchar(100) NOT NULL DEFAULT './images/default_avatar.png';
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
                DROP `travel_animation`,
                DROP `travel_grid`,
                DROP `card_image`,
                DROP `banner_image`;
        ");
    }
}
