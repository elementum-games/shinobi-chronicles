<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class VillageTradeMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("
            -- Alter table proposals
            ALTER TABLE `proposals` ADD `trade_data` VARCHAR(1000) NULL DEFAULT NULL;
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("
            -- Alter table proposals
            ALTER TABLE `proposals` DROP `trade_data`;
        ");
    }
}
