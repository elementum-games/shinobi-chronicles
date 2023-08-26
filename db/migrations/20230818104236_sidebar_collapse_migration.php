<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SidebarCollapseMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        // Start Transaction
        $this->execute("START TRANSACTION");

        // Modify missions table
        $this->execute("
            ALTER TABLE `user_settings`
                ADD `sidebar_collapse` varchar(50) NOT NULL DEFAULT 'closed';
        ");

        // Commit Transaction
        $this->execute("COMMIT");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        // Start Transaction
        $this->execute("START TRANSACTION");

        // Modify missions table
        $this->execute("
            ALTER TABLE `user_settings`
                DROP `sidebar_collapse`;
        ");

        // Commit Transaction
        $this->execute("COMMIT");
    }
}
