<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SenseiMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        // sensei table
        $this->execute("
            CREATE TABLE `sensei` (
                `sensei_id` int(11) NOT NULL,
                `students` varchar(50) NOT NULL DEFAULT '[]',
                `graduated` int(11) NOT NULL DEFAULT 0,
                `specialization` varchar(50) NOT NULL,
                `recruitment_message` varchar(100) NOT NULL,
                `student_message` varchar(500) NOT NULL
            )
        ");

        // student applications table
        $this->execute("
            CREATE TABLE `student_applications` (
                `sensei_id` int(11) NOT NULL,
                `student_id` int(11) NOT NULL
            )
        ");

        // add new columns
        $this->execute("
            ALTER TABLE `users`
                ADD COLUMN `sensei_id` int(11) NOT NULL,
                ADD COLUMN `accept_students` tinyint(1) NOT NULL DEFAULT 1
        ");
    }
}
