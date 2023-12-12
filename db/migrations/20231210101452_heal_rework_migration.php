<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class HealReworkMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("
            -- Alter battles table
            ALTER TABLE `battles` ADD `player1_last_damage_taken` INT(11) NOT NULL DEFAULT '0';
            ALTER TABLE `battles` ADD `player2_last_damage_taken` INT(11) NOT NULL DEFAULT '0';
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("
            -- Alter table battles
            ALTER TABLE `battles` DROP COLUMN `player1_last_damage_taken`;
            ALTER TABLE `battles` DROP COLUMN `player2_last_damage_taken`;
        ");
    }
}
