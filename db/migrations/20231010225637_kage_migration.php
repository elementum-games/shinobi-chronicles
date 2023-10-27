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
            `policy_id` INT(11) NULL DEFAULT NULL,
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
            `policy_id` INT(11) NOT NULL,
            `start_time` INT(11) NOT NULL,
            `end_time` INT(11) NULL DEFAULT NULL,
            PRIMARY KEY (`log_id`));

            -- Add table challenge_requests
            CREATE TABLE `shinobi_chronicles`.`challenge_requests` (
            `request_id` INT(11) NOT NULL AUTO_INCREMENT,
            `challenger_id` INT(11) NOT NULL,
            `seat_holder_id` INT(11) NOT NULL,
            `scheduled_battle_id` INT(11) NOT NULL,
            `seat_id` INT(11) NOT NULL,
            `created_time` INT NOT NULL,
            `accepted_time` INT NULL DEFAULT NULL,
            `start_time` INT NULL DEFAULT NULL,
            `end_time` INT NULL DEFAULT NULL,
            `seat_holder_locked` TINYINT(1) NOT NULL DEFAULT 0,
            `challenger_locked` TINYINT(1) NOT NULL DEFAULT 0,
            `selected_times` VARCHAR(200) NOT NULL DEFAULT '[]',
            `battle_id` INT(11) NULL DEFAULT NULL,
            `winner` VARCHAR(20) NULL DEFAULT NULL,
            PRIMARY KEY (`request_id`));

            -- Alter table villages
            ALTER TABLE `villages` ADD `policy_id` INT(11) NOT NULL DEFAULT '0';

            -- Alter table player_seats
            ALTER TABLE `village_seats` ADD `is_provisional` TINYINT(1) NOT NULL DEFAULT 0;

            -- Alter table notifications
            ALTER TABLE `notifications` ADD `expires` INT(11) NULL DEFAULT NULL;

            -- Alter table operations
            ALTER TABLE `operations` CHANGE `last_update` `last_update_ms` BIGINT(14) NOT NULL;

            -- Update village name
            UPDATE `region_locations` SET `name` = 'Nekogakure' WHERE `id` = 29;

            -- Alter table users
            ALTER TABLE `users` ADD `locked_challenge` INT(11) NOT NULL DEFAULT 0;
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

            -- Drop table challenge_requests
            DROP TABLE `challenge_requests`;

            -- Drop column policy_id from villages
            ALTER TABLE `villages` DROP COLUMN `policy_id`;

            -- Drop column is_provisional from village_seats;
            ALTER TABLE `village_seats` DROP COLUMN `is_provisional`;

            -- Drop column `expires` from notifications
            ALTERT TABLE `notifications` DROP COLUMN `expires`;

            -- Alter table operations
            ALTER TABLE `operations` CHANGE `last_update_ms` `last_update` INT(11) NOT NULL;

            -- Alter table users
            ALTER TABLE `users` DROP column `locked_challenge`;
        ");
    }
}