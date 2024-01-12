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
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void {
        $this->execute("
            -- Alter table villages
            ALTER TABLE `villages` DROP `monthly_points`;
        ");
    }
}
