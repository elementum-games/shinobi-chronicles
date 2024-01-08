<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class BattleRoundMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("
            -- Alter table battles
            ALTER TABLE `battles` ADD `rounds` INT(11) NOT NULL DEFAULT '1';
            ALTER TABLE `battles` ADD `current_round` INT(11) NOT NULL DEFAULT '1';
            ALTER TABLE `battles` ADD `team1_wins` INT(11) NOT NULL DEFAULT '0';
            ALTER TABLE `battles` ADD `team2_wins` INT(11) NOT NULL DEFAULT '0';
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("
            -- Alter table battles
            ALTER TABLE `battles` DROP `rounds`;
            ALTER TABLE `battles` DROP `current_round`;
            ALTER TABLE `battles` DROP `team1_wins`;
            ALTER TABLE `battles` DROP `team2_wins`;
        ");
    }
}
