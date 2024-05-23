<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MiscErrorLogs extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("
            -- Create table for misc. error logging not logged by php/mysql
            CREATE TABLE `error_logs` (
                `log_id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `log_type` VARCHAR(25) NOT NULL,
                `content` TEXT NOT NULL,
                `time` INT NOT NULL
            );
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("
            -- Drop misc logs
            DROP TABLE `error_logs`;
        ");
    }
}
