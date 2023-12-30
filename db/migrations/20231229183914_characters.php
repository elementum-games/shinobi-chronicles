<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Characters extends AbstractMigration
{
    public function up() {
        $this->execute("
            -- Change user table to characters
            RENAME TABLE `users` TO `characters`;
            
            -- Add character data into table
            ALTER TABLE `characters` DROP PRIMARY KEY, CHANGE `user_id` `user_id` INT NOT NULL;
            ALTER TABLE `characters`
                ADD `character_id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT FIRST;
                
            -- Add indexing to character_id
            ALTER TABLE `characters` ADD INDEX (`staff_level`);

            -- Add character_id to user_records
            ALTER TABLE `user_record`
                ADD `character_id` INT NOT NULL AFTER `user_id`;

            -- Add character id to existing record, this is needed to maintain reversibility
            UPDATE `user_record`
                SET `character_id` = (SELECT `character_id` FROM `characters` WHERE `characters`.`user_id` = `user_record`.`user_id`);

            -- Add character_id to reports
            ALTER TABLE `reports`
                ADD `character_id` INT NOT NULL AFTER `user_id`;

            -- Add character id to existing reports, this is needed to maintain reversibility
            UPDATE `reports`
                SET `character_id` = (SELECT `character_id` FROM `characters` WHERE `characters`.`user_id` = `reports`.`user_id`);
            
            -- Create new users table
            CREATE TABLE `users` (
                `user_id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `user_name` VARCHAR(40) NOT NULL,
                `password` VARCHAR(256) NOT NULL,
                `staff_level` SMALLINT(6) NOT NULL DEFAULT 0
            );
            
            -- Populate new user table
            INSERT INTO `users` (`user_id`, `user_name`, `password`, `staff_level`)
            SELECT `user_id`, `user_name`, `password`, `staff_level` FROM `characters`;
            
            -- Add indexing to users
            ALTER TABLE `users` ADD INDEX (`user_name`);
            
            -- Remove user data from characters
            ALTER TABLE `characters`
                DROP `password`;
        ");
    }

    public function down() {
        $this->execute("
            -- Create essential data in characters
            ALTER TABLE `characters`
                ADD `password` VARCHAR(256) NOT NULL AFTER `user_name`;
                
            -- Migrate user data into character data
            UPDATE `characters` 
                SET `password` = (select `password` from `users` where `characters`.`user_id` = `users`.`user_id`);
            
            -- Drop character data from characters
            ALTER TABLE `characters`
                DROP PRIMARY KEY,
                DROP `user_id`,
                CHANGE `character_id` `user_id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT;

            -- Update user record and attach them to appropriate user
            ALTER TABLE `user_record`
                DROP `user_id`,
                CHANGE `character_id` `user_id` INT NOT NULL;

            -- Update reports and attach them to appropriate user
            ALTER TABLE `reports`
                DROP `user_id`,
                CHANGE `character_id` `user_id` INT NOT NULL;
                
            -- Remove new users table
            DROP TABLE `users`;
            
            -- Revert characters back to users
            RENAME TABLE `characters` TO `users`;

            -- Remove staff_level indexing from users
            DROP INDEX `staff_level` ON `users`;
        ");
    }
}
