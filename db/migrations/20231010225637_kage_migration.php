<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class KageMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("
            -- Add table proposals
            CREATE TABLE `proposals` (
            `proposal_id` INT(11) NOT NULL AUTO_INCREMENT,
            `village_id` INT(11) NOT NULL,
            `user_id` INT(11) NOT NULL,
            `name` VARCHAR(200) NOT NULL,
            `start_time` INT(11) NOT NULL,
            `end_time` INT(11) NULL DEFAULT NULL,
            `result` VARCHAR(20) NULL DEFAULT NULL,
            `type` VARCHAR(20) NOT NULL,
            `target_village_id` INT(11) NULL DEFAULT NULL,
            `policy_id` INT(11) NULL DEFAULT NULL
            PRIMARY KEY (`proposal_id`));

            -- Add table vote_logs
            CREATE TABLE `vote_logs` (
            `vote_id` INT(11) NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) NOT NULL,
            `proposal_id` INT(11) NOT NULL,
            `vote` INT(11) NOT NULL,
            `rep_adjustment` INT(11) NOT NULL,
            `vote_time` INT(11) NOT NULL,
            PRIMARY KEY (`vote_id`));

            -- Add table policy_logs
            CREATE TABLE `vote_logs` (
            `log_id` INT(11) NOT NULL AUTO_INCREMENT,
            `village_id` INT(11) NOT NULL,
            `policy` INT(11) NOT NULL,
            `start_time` INT(11) NOT NULL,
            `end_time` NULL DEFAULT NULL,
            PRIMARY KEY (`log_id`));

            -- Alter table villages
            ALTER TABLE `villages` ADD `policy` INT(11) NOT NULL DEFAULT '0';

            -- Alter table player_seats
            ALTER TABLE `village_seats` ADD `is_provisional` TINYINT(1) NOT NULL DEFAULT 0;
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("
            -- Drop table proposals
            DROP TABLE `proposals`;

            -- Drop table vote_logs
            DROP TABLE `vote_logs`;

            -- Drop table policy_logs
            DROP TABLE `policy_logs`;

            -- Drop column policy from villages
            ALTER TABLE `villages` DROP COLUMN `policy`;

            -- Drop column is_provisional from village_seats;
            ALTER TABLE `village_seats` DROP COLUMN `is_provisional`;
        ");
    }
}