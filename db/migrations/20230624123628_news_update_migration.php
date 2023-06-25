<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class NewsUpdateMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        // Modify news_posts table
        $this->execute("
            ALTER TABLE `news_posts`
                ADD `tags` VARCHAR(100) NOT NULL DEFAULT '[]',
                ADD `version` VARCHAR(100) DEFAULT NULL;
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        // Modify news_posts table
        $this->execute("
            ALTER TABLE `news_posts`
                DROP `tags`,
                DROP `version`;
        ");
    }
}
