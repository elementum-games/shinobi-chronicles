<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class BattleTimerMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        // Modify battles table
        $this->execute("
            ALTER TABLE `battles`
                ADD `player1_time` int(11) NOT NULL DEFAULT 0,
                ADD `player2_time` int(11) NOT NULL DEFAULT 0,
                ADD `is_retreat` tinyint(1) NOT NULL DEFAULT 0;
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        // Modify battles table
        $this->execute("
            ALTER TABLE `battles`
                DROP `player1_time`,
                DROP `player2_time`,
                DROP `is_retreat`;
        ");
    }
}
