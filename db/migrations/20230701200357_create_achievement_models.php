<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAchievementModels extends AbstractMigration
{

    public function up(): void {
        $this->execute("START TRANSACTION;");

        $this->execute("
            CREATE TABLE `user_achievements` (
                `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `achievement_id` VARCHAR(256) NOT NULL,
                `user_id` INT NOT NULL,
                `achieved_at` int NOT NULL
            );

            CREATE TABLE `user_achievements_progress` (
                `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `achievement_id` VARCHAR(256) NOT NULL,
                `user_id` INT NOT NULL,
                `progress_data` VARCHAR(2000) NOT NULL
            );

            CREATE TABLE `world_first_achievements` (
                `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `achievement_id` VARCHAR(256) NOT NULL,
                `user_id` INT NOT NULL,
                `achieved_at` int NOT NULL
            );

        ");

        $this->execute("COMMIT;");
    }

    public function down(): void {
        $this->execute("START TRANSACTION;");

        $this->execute("
            DROP TABLE `user_achievements`;
            DROP TABLE `user_achievements_progress`;
            DROP TABLE `world_first_achievements`;

        ");

        $this->execute("COMMIT;");
    }
}
