<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveTravelSettings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("
            ALTER TABLE `user_settings`
                DROP `travel_animation`,
                DROP `travel_grid`;
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("
            ALTER TABLE `user_settings`
                ADD `travel_animation` VARCHAR(50) NOT NULL DEFAULT 'smooth',
                ADD `travel_grid` VARCHAR(50) NOT NULL DEFAULT 'visible';
        ");
    }
}
