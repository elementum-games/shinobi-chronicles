<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MonthlyVillagePointsMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void {
        $this->execute("
            -- Alter table villages
            ALTER TABLE `villages` ADD `monthly_points` SMALLINT(6) NOT NULL DEFAULT '0';
            ALTER TABLE `villages` ADD `prev_monthly_points` SMALLINT(6) NOT NULL DEFAULT '0';

            -- Alter table users
            ALTER TABLE `users` ADD `prev_monthly_pvp` SMALLINT(6) NOT NULL DEFAULT '0';

            -- Alter table teams
            ALTER TABLE `teams` ADD `prev_monthly_points` SMALLINT(6) NOT NULL DEFAULT '0';
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void {
        $this->execute("
            -- Alter table villages
            ALTER TABLE `villages` DROP `monthly_points`;
            ALTER TABLE `villages` DROP `prev_monthly_points`;

            -- Alter table users
            ALTER TABLE `users` DROP `prev_monthly_pvp`;

            -- Alter table teams
            ALTER TABLE `teams` DROP `prev_monthly_points`;
        ");
    }
}
