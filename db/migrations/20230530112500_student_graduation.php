<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class StudentGraduation extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        // Modify sensei table
        $this->execute("
            ALTER TABLE `sensei`
                ADD COLUMN `graduated_students` VARCHAR(500) NOT NULL DEFAULT '[]',
                CHANGE `graduated` `graduated_count` INT(11) NOT NULL DEFAULT '0'
        ");
    }
}
