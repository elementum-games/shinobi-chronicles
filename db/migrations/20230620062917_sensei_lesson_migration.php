<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SenseiLessonMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        // Modify sensei table
        $this->execute("
            ALTER TABLE `sensei`
                ADD `temp_students` VARCHAR(50) NOT NULL DEFAULT '[]',
                ADD `time_trained` INT(13) NOT NULL DEFAULT '0',
                ADD `yen_gained` INT(11) NOT NULL DEFAULT '0',
                ADD `is_active` TINYINT(1) NOT NULL DEFAULT '1',
                ADD `enable_lessons` TINYINT(1) NOT NULL DEFAULT '1';
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        // Modify sensei table
        $this->execute("
            ALTER TABLE `sensei`
                DROP `temp_students`,
                DROP `time_trained`,
                DROP `yen_gained`,
                DROP `is_active`,
                DROP `enable_lessons`;
        ");
    }

}
