<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MissionRewardMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        // Modify missions table
        $this->execute("
            ALTER TABLE `missions` 
                ADD `rewards` VARCHAR(500) NOT NULL DEFAULT '[]';
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        // Modify missions table
        $this->execute("
            ALTER TABLE `news_posts`
                DROP `rewards`;
        ");
    }
}
