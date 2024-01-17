<?php

use Phinx\Seed\AbstractSeed;

class BaseStructureAndData extends AbstractSeed {
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void {
        // Don't run if users table already exists
        $stmt = $this->query("SHOW TABLES LIKE 'users'");
        $rows = $stmt->rowCount();

        if($rows > 0) {
            echo "\r\n::STOPPING - `users` table found. Please empty DB to run seed::\r\n";
            return;
        }

        $this->execute(
            "
            CREATE TABLE IF NOT EXISTS `ai_opponents` (
                `ai_id` int(11) NOT NULL AUTO_INCREMENT,
              `rank` smallint(6) NOT NULL,
              `money` int(11) NOT NULL,
              `name` varchar(50) NOT NULL,
              `max_health` float(12,2) DEFAULT NULL,
              `level` smallint(6) NOT NULL,
              `ninjutsu_skill` double(12,2) NOT NULL,
              `genjutsu_skill` double(12,2) NOT NULL,
              `taijutsu_skill` double(12,2) NOT NULL,
              `cast_speed` double(12,2) NOT NULL,
              `speed` double(12,2) NOT NULL,
              `strength` double(12,2) NOT NULL,
              `intelligence` double(12,2) NOT NULL,
              `willpower` double(12,2) NOT NULL,
              `moves` text NOT NULL,
              PRIMARY KEY (`ai_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=`latin1`;
        "
        );

        $this->execute(
            "CREATE TABLE IF NOT EXISTS `banned_ips` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(64) DEFAULT NULL,
  `ban_level` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=`latin1`;"
        );
        $this->execute(
            "
        CREATE TABLE IF NOT EXISTS `battles` (
    `battle_id` int(11) NOT NULL AUTO_INCREMENT,
  `battle_type` smallint(6) NOT NULL,
  `start_time` int(11) DEFAULT '0',
  `turn_time` int(11) DEFAULT NULL,
  `turn_count` smallint(6) NOT NULL DEFAULT '0',
  `winner` varchar(32) DEFAULT NULL,
  `player1` varchar(64) NOT NULL,
  `player2` varchar(64) NOT NULL,
  `fighter_health` text NOT NULL,
  `fighter_actions` text NOT NULL,
  `field` text NOT NULL,
  `active_effects` text NOT NULL,
  `active_genjutsu` text NOT NULL,
  `jutsu_cooldowns` text NOT NULL,
  `fighter_jutsu_used` text NOT NULL,
  PRIMARY KEY (`battle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=`latin1`;
        "
        );
        $this->execute(
            "
        CREATE TABLE IF NOT EXISTS `battle_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `battle_id` int(11) NOT NULL,
  `turn_number` smallint(6) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `battle_logs_turn` (`battle_id`,`turn_number`),
  KEY `battle_logs_latest_turn` (`battle_id`,`turn_number`)
) ENGINE=InnoDB DEFAULT CHARSET=`utf8`;
        "
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `blacklist` (
    `user_id` int(11) NOT NULL,
  `blocked_ids` text CHARACTER SET `latin1` COLLATE `latin1_spanish_ci` NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=`latin1`;"
        );
        $this->execute(
            "
        CREATE TABLE IF NOT EXISTS `bloodlines` (
    `bloodline_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `clan_id` int(11) NOT NULL,
  `village` varchar(24) NOT NULL,
  `rank` varchar(32) NOT NULL,
  `passive_boosts` text NOT NULL,
  `combat_boosts` text NOT NULL,
  `jutsu` text NOT NULL,
  PRIMARY KEY (`bloodline_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
        "
        );

        $this->execute(
            "
        CREATE TABLE IF NOT EXISTS `chat` (
    `post_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(50) NOT NULL,
  `message` varchar(550) NOT NULL,
  `title` varchar(50) DEFAULT NULL,
  `village` varchar(50) DEFAULT NULL,
  `time` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `staff_level` smallint(6) NOT NULL DEFAULT '0',
  `user_color` varchar(50) NOT NULL,
  `edited` int(11) NOT NULL,
  PRIMARY KEY (`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=`latin1`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `chat_edit_log` (
    `edit_id` int(11) NOT NULL AUTO_INCREMENT,
  `editor_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `old_message` text NOT NULL,
  `new_message` text NOT NULL,
  PRIMARY KEY (`edit_id`)
) ENGINE=MyISAM DEFAULT CHARSET=`latin1`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `clans` (
    `clan_id` int(11) NOT NULL AUTO_INCREMENT,
  `village` varchar(24) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `bloodline_only` tinyint(4) DEFAULT NULL,
  `boost` varchar(48) DEFAULT NULL,
  `boost_amount` float(12,2) DEFAULT NULL,
  `points` int(11) DEFAULT '0',
  `leader` int(11) DEFAULT '0',
  `elder_1` int(11) DEFAULT '0',
  `elder_2` int(11) DEFAULT '0',
  `challenge_1` varchar(64) NOT NULL DEFAULT '0',
  `logo` varchar(150) NOT NULL DEFAULT './images/default_avatar.png',
  `motto` varchar(200) NOT NULL,
  `info` text NOT NULL,
  PRIMARY KEY (`clan_id`)
) ENGINE=MyISAM DEFAULT CHARSET=`latin1`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `convos` (
    `convo_id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `active` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`convo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=`utf8mb4`;"
        );
        $this->execute(
            "
CREATE TABLE IF NOT EXISTS `convos_alerts` (
    `alert_id` int(11) NOT NULL AUTO_INCREMENT,
  `system_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `target_id` int(11) NOT NULL,
  `message` varchar(255) DEFAULT NULL,
  `time` int(11) NOT NULL,
  `unread` int(11) NOT NULL DEFAULT '1',
  `alert_deleted` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`alert_id`)
) ENGINE=InnoDB DEFAULT CHARSET=`utf8mb4`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `convos_messages` (
    `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `convo_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `time` int(11) NOT NULL,
  `deleted` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=`utf8mb4`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `convos_users` (
    `entry_id` int(11) NOT NULL AUTO_INCREMENT,
  `convo_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `last_read` int(11) NOT NULL DEFAULT '0',
  `muted` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`entry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=`utf8mb4`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `currency_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `character_id` int(11) NOT NULL,
  `currency_type` varchar(128) NOT NULL,
  `previous_balance` int(11) NOT NULL,
  `new_balance` int(11) NOT NULL,
  `transaction_amount` int(11) NOT NULL,
  `transaction_description` text,
  `transaction_time` int(11) NOT NULL DEFAULT '1577916956',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=`utf8`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `daily_tasks` (
    `user_id` int(11) NOT NULL,
  `tasks` text CHARACTER SET `latin1` NOT NULL,
  `last_reset` int(11) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=`utf8mb4`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `events_log` (
    `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `stage_id` int(11) NOT NULL,
  `stage_details` text NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=`latin1`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `items` (
    `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(150) DEFAULT NULL,
  `rank` tinyint(4) NOT NULL,
  `purchase_type` tinyint(4) NOT NULL,
  `purchase_cost` int(11) NOT NULL,
  `use_type` tinyint(4) NOT NULL,
  `effect` varchar(50) NOT NULL,
  `effect_amount` float NOT NULL,
  PRIMARY KEY (`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=`latin1`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `journals` (
    `user_id` int(11) NOT NULL,
  `journal` text NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=`latin1`;"
        );

        $this->execute(
            "CREATE TABLE IF NOT EXISTS `jutsu` (
    `jutsu_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `jutsu_type` varchar(24) NOT NULL,
  `rank` tinyint(4) NOT NULL,
  `power` float NOT NULL,
  `hand_seals` varchar(50) NOT NULL,
  `element` varchar(32) NOT NULL,
  `parent_jutsu` int(11) NOT NULL,
  `purchase_type` smallint(6) DEFAULT NULL,
  `purchase_cost` int(11) NOT NULL,
  `use_cost` smallint(6) NOT NULL,
  `use_type` varchar(32) NOT NULL,
  `cooldown` tinyint(4) NOT NULL DEFAULT '0',
  `battle_text` varchar(500) NOT NULL,
  `description` varchar(250) NOT NULL,
  `effect` varchar(50) DEFAULT NULL,
  `effect_amount` float DEFAULT NULL,
  `effect_length` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`jutsu_id`)
) ENGINE=MyISAM DEFAULT CHARSET=`latin1`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `logs` (
    `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `log_type` varchar(64) DEFAULT NULL,
  `log_title` varchar(100) DEFAULT NULL,
  `log_time` int(11) DEFAULT NULL,
  `log_contents` text,
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=`latin1`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `missions` (
    `mission_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `rank` tinyint(4) NOT NULL,
  `mission_type` tinyint(4) NOT NULL,
  `stages` text NOT NULL,
  `money` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`mission_id`)
) ENGINE=MyISAM DEFAULT CHARSET=`latin1`;"
        );
        $this->execute(
            "
CREATE TABLE IF NOT EXISTS `multi_accounts` (
    `multi_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `status` varchar(75) NOT NULL,
  PRIMARY KEY (`multi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=`utf8mb4`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `news_posts` (
    `post_id` int(11) NOT NULL AUTO_INCREMENT,
  `sender` varchar(50) NOT NULL,
  `title` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=`latin1`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `official_warnings` (
    `warning_id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `staff_name` varchar(75) NOT NULL,
  `user_id` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `data` text NOT NULL,
  `viewed` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`warning_id`)
) ENGINE=InnoDB DEFAULT CHARSET=`utf8mb4`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `payments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `txn_id` varchar(32) NOT NULL,
  `payment_date` varchar(32) NOT NULL,
  `time` int(11) NOT NULL,
  `username` varchar(32) NOT NULL,
  `buyer_name` varchar(50) NOT NULL,
  `buyer_email` varchar(64) NOT NULL,
  `payment_amount` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `payment_currency` varchar(16) NOT NULL,
  `address_city` varchar(50) NOT NULL,
  `address_country` varchar(50) NOT NULL,
  `address_state` varchar(20) NOT NULL,
  `address_street` varchar(50) NOT NULL,
  `address_zip` varchar(16) NOT NULL,
  `address_status` varchar(24) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=`latin1`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `player_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(128) DEFAULT NULL,
  `log_type` varchar(128) DEFAULT NULL,
  `log_time` datetime(2) DEFAULT NULL,
  `log_contents` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=`utf8`;"
        );
        $this->execute(
            "
CREATE TABLE IF NOT EXISTS `premium_credit_exchange` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `seller` varchar(50) NOT NULL,
  `premium_credits` int(11) NOT NULL,
  `money` int(11) NOT NULL,
  `completed` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=`latin1`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `private_messages` (
    `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `sender` varchar(50) NOT NULL,
  `recipient` varchar(50) NOT NULL,
  `subject` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `time` int(11) NOT NULL,
  `message_read` tinyint(4) NOT NULL DEFAULT '0',
  `staff_level` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`message_id`)
) ENGINE=MyISAM DEFAULT CHARSET=`latin1`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `ranks` (
    `rank_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `base_level` smallint(6) NOT NULL,
  `max_level` smallint(6) NOT NULL,
  `base_stats` int(11) NOT NULL,
  `stats_per_level` int(11) NOT NULL,
  `health_gain` int(11) NOT NULL,
  `pool_gain` smallint(6) NOT NULL,
  `stat_cap` int(11) NOT NULL,
  PRIMARY KEY (`rank_id`)
) ENGINE=MyISAM DEFAULT CHARSET=`latin1`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `reports` (
    `report_id` int(11) NOT NULL AUTO_INCREMENT,
  `report_type` tinyint(4) NOT NULL,
  `content_id` int(11) NOT NULL,
  `content` text,
  `user_id` int(11) DEFAULT NULL,
  `staff_level` tinyint(4) NOT NULL DEFAULT '0',
  `reporter_id` int(11) DEFAULT NULL,
  `moderator_id` int(11) DEFAULT NULL,
  `reason` varchar(100) DEFAULT NULL,
  `notes` text,
  `status` tinyint(4) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  PRIMARY KEY (`report_id`)
) ENGINE=MyISAM DEFAULT CHARSET=`latin1`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `special_missions` (
    `mission_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `difficulty` varchar(255) DEFAULT NULL,
  `start_time` int(11) NOT NULL,
  `end_time` int(11) NOT NULL DEFAULT '0',
  `progress` int(11) NOT NULL DEFAULT '0',
  `target` varchar(255) DEFAULT NULL,
  `log` text NOT NULL,
  `reward` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`mission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=`latin1`;"
        );
        $this->execute(
            "
CREATE TABLE IF NOT EXISTS `staff_logs` (
    `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `time` int(11) NOT NULL,
  `type` varchar(75) DEFAULT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=`latin1`;"
        );

        $this->execute(
            "CREATE TABLE IF NOT EXISTS `support_request` (
    `support_id` int(11) NOT NULL AUTO_INCREMENT,
  `support_type` varchar(75) DEFAULT NULL,
  `support_key` varchar(75) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(40) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `ip_address` varchar(64) DEFAULT NULL,
  `subject` varchar(70) DEFAULT NULL,
  `message` text,
  `time` int(11) DEFAULT NULL,
  `updated` int(11) DEFAULT NULL,
  `open` int(1) DEFAULT '1',
  `admin_response` int(1) DEFAULT '0',
  `premium` int(1) DEFAULT '0',
  PRIMARY KEY (`support_id`)
) ENGINE=InnoDB DEFAULT CHARSET=`utf8mb4`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `support_request_responses` (
    `response_id` int(11) NOT NULL AUTO_INCREMENT,
  `support_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(40) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `ip_address` varchar(64) DEFAULT NULL,
  `message` text,
  `time` int(11) DEFAULT NULL,
  PRIMARY KEY (`response_id`)
) ENGINE=InnoDB DEFAULT CHARSET=`utf8mb4`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `system_storage` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `global_message` text,
  `time` varchar(64) NOT NULL,
  `database_version` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=`utf8`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `teams` (
    `team_id` int(11) NOT NULL AUTO_INCREMENT,
  `village` varchar(24) DEFAULT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '1',
  `name` varchar(40) DEFAULT NULL,
  `boost` varchar(64) DEFAULT NULL,
  `boost_amount` int(11) NOT NULL DEFAULT '0',
  `points` int(11) DEFAULT NULL,
  `monthly_points` smallint(6) DEFAULT NULL,
  `leader` int(11) DEFAULT NULL,
  `members` varchar(200) NOT NULL,
  `mission_id` int(11) DEFAULT NULL,
  `mission_stage` text,
  `logo` varchar(200) NOT NULL,
  `boost_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`team_id`)
) ENGINE=MyISAM DEFAULT CHARSET=`latin1`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `username_log` (
    `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `old_name` varchar(40) NOT NULL,
  `new_name` varchar(40) NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=`latin1`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `users` (
    `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(40) NOT NULL,
  `password` varchar(256) NOT NULL,
  `email` varchar(100) NOT NULL,
  `staff_level` smallint(6) NOT NULL DEFAULT '0',
  `support_level` int(1) DEFAULT '0',
  `health` double(12,2) NOT NULL,
  `max_health` double(12,2) NOT NULL,
  `money` int(11) UNSIGNED NOT NULL,
  `premium_credits` int(11) NOT NULL DEFAULT '0',
  `premium_credits_purchased` int(11) NOT NULL DEFAULT '0',
  `forbidden_seal` varchar(64) NOT NULL,
  `chat_color` varchar(100) NOT NULL,
  `chat_effect` varchar(100) DEFAULT NULL,
  `current_ip` varchar(40) NOT NULL,
  `last_ip` varchar(40) NOT NULL,
  `failed_logins` smallint(6) NOT NULL DEFAULT '0',
  `global_message_viewed` tinyint(4) NOT NULL DEFAULT '1',
  `avatar_link` varchar(100) NOT NULL DEFAULT './images/default_avatar.png',
  `profile_song` varchar(100) NOT NULL,
  `gender` varchar(15) NOT NULL,
  `village` varchar(30) NOT NULL,
  `level` smallint(6) NOT NULL,
  `rank` smallint(6) NOT NULL,
  `stamina` float NOT NULL,
  `max_stamina` float NOT NULL,
  `chakra` float NOT NULL,
  `max_chakra` float NOT NULL,
  `elements` varchar(100) DEFAULT NULL,
  `regen_rate` float NOT NULL,
  `exp` int(11) NOT NULL,
  `bloodline_id` smallint(6) NOT NULL,
  `bloodline_name` varchar(40) NOT NULL,
  `clan_id` smallint(1) NOT NULL DEFAULT '0',
  `clan_office` int(11) NOT NULL DEFAULT '0',
  `team_id` varchar(32) NOT NULL DEFAULT '0',
  `battle_id` int(11) NOT NULL DEFAULT '0',
  `last_ai` int(11) NOT NULL,
  `last_free_stat_change` int(11) DEFAULT '0',
  `last_pvp` int(11) NOT NULL,
  `last_death` int(11) NOT NULL,
  `challenge` int(11) NOT NULL DEFAULT '0',
  `location` varchar(9) NOT NULL,
  `stealth` tinyint(4) NOT NULL DEFAULT '0',
  `train_type` varchar(64) DEFAULT NULL,
  `train_gain` float DEFAULT NULL,
  `train_time` int(11) DEFAULT NULL,
  `pvp_wins` int(11) NOT NULL,
  `pvp_losses` int(11) NOT NULL,
  `ai_wins` int(11) NOT NULL,
  `ai_losses` int(11) NOT NULL,
  `monthly_pvp` smallint(6) NOT NULL DEFAULT '0',
  `ninjutsu_skill` int(11) NOT NULL,
  `genjutsu_skill` int(11) NOT NULL,
  `taijutsu_skill` int(11) NOT NULL,
  `bloodline_skill` int(11) NOT NULL,
  `cast_speed` int(11) NOT NULL,
  `speed` int(11) NOT NULL,
  `intelligence` int(11) NOT NULL,
  `willpower` int(11) NOT NULL,
  `mission_id` int(11) NOT NULL DEFAULT '0',
  `mission_stage` text NOT NULL,
  `exam_stage` smallint(6) DEFAULT '0',
  `last_login` int(11) NOT NULL DEFAULT '0',
  `last_update` int(11) NOT NULL DEFAULT '0',
  `last_active` int(11) NOT NULL DEFAULT '0',
  `ban_data` text,
  `ban_type` varchar(24) NOT NULL,
  `ban_expire` int(11) DEFAULT NULL,
  `journal_ban` tinyint(4) NOT NULL DEFAULT '0',
  `avatar_ban` tinyint(4) NOT NULL DEFAULT '0',
  `song_ban` tinyint(4) NOT NULL DEFAULT '0',
  `layout` varchar(50) NOT NULL DEFAULT 'classic_blue',
  `log_actions` tinyint(4) NOT NULL DEFAULT '0',
  `register_date` int(11) NOT NULL,
  `verify_key` varchar(64) NOT NULL,
  `user_verified` tinyint(4) NOT NULL DEFAULT '0',
  `village_changes` tinyint(4) NOT NULL DEFAULT '0',
  `username_changes` tinyint(4) NOT NULL DEFAULT '1',
  `clan_changes` tinyint(4) NOT NULL DEFAULT '0',
  `special_mission` int(11) DEFAULT '0',
  `spouse` int(11) DEFAULT '0',
  `marriage_time` int(11) DEFAULT '0',
  `missions_completed` varchar(256) DEFAULT NULL,
  `presents_claimed` varchar(100) DEFAULT NULL,
  `censor_explicit_language` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=`latin1`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `user_bloodlines` (
    `user_id` int(11) NOT NULL,
  `bloodline_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `passive_boosts` text NOT NULL,
  `combat_boosts` text NOT NULL,
  `jutsu` text NOT NULL,
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=`latin1`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `user_inventory` (
    `user_id` int(11) NOT NULL,
  `jutsu` text NOT NULL,
  `items` text NOT NULL,
  `bloodline_jutsu` varchar(500) NOT NULL,
  `equipped_jutsu` varchar(500) DEFAULT NULL,
  `equipped_items` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=`latin1`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `user_record` (
    `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `staff_name` varchar(75) NOT NULL,
  `user_id` int(11) NOT NULL,
  `record_type` varchar(100) NOT NULL,
  `time` int(11) NOT NULL,
  `data` text NOT NULL,
  `deleted` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=`utf8mb4`;"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `villages` (
    `village_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL,
  `location` varchar(8) DEFAULT NULL,
  `points` int(11) DEFAULT '0',
  `leader` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`village_id`)
) ENGINE=MyISAM DEFAULT CHARSET=`latin1`;"
        );

        // data
        $this->execute("START TRANSACTION;");

        // AI Opponents
        $this->execute(
            "
           INSERT INTO `ai_opponents` (`ai_id`, `rank`, `money`, `name`, `max_health`, `level`, `ninjutsu_skill`, `genjutsu_skill`, `taijutsu_skill`, `cast_speed`, `speed`, `strength`, `intelligence`, `willpower`, `moves`) VALUES
(1, 1, 20, 'Annoying Crow', 0.60, 1, 0.80, 0.80, 0.80, 0.22, 0.22, 0.01, 0.10, 0.05, '[{\"battle_text\":\"[opponent] pecks at [player]&#039;s head with its beak.\",\"power\":\"1.2\",\"jutsu_type\":\"taijutsu\"}]'),
(2, 1, 40, 'Academy Bully', 0.60, 5, 0.80, 0.80, 0.80, 0.22, 0.22, 0.01, 0.10, 0.05, '[{\"battle_text\":\"[opponent] repeatedly punches [player] in the gut after throwing sand in [gender2] eyes.\",\"power\":\"1.4\",\"jutsu_type\":\"taijutsu\"},{\"battle_text\":\"[opponent] delivers a kick filled with chakra to [player]&#039;s side.\",\"power\":\"1.4\",\"jutsu_type\":\"taijutsu\"}]'),
(3, 1, 45, 'Prodigy Student', 0.60, 10, 0.80, 0.80, 0.80, 0.22, 0.22, 0.01, 0.10, 0.05, '[{\"battle_text\":\"[opponent] sweeps at [player]&#039;s legs, causing them to fall\",\"power\":\"1.9\",\"jutsu_type\":\"taijutsu\"},{\"battle_text\":\"[opponent] creates multiple clones that trick [player] before [gender] is struck from behind.\",\"power\":\"1.9\",\"jutsu_type\":\"taijutsu\"}]'),
(4, 2, 50, 'Academy Graduate', 0.65, 11, 0.85, 0.85, 0.85, 0.20, 0.20, 0.01, 0.10, 0.05, '[{\"battle_text\":\"[opponent] uses a replacement technique and strikes [player] from behind.\",\"power\":\"2\",\"jutsu_type\":\"taijutsu\"},{\"battle_text\":\"[opponent] throws a smoke bomb at [player] before throwing a multitude of chakra soaked shuriken.\",\"power\":\"2\",\"jutsu_type\":\"ninjutsu\"}]'),
(5, 2, 95, 'Advanced Genin', 0.65, 16, 0.85, 0.85, 0.85, 0.22, 0.22, 0.01, 0.10, 0.05, '[{\"battle_text\":\"[opponent] stomps their foot on the ground and launches a wave of earth spikes.\",\"power\":\"2.5\",\"jutsu_type\":\"ninjutsu\"},{\"battle_text\":\"[opponent] uses their bloodline to trap [player] in a gruesome genjutsu.\",\"power\":\"2.5\",\"jutsu_type\":\"genjutsu\"}]'),
(6, 2, 110, 'Weapon Fanatic', 0.65, 18, 0.85, 0.85, 0.85, 0.22, 0.22, 0.01, 0.10, 0.05, '[{\"battle_text\":\"[opponent] rushes forward and slices at [player] with two chakra infused Ninjato.\",\"power\":\"2.7\",\"jutsu_type\":\"taijutsu\"},{\"battle_text\":\"[opponent] sends [player] flying with a strike from their hammer.\",\"power\":\"2.7\",\"jutsu_type\":\"taijutsu\"}]'),
(13, 2, 75, 'Crafty Kunoichi', 0.65, 14, 0.85, 0.85, 0.85, 0.20, 0.20, 0.01, 0.10, 0.05, '[{\"battle_text\":\"[opponent] uses a series of wires to suspend [player]&#039;s body in the air. Electricity slowly slides down the metal wiring till it comes in contact with [player]&#039;s body, sending a surge of negative energy though their system.\",\"power\":\"2.3\",\"jutsu_type\":\"ninjutsu\"},{\"battle_text\":\"[opponent] delivers a powerful blow to the back of [player]&#039;s knee causing them to kneel down on one leg before Crafty Kunoichi does a backflip and smashes her foot under [player]&#039;s chin, sending them backwards.\",\"power\":\"2.3\",\"jutsu_type\":\"taijutsu\"}]'),
(7, 3, 150, 'Furious Tiger', 0.70, 26, 0.85, 0.85, 0.85, 0.20, 0.20, 0.01, 0.10, 0.05, '[{\"battle_text\":\"[opponent] sinks its teeth into [player]&#039;s side, the sound of bones breaking is painfully audible\",\"power\":\"3.3\",\"jutsu_type\":\"taijutsu\"},{\"battle_text\":\"[opponent] eyes glimmer as they move at incredible speed, leaving a blue streak in their haste. The blue streak passes though [player] causing them to hunch over in sever pain, bleeding where the streak had passed.\",\"power\":\"3.3\",\"jutsu_type\":\"genjutsu\"}]'),
(8, 3, 210, 'Genin Trio', 0.70, 42, 0.85, 0.85, 0.85, 0.22, 0.22, 0.01, 0.10, 0.05, '[{\"battle_text\":\"[opponent] attack as one, delivering a well timed kunai to [player]&#039;s limbs before closing in proximity, two slam their elbow into [player]&#039;s gut and back, the third comes up from under the ground with a singular punch to the chin.\",\"power\":\"3.7\",\"jutsu_type\":\"taijutsu\"},{\"battle_text\":\"[opponent] each cast a element based jutsu, one wind, another fire and the last lightning. the resulting jutsu combine and effectively cause great damage to the environment and [player].\",\"power\":\"3.8\",\"jutsu_type\":\"ninjutsu\"}]'),
(9, 3, 240, 'Novice Chuunin', 0.70, 50, 0.85, 0.85, 0.85, 0.22, 0.22, 0.01, 0.10, 0.05, '[{\"battle_text\":\"[opponent] fills calthrops with chakra and throws them across the ground in front of [player], Anbu Novice lets lose a massive amount of shuriken which come in contact with the electrical chakra in the calthrops, destroying [player]&#039;s nerves with ease.\",\"power\":\"4.2\",\"jutsu_type\":\"ninjutsu\"},{\"battle_text\":\"[opponent] disables [player]&#039;s mobility by slashing with their Ninjato at the crucial areas. When [player] retaliates a Substitution jutsu is used by Anbu Novice before a piece of cold steel is pressed against [player]&#039;s neck, now the victim of an assassination jutsu.\",\"power\":\"4.3\",\"jutsu_type\":\"taijutsu\"}]'),
(10, 2, 120, 'Talented Genin', 0.65, 20, 0.85, 0.85, 0.85, 0.22, 0.22, 0.01, 0.10, 0.05, '[{\"battle_text\":\"[opponent] pulling a wire that runs across the ground triggering a spring trap that fires dozens of kunai at [player].\",\"power\":\"3\",\"jutsu_type\":\"taijutsu\"},{\"battle_text\":\"[opponent] performs a series of hand signs before spitting out a dark violet colored acid that eats [player]&#039;s tissue at an alarming rate.\",\"power\":\"3\",\"jutsu_type\":\"ninjutsu\"}]'),
(11, 3, 170, 'Elite Contender', 0.70, 28, 0.82, 0.82, 0.82, 0.20, 0.20, 0.01, 0.10, 0.05, '[{\"battle_text\":\"[opponent] uses their extreme talent over taijutsu to deliver several precise blows, a spinning kick to the head and finally strike to [player]&#039;s left rib with a metal stave.\",\"power\":\"2.8\",\"jutsu_type\":\"taijutsu\"},{\"battle_text\":\"[opponent] bites their hand causing it to bleed and begins to perform handseals, using their bloodline to mix the blood with a firey jutsu they spew out, Which results in several spears of green flames to consume [player]\",\"power\":\"2.7\",\"jutsu_type\":\"ninjutsu\"}]'),
(12, 3, 190, 'Jounin&#039;s Shadow Clone', 0.70, 33, 0.85, 0.85, 0.85, 0.22, 0.22, 0.01, 0.10, 0.05, '[{\"battle_text\":\"[opponent] performs and insane amount of hand signs before firing out a barrage of fire bullets that couple with a giant snake made purely out of water, when the two come in contact right by [player], a searing haze of steam is created, boiling [player] alive.\",\"power\":\"3.5\",\"jutsu_type\":\"ninjutsu\"},{\"battle_text\":\"[opponent] pulls out two long jagged edged butcher knife style blades, using the momentum of the blades Jounin&#039;s Shadow Clone spins violently like a tornado. The resulting flurry tears [player] to shreds.\",\"power\":\"3.5\",\"jutsu_type\":\"taijutsu\"}]'),
(14, 4, 250, 'Chuunin Expert', 0.70, 51, 0.85, 0.85, 0.85, 0.20, 0.20, 0.01, 0.10, 0.05, '[{\"battle_text\":\"[opponent] strings together a combo of swift weapon attacks, ferociously attacking [player]\",\"power\":\"4.0\",\"jutsu_type\":\"taijutsu\"},{\"battle_text\":\"[opponent] unleashes a fireball barrage at [player].\",\"power\":\"4.0\",\"jutsu_type\":\"ninjutsu\"}]'),
(15, 4, 265, 'Village Outlaw', 0.70, 55, 0.85, 0.85, 0.85, 0.20, 0.20, 0.01, 0.10, 0.05, '[{\"battle_text\":\"[opponent] launches a barrage of shuriken at [player]       while player is distracted fending of shuriken Village Outlaw       sends a kunai flying [player] instinctively deflects kunai behind      him but fails to notice the explosive tag attached to the kunai. The tag explodes in a fiery blast.\",\"power\":\"4.1\",\"jutsu_type\":\"taijutsu\"},{\"battle_text\":\"[opponent] launches a barrage of fireballs that rain down from the sky at you.\",\"power\":\"4.1\",\"jutsu_type\":\"ninjutsu\"}]'),
(16, 4, 270, 'Rogue Samurai', 0.70, 60, 0.85, 0.85, 0.85, 0.22, 0.22, 0.01, 0.10, 0.05, '[{\"battle_text\":\"[opponent] rushes at [player] and feigns a slash with his sword while puling out a second katana with his other hand. Rogue Samurai swings the second katana in a vicious surprise strike at [player]&#039;s lowered defenses.\",\"power\":\"4.2\",\"jutsu_type\":\"taijutsu\"}]'),
(17, 4, 275, 'Enemy ANBU', 0.70, 65, 0.85, 0.85, 0.85, 0.22, 0.22, 0.01, 0.10, 0.05, '[{\"battle_text\":\"[opponent] traps [player] in a genjutsu that obscurs [gender2] vision. Suddenly out of nowhere lightning bolts come shooting through the air at [player] and pierce through [gender2].\",\"power\":\"4.6\",\"jutsu_type\":\"ninjutsu\"}]'),
(18, 4, 290, 'ANBU Captain', 0.70, 75, 0.80, 0.80, 0.80, 0.30, 0.30, 0.01, 0.10, 0.05, '[{\"battle_text\":\"[opponent] performs a series of hand signs in a blur, and rain begins to drench the battlefield, so heavily that it becomes difficult to see. As [player] looks at [opponent], suddenly [gender] feels a blade in their back and sees the shadow clone of [gender2] opponent fading away.\",\"power\":\"4.8\",\"jutsu_type\":\"ninjutsu\"},{\"battle_text\":\"[opponent] forms several hand signs faster than the eye can follow. As the final sign is weaved several puddles appear around the battlefield. [opponent] touches one with his foot and sends a blast of chakra into it, causing a jet of water to shoot out of another puddle and pierce [player] through the stomach.\",\"power\":\"4.8\",\"jutsu_type\":\"ninjutsu\"}]'),
(21, 4, 280, 'Muscle-bound Jonin', 0.75, 70, 1.00, 1.00, 1.00, 0.10, 0.10, 0.00, 0.25, 0.25, '[{\"battle_text\":\"[opponent] pumps chakra into his arms and slams the ground, causing a massive shockwave that blasts towards [player].\",\"power\":\"4.7\",\"jutsu_type\":\"taijutsu\"},{\"battle_text\":\"[opponent] uses their immense physical strength to execute a powerful taijutsu maneuver using their legs. They sweep their leg in a wide arc, striking opponents with a powerful force and sending them flying.\",\"power\":\"4.7\",\"jutsu_type\":\"taijutsu\"}]'),
(22, 4, 295, 'Fumetsu Defector', 0.75, 80, 1.00, 1.00, 1.00, 0.10, 0.10, 0.00, 0.10, 0.10, '[{\"battle_text\":\"The glowing lights of [opponent]&#039;s heavenly eyes shines brightly, revealing to [gender2] the very fabric of reality. With a casual strike, [opponent] unleashes their power, unbinding the bonds that hold the mortal realm together. Unfortunately for [player], the bonds being destroyed are their own, and their flesh at the impact site dissipates into nothingness as if it was never there.\",\"power\":\"4.8\",\"jutsu_type\":\"ninjutsu\"},{\"battle_text\":\"[opponent]&#039;s shimmering eyes show the complex makeup of [opponent]&#039;s body. Having learned the divine formulas from her sacred tablet, she applies subtle pressure to the fabric of [player]&#039;s being. With agonising speed, the flesh of [player]&#039;s chest transmutes into lead, forcing them to the ground as reality tries hard to force them back to their original state, tearing [player] apart from the inside.\",\"power\":\"4.8\",\"jutsu_type\":\"ninjutsu\"}]'),
(23, 4, 300, 'Kibou Defector', 0.75, 85, 0.75, 0.75, 0.75, 0.35, 0.35, 0.00, 0.10, 0.10, '[{\"battle_text\":\"[opponent] charges all of his strength into their fist as bolts of lightning crackle over their flesh. Vanishing from sight, [opponent] runs towards [player] at top speed, delivering a shocking uppercut to the chest which wracks [player]&#039;s body with lightning, leaving them in a crumpled heap.\",\"power\":\"4.8\",\"jutsu_type\":\"taijutsu\"},{\"battle_text\":\"Lightning wells up in [opponent]&#039;s body as they pull a single kunai from their pouch. Holding it up, [opponent] drops the kunai and vanishes in a flash of electricity, dashing in a circle around [player] and bludgeoning them with over a hundred blows. [opponent] then returns to where they started their run, catching the falling kunai before it reaches the ground.\",\"power\":\"4.8\",\"jutsu_type\":\"taijutsu\"}]');
"
        );

        // Bloodlines
        $this->execute(
            "INSERT INTO `bloodlines` (`bloodline_id`, `name`, `clan_id`, `village`, `rank`, `passive_boosts`, `combat_boosts`, `jutsu`) VALUES
    (1, 'Shadow Manipulator', 1, 'Leaf', '2', '[{\"power\":\"10\",\"effect\":\"stealth\"}]', '[{\"power\":\"10\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"10\",\"effect\":\"genjutsu_boost\"}]', '[{\"name\":\"Shadow Spear\",\"rank\":\"2\",\"power\":\"1.8\",\"hand_seals\":\"120\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"A frightening technique which solidifies the user&#039;s shadow, creating a deadly and unpredictable weapon at their disposal.\",\"battle_text\":\"[player] feigns an opening in battle, allowing [opponent] to close the distance. Once they are in range, [player] throws out a single hand seal before [gender2] shadow leaps up from below [opponent], viciously impaling their leg.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"30\",\"effect_length\":\"2\"},{\"name\":\"Devouring Darkness\",\"rank\":\"3\",\"power\":\"3.5\",\"hand_seals\":\"121\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"A shadow manipulator&#039;s greatest ability, this illusion snuffs out the sun and casts their foes into an inescapable oubliette.\",\"battle_text\":\"[player]&#039;s shadow expands at an impossible rate, engulfing the sky and darkening the sun until all around [opponent] is darkness. Suddenly, the ground below gives way, and [opponent] falls screaming into an endless pit of shadows from which there is no escape.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"55\",\"effect_length\":\"2\"}]'),
(2, 'Marionette Maniac', 2, 'Sand', '2', '[]', '[{\"power\":\"20\",\"effect\":\"taijutsu_boost\"},{\"power\":\"10\",\"effect\":\"ninjutsu_resist\"}]', '[{\"name\":\"Dancing Strings\",\"rank\":\"2\",\"power\":\"2.3\",\"hand_seals\":\"15\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"It might not seem fair ganging up on your opponent with two marionettes armed to the teeth with hidden weapons, but who said war was meant to be fair after all?\",\"battle_text\":\"[player] watches from a distance as [opponent] struggles to hold off the advancing marionettes. With a subte swish of [gender2] fingers, each marionette reveals hidden reserves of weapons that completely overwhelm [opponent] as [player] laughs.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"speed_nerf\",\"effect_amount\":\"15\",\"effect_length\":\"2\"},{\"name\":\"Infernal Castelet\",\"rank\":\"3\",\"power\":\"3.4\",\"hand_seals\":\"16\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"For you, the battlefield is a grand stage, with a legion of marionettes moving at your command. When they all converge on one foe, who could possibly stand against them?\",\"battle_text\":\"With wide sweeping movements like a deranged conductor, [player] calls in a hundred marionettes to swarm around [opponent]. For the briefest of times, it looks like [opponent] might have a chance, but that&#039;s all part of the show. The marionettes unleash an inferno from hidden weapons in their chests, their spiralling dance creating a pillar of flame that brings the performance to a close.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"10\",\"effect_length\":\"2\"}]'),
(3, 'Fiendish Whispers', 3, 'Mist', '2', '[{\"power\":\"5\",\"effect\":\"regen\"},{\"power\":\"10\",\"effect\":\"scout_range\"}]', '[{\"power\":\"15\",\"effect\":\"genjutsu_boost\"}]', '[{\"name\":\"Encomium of Darkness\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"12\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"A song you cannot play on any other flute, as the spirit trapped inside guides you to play a tune which corrupts even the purest of hearts.\",\"battle_text\":\"[player] closes their eyes and holds an odd looking flute to [gender2] lips. As they play, [player]&#039;s fingers move at unnatural angles, and the music washes over [opponent]. Unable to resist, [opponent] carves profane symbols into their flesh as their mind twists away into nothingness.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"55\",\"effect_length\":\"2\"},{\"name\":\"Requiem for the Light\",\"rank\":\"3\",\"power\":\"3.5\",\"hand_seals\":\"104\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"The penultimate tune the fiend of the flute can teach. Those who hear it find any and all hope lost, as it becomes clear evil will triumph with this bitter dirge.\",\"battle_text\":\"[player] plays a song so dark and sad that [opponent] falls to their knees. As the light dies in their eyes, and they realise there is no hope left for them, [opponent] catches a glimpse of a terrifying, hungry beast lurking behind [player], playing the same twisted flute that damned them to this misery in the first place.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"40\",\"effect_length\":\"3\"}]'),
(4, 'Excess Chakra', 12, 'Leaf', '4', '[{\"power\":\"10\",\"effect\":\"regen\"}]', '[{\"power\":\"10\",\"effect\":\"ninjutsu_boost\"}]', '[{\"name\":\"Arsenal of Excess\",\"rank\":\"2\",\"power\":\"2.3\",\"hand_seals\":\"155\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Your near limitless reserves of chakra gift you the ability to create nigh indestructible chakra constructs to give you the edge in battle.\",\"battle_text\":\"[player] leaks chakra from every pore in their skin, crafting a thick layer of ethereal blue armour from which a long chakra blade extends at the end of each arm. Armed with such unique weaponry, [player] easily overpowers [opponent] and each blow steals some power to fuel [player]&#039;s excessive reserves.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"absorb_chakra\",\"effect_amount\":\"10\",\"effect_length\":\"2\"}]'),
(5, 'Morphing Limbs', 13, 'Stone', '4', '[]', '[{\"power\":\"15\",\"effect\":\"taijutsu_boost\"},{\"power\":\"5\",\"effect\":\"taijutsu_resist\"}]', '[{\"name\":\"Infinite Modifications\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"111\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Opponents always learn how to adapt to even the best thought out strategy, but with a bloodline like yours you can always keep them on their toes!\",\"battle_text\":\"[player] moulds their arms into great hammers, crashing them over [opponent]&#039;s head. Before [opponent] can raise a defence, [player] has changed their hands into long knives that slash at blinding speeds. As [opponent] finds their feet, [player] shifts their arms again into lashing whips. [opponent] has no hope of keeping up.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"none\",\"effect_amount\":\"12\",\"effect_length\":\"2\"}]'),
(6, 'Desert Wanderer', 14, 'Sand', '4', '[]', '[{\"power\":\"15\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"5\",\"effect\":\"cast_speed_boost\"}]', '[{\"name\":\"Red Sands\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"112\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"You carry with you a source of the red desert sand from your homeland. Few know the odd black grain hidden within is gunpowder until it&#039;s too late.\",\"battle_text\":\"[player] throws [gender2] arms up as a massive red sandstorm surrounds [opponent]. As [opponent] tries to escape, they accidentally spark the gunpowder hidden in the crimson  sand, causing a devastating chain explosion.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"3\",\"effect\":\"none\"}]'),
(7, 'Electric Beast', 15, 'Cloud', '4', '[]', '[{\"power\":\"10\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"10\",\"effect\":\"heal\"}]', '[{\"name\":\"Bestial Storm\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"113\",\"element\":\"Lightning\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Your bloodline has gifted you with delicate whiskers highly tuned to electrical currents. Using them you can unleash ferocious storms.\",\"battle_text\":\"[player] breathes in deeply, [gender2] hair standing on end as electricity builds up in their body. [opponent] sees [player] charging up an attack, and tries to close the distance, but they are too slow. [player] unleashes a ferocious roar that sends waves of lightning through the air, shocking [opponent] with fear and thunder\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"none\",\"effect_amount\":\"8\",\"effect_length\":\"3\"}]'),
(8, 'Rejuvenating Waters', 3, 'Mist', '3', '[{\"power\":\"10\",\"effect\":\"regen\"}]', '[{\"power\":\"15\",\"effect\":\"heal\"}]', '[{\"name\":\"Tainted Spring\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"114\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Using your ancient connection to the fountain of youth, play upon your enemies insecurities and trap them in your deadly genjutsu.\",\"battle_text\":\"[player] cuts their hand and sprinkles their crystal clear blood on the ground. From these drops rise a golden fountain, the mythical Fountain of Youth. [opponent] cannot resist the chance of immortality and drinks deeply, not knowing the illusion has disguised a pool of acid and they are burning themselves away.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"40\",\"effect_length\":\"3\"}]'),
(9, 'Fiery Passion', 4, 'Leaf', '3', '[]', '[{\"power\":\"15\",\"effect\":\"taijutsu_boost\"},{\"power\":\"10\",\"effect\":\"speed_boost\"}]', '[{\"name\":\"Kiai!\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"17\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Nothing can stop you, nothing can match you, your power cannot be stopped, unleash it all at once!\",\"battle_text\":\"After taking several blows, [player] looks to be on their last legs. [opponent] moves in to finish them, but with a mighty shout [player] rises from the ground with a sudden kick to the chin, launching [opponent] into the air. [player] follows them and rains an endless barrage of blows before sending them crashing into the ground with a final KIAI!!!\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"}]'),
(10, 'Sensory Destroyer', 5, 'Cloud', '3', '[{\"power\":\"10\",\"effect\":\"scout_range\"}]', '[{\"power\":\"15\",\"effect\":\"genjutsu_boost\"}]', '[{\"name\":\"Internal Shock\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"18\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"A terrifying technique which tricks the opponent into believing they are undergoing total organ failure.\",\"battle_text\":\"[player] lands a delicate touch on [opponent]&#039;s spine, sending a wave of Yin chakra through their system. The connection made, [player]&#039;s bloodline seeps into [opponent]&#039;s mind, cutting off the sensation of all organs and senses. [opponent] believes they are dying, but with no lungs how can they scream?\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"55\",\"effect_length\":\"2\"}]'),
(11, 'Earthly Minerals', 6, 'Stone', '3', '[]', '[{\"power\":\"15\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"10\",\"effect\":\"cast_speed_boost\"}]', '[{\"name\":\"Gravel Pit\",\"rank\":\"2\",\"power\":\"2.3\",\"hand_seals\":\"19\",\"element\":\"Earth\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"With your innate ability to manipulate the earth, there&#039;s always a deadly trap awaiting your enemies.\",\"battle_text\":\"[player] slams their palms onto the ground, and a swirling sea of soil swallows [opponent] up to their waist before hardening and trapping them. [opponent] has nowhere to run now.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"speed_nerf\",\"effect_amount\":\"10\",\"effect_length\":\"2\"}]'),
(12, 'Beast Tamer', 15, 'Cloud', '2', '[{\"power\":\"10\",\"effect\":\"scout_range\"}]', '[{\"power\":\"15\",\"effect\":\"taijutsu_resist\"},{\"power\":\"15\",\"effect\":\"speed_boost\"}]', '[{\"name\":\"Pack Tactics\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"122\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Calling upon the ancient pact, a pack of hungry wolves emerges and joins you in a devastatingly coordinated assault.\",\"battle_text\":\"[player] lets out a shrill whistle human ears can barely perceive, and as if from nowhere a pack of wolves shadows [gender2] movements. [opponent] is quickly surrounded, and on a wordless signal the pack attacks, a flash of claws and fangs against which there is no defence.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"none\",\"effect_amount\":\"10\",\"effect_length\":\"3\"},{\"name\":\"Among Men, Among Wolves\",\"rank\":\"3\",\"power\":\"3.5\",\"hand_seals\":\"123\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"Astride the mightiest wolf in the pack, who could possibly stand against you?\",\"battle_text\":\"[player] howls at the sky and a hulking wolf runs onto the battlefield. Leaping upon its back, [player] shouts out a challenge to [opponent] before charging them down. Incoming attacks are deflected harmlessly as mount and rider move as one, unleashing a feral fury none can withstand.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"}]'),
(13, 'Ritual Winds', 14, 'Sand', '3', '[]', '[{\"power\":\"15\",\"effect\":\"genjutsu_boost\"},{\"power\":\"10\",\"effect\":\"ninjutsu_resist\"}]', '[{\"name\":\"Hypnotic Dance\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"4.3\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Using your family&#039;s ancient battle dance, you can trap any foe in an inescapable illusion.\",\"battle_text\":\"[Player] pulls out a pair of delicate fans from their belt. Opening them releases a glittering dust which fills the air, swirling in dazzling colours as [player] moves [gender2] fans and hips to an unheard beat. The will to fight drains from [opponent]&#039;s heart, just as their life drains from their body.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"55\",\"effect_length\":\"2\"}]'),
(14, 'Liquid of Power', 17, 'Mist', '3', '[]', '[{\"power\":\"20\",\"effect\":\"taijutsu_boost\"},{\"power\":\"5\",\"effect\":\"heal\"}]', '[{\"name\":\"Taste of True Power\",\"rank\":\"2\",\"power\":\"2.3\",\"hand_seals\":\"27\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"As a guardian of the sacred waters, you are entitled to carry a vial with you. One sip is all you need to win the battle.\",\"battle_text\":\"[player] pulls out the small vial of sacred water [gender2] carries with them. With but a small sip, [player]&#039;s muscles bulge and their senses heighten. Moving at insane speeds, [player] catches [opponent] by the neck and slams them head-first into the ground, leaving a crater at the impact site. The power wears off quickly, returning [player] to their normal stature.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"10\",\"effect_length\":\"2\"}]'),
(15, 'Detonating Masterpiece', 6, 'Stone', '2', '[{\"power\":\"10\",\"effect\":\"scout_range\"}]', '[{\"power\":\"20\",\"effect\":\"ninjutsu_boost\"}]', '[{\"name\":\"Singing Swallows\",\"rank\":\"2\",\"power\":\"2.3\",\"hand_seals\":\"30\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Crafting small birds with hollow segments so they whistle as they fly is a wonderful party trick, exploding them on an unsuspecting opponent makes it art!\",\"battle_text\":\"[player] throws a handful of clay sculptures into the air which burst into small birds. As the birds fly around, holes in their bodies create a keen whistling sound. With a single hand seal, the birds dive on [opponent], their whistle growing into an ear-splitting screech seconds before they detonate, engulfing [opponent] in shrapnel and flame.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"speed_nerf\",\"effect_amount\":\"10\",\"effect_length\":\"2\"},{\"name\":\"Scorched Landscape\",\"rank\":\"3\",\"power\":\"2.9\",\"hand_seals\":\"31\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"A devastating explosion which leaves the battlefield a scarred ruin for years to come.\",\"battle_text\":\"[player] taps into the natural clay under the earth&#039;s surface, flooding it with [gender2] volatile chakra. [opponent] believes they have won the battle when [player] retreats, only to have their pride turned to fear when the ground trembles. Pillars of flame erupt from the ground as a cataclysmic explosion rends the earth apart, obliterating everything in a massive radius.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"22\",\"effect_length\":\"2\"}]'),
(16, 'Abyssal Arsenal', 22, 'Mist', '1', '[{\"power\":\"20\",\"effect\":\"regen\"}]', '[{\"power\":\"30\",\"effect\":\"taijutsu_boost\"},{\"power\":\"10\",\"effect\":\"heal\"}]', '[{\"name\":\"Pariah Blade\",\"rank\":\"2\",\"power\":\"2.4\",\"hand_seals\":\"200\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"The hellish pact made by your ancestors allows you to draw all manner of demonic weaponry from your soul.\",\"battle_text\":\"[player] plunges [gender2] hand into their chest, gripping at something within in a shower of blood. [opponent] is horrified when a massive sword is drawn from the wound, which [player] wields with unnatural skill to devastate [opponent].\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"10\",\"effect_length\":\"1\"},{\"name\":\"Infernal Onslaught\",\"rank\":\"3\",\"power\":\"2.7\",\"hand_seals\":\"207\",\"element\":\"Fire\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"As you grow stronger, deeper pits of the arsenal open their doors to you, but your greatest power is calling on the denizens of the vault to destroy your foes with you.\",\"battle_text\":\"[player] plunges [gender2] hands into [gender2] sides, tearing through their flesh and gripping the handles of two massive, black bladed swords. As the blades are drawn, more hands claw through the wounds, themselves wielding identical blades. Not [player]&#039;s hands, but twisted demonic ones, and [opponent] has little hope of defence as a barrage of hellish strikes from every direction cut them down.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"20\",\"effect_length\":\"2\"},{\"name\":\"Hellspawned Advent\",\"rank\":\"4\",\"power\":\"4.4\",\"hand_seals\":\"208\",\"element\":\"Fire\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"200\",\"description\":\"Your body has grown increasingly inhuman as your power wears away your soul, strengthening your connection to the demonic arsenal to which you are bound.\",\"battle_text\":\"[player] thrusts a small, ornate dagger into [gender2] heart, twisting it like a macabre key opening a lock. [gender2] flesh peels away, revealing not blood and muscle but a suit of demonic armour covered in moving, leering faces. [player] reaches into the wound on their chest and draws out a massive great spear so dark it consumes the light around it. With a single thrust, [player] pierces through not just [opponent], but their very soul.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"}]'),
(18, 'Weeping Skies', 24, 'Cloud', '1', '[{\"power\":\"20\",\"effect\":\"regen\"}]', '[{\"power\":\"25\",\"effect\":\"taijutsu_boost\"},{\"power\":\"10\",\"effect\":\"speed_boost\"},{\"power\":\"10\",\"effect\":\"ninjutsu_resist\"}]', '[{\"name\":\"Supersonic Fist\",\"rank\":\"2\",\"power\":\"2.6\",\"hand_seals\":\"202\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"A deceptively simple skill which channels the power of a raging storm into a single, devastating strike.\",\"battle_text\":\"[player] charges all of [gender2] strength into their fist as bolts of lightning crackle over their flesh. Vanishing from sight, [player] runs towards [opponent] at top speed, delivering a shocking uppercut to the chest which wracks [opponent]&#039;s body with lightning, leaving them in a crumpled heap.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"},{\"name\":\"Thundering Flash\",\"rank\":\"3\",\"power\":\"3.5\",\"hand_seals\":\"205\",\"element\":\"Lightning\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"Move as fast as a descending thunderbolt, delivering countless strikes in an instant.\",\"battle_text\":\"Lightning wells up in [player]&#039;s body as they pull a single kunai from their pouch. Holding it up, [player] drops the kunai and vanishes in a flash of electricity, dashing in a circle around [opponent] and bludgeoning them with over a hundred blows. [player] then returns to where they started their run, catching the falling kunai before it reaches the ground.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"none\",\"effect_amount\":\"15\",\"effect_length\":\"2\"},{\"name\":\"Sky God&#039;s Descent\",\"rank\":\"4\",\"power\":\"4.4\",\"hand_seals\":\"206\",\"element\":\"Lightning\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"200\",\"description\":\"The ultimate expression of your family&#039;s martial arts. Defy the laws of physics and leave no enemy standing.\",\"battle_text\":\"[player] lands an electrically charged punch of unimaginable power to [opponent]&#039;s chin, sending them flying into the stratosphere. With a scream of power, [player] leaps up into the sky and spins through the gathering storm clouds, trails of electricity following their heel. The air grows silent as the devastating kick lands, a single crack of thunder peals, and [opponent] is cast down to the ground amidst a pillar of lightning.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"}]'),
(19, 'Gravitational Law', 25, 'Stone', '1', '[{\"power\":\"20\",\"effect\":\"regen\"}]', '[{\"power\":\"25\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"10\",\"effect\":\"cast_speed_boost\"},{\"power\":\"10\",\"effect\":\"taijutsu_resist\"}]', '[{\"name\":\"Crushing Intensity\",\"rank\":\"2\",\"power\":\"2.1\",\"hand_seals\":\"203\",\"element\":\"None\",\"cooldown\":\"4\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Even the subtlest adjustments to gravity can drastically alter the outcome of a battle.\",\"battle_text\":\"[player] raises a hand towards [opponent] and focuses on the gravity around them. With each step [opponent] takes, the force of gravity is intensified. Their bones and muscles struggle as [opponent] can barely move, leaving them at [player]&#039;s mercy\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"speed_nerf\",\"effect_amount\":\"15\",\"effect_length\":\"2\"},{\"name\":\"Cataclysmic Shift\",\"rank\":\"3\",\"power\":\"2.9\",\"hand_seals\":\"311\",\"element\":\"None\",\"cooldown\":\"2\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"Changing the flow of gravity is second nature now, alternating between high and low forces is easy for you, but a nightmare for your opponents.\",\"battle_text\":\"[player] removes gravity in a large chunk of earth, causing it to float in the air above [opponent]. With a subtle flick of [gender2] wrist, [player] inverts [opponent]&#039;s gravity, sending them flying up at high speeds into the floating island above. Gravity is then multiplied several times, causing the island to crash back down into the earth, crushing [opponent] underneath.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"10\",\"effect_length\":\"3\"},{\"name\":\"Extinction Edict\",\"rank\":\"4\",\"power\":\"3.5\",\"hand_seals\":\"306\",\"element\":\"None\",\"cooldown\":\"2\",\"purchase_cost\":\"10000\",\"use_cost\":\"200\",\"description\":\"Your boundless power allows you to reach into the heavens and draw upon vast celestial bodies to devastate any who oppose you.\",\"battle_text\":\"[player] raises a hand to the sky before bringing it down in a dramatic arc. [gender2] gravitic powers ensnare a colossal meteor, pulling it into a collision course with the battlefield. The clouds part before the meteor as animals flee from the impact site. [opponent] has little chance to react as the burning rock crashes into the earth, casting flame and devastation as far as the eye can see, forever scarring the land with this awesome power.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"20\",\"effect_length\":\"2\"}]'),
(20, 'Smouldering Sands', 26, 'Sand', '1', '[{\"power\":\"10\",\"effect\":\"stealth\"}]', '[{\"power\":\"30\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"10\",\"effect\":\"taijutsu_resist\"},{\"power\":\"10\",\"effect\":\"ninjutsu_resist\"}]', '[{\"name\":\"Unforgiving Deserts\",\"rank\":\"2\",\"power\":\"2.1\",\"hand_seals\":\"204\",\"element\":\"None\",\"cooldown\":\"4\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Bury your opponent under a wave of burning hot desert sand.\",\"battle_text\":\"The sand under [opponent]&#039;s feet gives way, trapping them in place as [player] throws [gender2] arms into the air. A wave of scalding hot sand rises above [opponent], who can do little to escape the pain crashing down around them.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"speed_nerf\",\"effect_amount\":\"10\",\"effect_length\":\"2\"},{\"name\":\"Devouring Dunes\",\"rank\":\"3\",\"power\":\"3.1\",\"hand_seals\":\"211\",\"element\":\"None\",\"cooldown\":\"4\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"Raise the temperature to extreme levels, desiccating your foe and leaving them vulnerable to your attacks.\",\"battle_text\":\"[player] drastically raises the air temperature, causing [opponent] to stagger through the sand as all moisture saps from their body. When [opponent] is at their weakest, [player] claps [gender2] hands together as two massive fists of sand rise from the dunes, slamming into [opponent] and crushing any hope of survival they might still have had.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"taijutsu_nerf\",\"effect_amount\":\"10\",\"effect_length\":\"2\"},{\"name\":\"Inferno Twister\",\"rank\":\"4\",\"power\":\"4.4\",\"hand_seals\":\"212\",\"element\":\"Wind\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"200\",\"description\":\"With absolute mastery over sand and heat, you are capable of some truly terrifying attacks few defences can withstand.\",\"battle_text\":\"[player] places [gender2] hands on the sand below them, manipulating it to rise up in a fearsome sandstorm, at the heart of which stands [opponent]. As a burning desert wind blows through the storm, the sand whips up into a massive cyclone. The heat fuses the sand into glass, and [opponent] is burnt, slashed and thrown into the sky with little hope of a safe landing when the storm finally clears.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"}]'),
(26, 'Malevolent Tattoo', 11, 'Mist', '4', '[{\"power\":\"5\",\"effect\":\"regen\"}]', '[{\"power\":\"15\",\"effect\":\"ninjutsu_boost\"}]', '[{\"name\":\"Power Unleashed\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"305\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"The dark tattoo on the body slowly consumes your chakra but, if you carefully overfeed it, you can unleash all your stolen power in a single, devastating blast.\",\"battle_text\":\"[player] holds a single hand seal as visible lines of chakra emanate from [gender2] body into a wicked tattoo on their chest. The markings begin to glow with an unearthly crimson light, before [player] screams out in pain as a destructive blast of malevolent chakra explodes from their chest, engulfing [opponent] in a sea of power.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"}]'),
(21, 'Cursed Waters', 11, 'Mist', '3', '[]', '[{\"power\":\"10\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"10\",\"effect\":\"cast_speed_boost\"},{\"power\":\"5\",\"effect\":\"ninjutsu_resist\"}]', '[{\"name\":\"Awaken The Deep\",\"rank\":\"2\",\"power\":\"2.1\",\"hand_seals\":\"300\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"The power that haunts you might well one day consume you, but as long as you keep it fed The Deep seems happy to ignore you. For now...\",\"battle_text\":\"[player] sees a small pool of water nearby, and takes a deep breath to keep [gender2] fear at bay. Knowing what can lurk within, [gender2] allows [opponent] to push them back, dragging the fight over towards the puddle. As their battling figures are reflected in the pool a long, barbed tentacle emerges, wrapping around [opponent]&#039;s leg and tearing flesh from bone as The Deep drags its next meal into its impossible realm.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"speed_nerf\",\"effect_amount\":\"15\",\"effect_length\":\"2\"}]'),
(22, 'Hidden Weapons', 13, 'Stone', '3', '[{\"power\":\"10\",\"effect\":\"scout_range\"}]', '[{\"power\":\"15\",\"effect\":\"taijutsu_boost\"}]', '[{\"name\":\"The Art of War\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"301\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"A strategist can win even the hardest of battles if they choose the ground on which they fight. A genius can best even them when they load that site up with traps.\",\"battle_text\":\"The moment [player] has been planning for has finally come. As [opponent] unexpectedly steps on an inconspicuous branch the battlefield springs to life as a vicious clasping trap pins [opponent]&#039;s leg in place. From concealed places across the battlefield a rain of kunai, shuriken, arrows and stones rain down, all focused on the single point [opponent] is trapped. [player] takes notes for how to improve their strategy for next time!\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"none\",\"effect_amount\":\"15\",\"effect_length\":\"2\"}]'),
(23, 'Destructive Rage', 20, 'Cloud', '3', '[]', '[{\"power\":\"25\",\"effect\":\"taijutsu_boost\"}]', '[{\"name\":\"Boundless Hatred\",\"rank\":\"2\",\"power\":\"2.4\",\"hand_seals\":\"302\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"It probably isn&#039;t your opponent&#039;s fault your life sucks, but beating them to a pulp does a little to make you feel better.\",\"battle_text\":\"Thinking back on how much [gender2] life has sucked, [player] flies off the handle into an uncontrollable rage. [opponent] barely has time to react as they are tackled to the ground and [player] starts swinging punch after punch into [opponent]&#039;s face, swearing with every blow.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"10\",\"effect_length\":\"1\"}]'),
(24, 'Tranquil Meadow', 1, 'Leaf', '3', '[{\"power\":\"10\",\"effect\":\"regen\"}]', '[{\"power\":\"15\",\"effect\":\"genjutsu_boost\"}]', '[{\"name\":\"Insidious Flora\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"303\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Conjure a field of beautiful flowers, and sap the very life from your foes as their deadly toxins take care of the battle for you.\",\"battle_text\":\"[player] spreads their arms wide, creating a beautiful meadow of picturesque flowers and sweet scents. [opponent] realises far too late this layer of beauty is a cruel illusion, and the real meadow is a deadly garden of poisonous plants and venomous vines. The choking pollen creeps into [opponent]&#039;s throat, choking them while [player] watches on with a serene smile.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"55\",\"effect_length\":\"2\"}]'),
(25, 'Serpent&#039;s Fang', 18, 'Sand', '3', '[{\"power\":\"5\",\"effect\":\"regen\"}]', '[{\"power\":\"15\",\"effect\":\"taijutsu_boost\"},{\"power\":\"5\",\"effect\":\"ninjutsu_resist\"}]', '[{\"name\":\"Chain Devastation\",\"rank\":\"2\",\"power\":\"2.3\",\"hand_seals\":\"304\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Drawing upon the deadly tools of your bloodline, confound and constrict your opponents within a sea of bladed chains.\",\"battle_text\":\"[player] raises [gender2] hand towards [opponent] and calls upon the power of their bloodline. Several large, bladed chains burst from the ground, striking like coiling serpents and constricting [opponent] so tight they can hardly breathe. With a single gesture, the chains return to the earth, smashing [opponent] into the ground and leaving deep cuts across their torso.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"10\",\"effect_length\":\"2\"}]'),
(27, 'Elemental Neutralizer', 6, 'Stone', '4', '[]', '[{\"power\":\"15\",\"effect\":\"ninjutsu_resist\"},{\"power\":\"5\",\"effect\":\"heal\"}]', '[{\"name\":\"Crescent Seal\",\"rank\":\"2\",\"power\":\"2.0\",\"hand_seals\":\"307\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"An ancient seal which is designed to tear the ninjutsu from a target, rendering an enemy shinobi helpless.\",\"battle_text\":\"Landing a gentle blow with their finger and thumb upon [opponent], [player] doesn&#039;t seem to have caused any damage. The black crescent seal is easily overlooked until [opponent] attempts to use a jutsu. Their chakra flies into the seal and wracks their body with pain, leaving [player] with a distinct advantage for the battle to come.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"ninjutsu_nerf\",\"effect_amount\":\"20\",\"effect_length\":\"2\"}]'),
(28, 'Heightened Reflexes', 4, 'Leaf', '4', '[]', '[{\"power\":\"20\",\"effect\":\"taijutsu_boost\"},{\"power\":\"5\",\"effect\":\"ninjutsu_resist\"}]', '[{\"name\":\"Impossible Strikes\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"308\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Your slightly quicker reaction speed lets you avoid attacks and swing past your opponents defences in ways they wouldn&#039;t believe possible.\",\"battle_text\":\"[player]&#039;s body appears to move like fluid, sliding and twisting out of the way of every attack [opponent] tries to land. Every millisecond wide opening in [opponent]&#039;s form is capitalised upon, [player] responding to every missed movement with a devastating counter blow that onlookers can&#039;t believe a human would be capable of.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"none\",\"effect_amount\":\"10\",\"effect_length\":\"2\"}]'),
(29, 'Predatory Mirages', 5, 'Cloud', '4', '[]', '[{\"power\":\"15\",\"effect\":\"genjutsu_boost\"},{\"power\":\"5\",\"effect\":\"ninjutsu_resist\"}]', '[{\"name\":\"Empathetic Consumption\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"309\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Taking advantage of the vicious acid your body can produce, you lull an opponent into an illusion to distract them as they are consumed.\",\"battle_text\":\"[player] steps out of the way of [opponent]&#039;s attack, trapping them in a simple illusion. [opponent] believes their attack was successful, but [player]&#039;s body warps to consume [opponent]&#039;s outstretched limb. The genjutsu slows their perception of time down, enough for [player] to release [gender2] digestive acids and slowly absorb [opponent].\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"55\",\"effect_length\":\"2\"}]'),
(30, 'Illusionary Madness', 10, 'Sand', '4', '[]', '[{\"power\":\"15\",\"effect\":\"genjutsu_boost\"},{\"power\":\"5\",\"effect\":\"genjutsu_resist\"}]', '[{\"name\":\"The Gates of Madness\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"310\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Cast your opponent into your clan&#039;s sacred genjutsu, a desert hellscape from which few escape.\",\"battle_text\":\"[player] traps [opponent] in a special genjutsu known only to [gender2] clan. This wide, desert landscape is littered with bodies and bones, remnants of those who never found their escape. [opponent] feels the heat, thirst and pain of a desert as very real sensations, and with little other choice they wander into the sands, searching for respite [player] knows they will never find. [gender] will add another trophy to this hellscape.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"taijutsu_nerf\",\"effect_amount\":\"50\",\"effect_length\":\"2\"}]'),
(47, 'Eyes of God', 33, 'Cloud', '1', '[{\"power\":\"10\",\"effect\":\"scout_range\"}]', '[{\"power\":\"30\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"10\",\"effect\":\"genjutsu_resist\"},{\"power\":\"10\",\"effect\":\"taijutsu_resist\"}]', '[{\"name\":\"Divine Unmaking\",\"rank\":\"2\",\"power\":\"2.1\",\"hand_seals\":\"529\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Awaken the power of your divine eyes to unmake the world of mortals.\",\"battle_text\":\"The glowing lights of [player]&#039;s heavenly eyes shines brightly, revealing to [gender2] the very fabric of reality. With a casual strike, [player] unleashes their power, unbinding the bonds that hold the mortal realm together. Unfortunately for [opponent], the bonds being destroyed are their own, and their flesh at the impact site dissipates into nothingness as if it was never there.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"10\",\"effect_length\":\"2\"},{\"name\":\"Heavenly Transmutation\",\"rank\":\"3\",\"power\":\"3.5\",\"hand_seals\":\"530\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"As your eyes can see the fabric of reality, by learning the divine formulas that underpin all creation, one matter can be turned into another with ease, even something as complex as human flesh.\",\"battle_text\":\"[player]&#039;s shimmering eyes show the complex makeup of [opponent]&#039;s body. Having learned the divine formulas from [gender2] sacred tablet, [gender] applies subtle pressure to the fabric of [opponent]&#039;s being. With agonising speed, the flesh of [opponent]&#039;s chest transmutes into lead, forcing them to the ground as reality tries hard to force them back to their original state, tearing [opponent] apart from the inside.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"},{\"name\":\"The Right of Creation\",\"rank\":\"4\",\"power\":\"4.4\",\"hand_seals\":\"531\",\"element\":\"None\",\"cooldown\":\"4\",\"purchase_cost\":\"10000\",\"use_cost\":\"200\",\"description\":\"Your eyes have shown you how to break and change the fabric of reality, but in this ultimate expression of your power, you have the most sacred of all powers. Creation.\",\"battle_text\":\"[opponent] feels a sudden pressure in their throat as [player] stares at them with [gender2] majestic eyes. Within moments they cough out a large peach like fruit that [player] consumes. Divine power floods [gender2] body as a halo of golden chakra surrounds their head. They place a single godly finger on [opponent]&#039;s head, causing an agonising spasm of mutation as new limbs and eyes sprout out painfully from their pathetic, mortal form.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"}]'),
(32, 'Shattering Glass', 14, 'Sand', '3', '[]', '[{\"power\":\"20\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"5\",\"effect\":\"genjutsu_resist\"}]', '[{\"name\":\"Glittering Storm\",\"rank\":\"2\",\"power\":\"1.7\",\"hand_seals\":\"500\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Utilising your clan&#039;s unique chakra, create a massive glass dome over the battlefield, then shatter it to create a devastating rain of razor sharp debris.\",\"battle_text\":\"[player] throws handfuls of sand into the air, infusing it with their chakra. After a few frenzied seconds, the sand has changed into a giant glass dome which covers the battlefield. The sun reflects brilliant colours through the dome, dazzling [opponent], before they realise with horror that [player] has thrown a small rock at the dome&#039;s walls. The glass shatters as one, raining shards of glass upon everything in range.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"20\",\"effect_length\":\"3\"}]'),
(33, 'Descendants of Blood', 3, 'Mist', '3', '[{\"power\":\"10\",\"effect\":\"scout_range\"}]', '[{\"power\":\"10\",\"effect\":\"genjutsu_boost\"}]', '[{\"name\":\"Scent of Decay\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"501\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Having placed your blood on a select few corpses for this battle, your opponents will learn to fear that which they cannot understand.\",\"battle_text\":\"Placing [gender2] hands on the ground, [player] brings forth a body [gender] &#039;prepared&#039; earlier. [opponent] is quick to recognise the face as that of someone close to them, and cries out in horror as they are forced to battle someone they once held dear. [player] laughs as [gender2] technique reaps its unholy toll.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"100\",\"effect_length\":\"1\"}]'),
(34, 'Energy Manipulation', 12, 'Leaf', '3', '[{\"power\":\"10\",\"effect\":\"regen\"}]', '[{\"power\":\"15\",\"effect\":\"ninjutsu_boost\"}]', '[{\"name\":\"Energy Prison\",\"rank\":\"2\",\"power\":\"2.0\",\"hand_seals\":\"502\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Using your clan&#039;s knowledge of the godly power of &#039;energy&#039;, you can create unbreakable barriers around foes, within which you can torture them until your heart is content\",\"battle_text\":\"[player] kicks [opponent] into the air, then moves their hands in strange shapes until an almost invisible sphere of energy appears around [opponent]. Try as they might, [opponent] cannot break free, and each time they strike the barrier a long spike of energy retaliates with the same force as their blow, turning their every attack back on themselves.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"taijutsu_nerf\",\"effect_amount\":\"15\",\"effect_length\":\"2\"}]'),
(35, 'Molded Energy', 20, 'Cloud', '3', '[]', '[{\"power\":\"20\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"5\",\"effect\":\"cast_speed_boost\"}]', '[{\"name\":\"Judgement\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"504\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Use the power of the gods, &#039;energy&#039;, to deliver a devastating rain of holy light down on the unworthy.\",\"battle_text\":\"[player] leaps into the air as small discs of energy appear under [gender2] feet, allowing them to remain in place. Around [gender2], shining blades of bright light appear in their hundreds. [player] gestures towards [opponent] with a regal bearing, launching a deluge of light beams that pass through all defences and scorch the flesh on impact.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"}]'),
(36, 'Cavalcade of Souls', 19, 'Stone', '3', '[{\"power\":\"5\",\"effect\":\"regen\"}]', '[{\"power\":\"15\",\"effect\":\"genjutsu_boost\"},{\"power\":\"5\",\"effect\":\"ninjutsu_resist\"}]', '[{\"name\":\"Nightmare Possession\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"503\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Open the gates to the netherworld and lead a desperate soul to take over a new body, in exchange for its obedience.\",\"battle_text\":\"[player] hums a tune as the distant sound of a massive gate swinging open rings out. [opponent] starts to feel lost, and alone, when suddenly a small light dangles before them. It is comforting, holding it close will keep [opponent] safe. [player]&#039;s trap is sprung and a fiendish spirit takes charge of [opponent]&#039;s body, carving heretical symbols from dead languages into their flesh at the bidding of its new master, [player].\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"40\",\"effect_length\":\"3\"}]'),
(37, 'Superconductive Metals', 15, 'Cloud', '2', '[]', '[{\"power\":\"20\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"10\",\"effect\":\"ninjutsu_resist\"}]', '[{\"name\":\"Connected Circuit\",\"rank\":\"2\",\"power\":\"2.1\",\"hand_seals\":\"505\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"There&#039;s enough metal in the ground you fight on that, with a clever little spark, you can turn any unsuspecting patch of dirt into an electrifying land mine\",\"battle_text\":\"[player] throws a handful of electrically charged kunai into the air, which land straight down, creating an obvious trap [opponent] would never fall for. Cautiously moving around the charged blades, [opponent] is caught completely unaware when a distant patch of dirt underfoot explodes with electricity, stunning [opponent].\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"taijutsu_nerf\",\"effect_amount\":\"15\",\"effect_length\":\"2\"},{\"name\":\"Mjolnir\",\"rank\":\"3\",\"power\":\"3.0\",\"hand_seals\":\"506\",\"element\":\"Lightning\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"Your clan once used their incredible power to launch giant rods of conductive metal into the upper atmosphere. It&#039;s time to bring the thunder.\",\"battle_text\":\"Drawing upon the power of [gender2] ancestors, [player] calls down five massive rods of metal from the sky, which land in a pentagram around [opponent]. The devastation of these objects crashing into the earth throws debris into the air, which creates an interconnected net of metals [player] channels their lightning chakra through. [opponent] has nowhere to run, and is overcome by a barrage of explosive energy few could hope to withstand.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"20\",\"effect_length\":\"2\"}]');
"
        );
        $this->execute(
            "INSERT INTO `bloodlines` (`bloodline_id`, `name`, `clan_id`, `village`, `rank`, `passive_boosts`, `combat_boosts`, `jutsu`) VALUES
    (38, 'Autumn&#039;s Rebirth', 12, 'Leaf', '2', '[{\"power\":\"10\",\"effect\":\"regen\"}]', '[{\"power\":\"10\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"10\",\"effect\":\"genjutsu_resist\"}]', '[{\"name\":\"Fire of Renewal\",\"rank\":\"2\",\"power\":\"2.2\",\"hand_seals\":\"507\",\"element\":\"Fire\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Through the destruction wrought by flame, hope can grow anew.\",\"battle_text\":\"As a curtain of autumn leaves drift gently down, [opponent] finds their heart finding cause for rest. This brief window of calm is dashed almost instantly as the leaves begin to whirl about, and [player] appears in their midst as they suddenly combust. Flames soar through the air, burning [opponent] horribly, but as they clear a single bud cracks through the blackened soil to start life once again.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"10\",\"effect_length\":\"2\"},{\"name\":\"Autumnal Revival\",\"rank\":\"3\",\"power\":\"3.5\",\"hand_seals\":\"508\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"While your ancestors tended to vast forests to keep life in balance, you have learned that all your power can be used to create a single tree, greater than any the land has ever known.\",\"battle_text\":\"[opponent] limps through the sea of flame [player] has cast around themselves, unknowingly trampling a lone bud that was just starting to bloom. In a rage, [player] summons all [gender2] might to grow the bud into a colossal oak tree that shatters the clouds above. Deep within its core, slowly being drained as fuel for the tree&#039;s growth, [opponent] has no idea their sacrifice could create something so beautiful.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"}]'),
(39, 'Evoked Inugami', 13, 'Stone', '2', '[{\"power\":\"5\",\"effect\":\"regen\"}]', '[{\"power\":\"20\",\"effect\":\"genjutsu_boost\"},{\"power\":\"5\",\"effect\":\"taijutsu_resist\"}]', '[{\"name\":\"Invoking the Beasts\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"516\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"There is a darkness inside you in the eyes of others, so why not let it out to play?\",\"battle_text\":\"[opponent] hears the distant sound of howling as [player] hunches over, tendrils of inky darkness escaping from their spine and coiling into the forms of large black canines. These massive dogs waste no time in encircling [opponent] and biting each at an exposed limb until [player] moves in to finish the job.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"55\",\"effect_length\":\"2\"},{\"name\":\"Ritual of the Inugami\",\"rank\":\"3\",\"power\":\"3.5\",\"hand_seals\":\"517\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"Uses complex illusions to trick the opponent into believing they have summoned a truly colossal Inugami from the pits of hell itself.\",\"battle_text\":\"[player] manipulates [opponent]&#039;s perception, forcing them to see a massive black dog with blood red eyes and a boundless hunger looming over them. The Inugami picks [opponent] up in its monstrous jaws, and try as they might to resist it isn&#039;t long before [opponent] is consumed. The insides of the Inugami are all hellfire and brimstone, but the screams of [opponent] aren&#039;t strong enough to be heard outside their new prison.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"ninjutsu_nerf\",\"effect_amount\":\"55\",\"effect_length\":\"2\"}]'),
(40, 'Monstrous Transformation', 11, 'Mist', '2', '[{\"power\":\"10\",\"effect\":\"scout_range\"}]', '[{\"power\":\"20\",\"effect\":\"ninjutsu_boost\"}]', '[{\"name\":\"Aqua Torpedo\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"512\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Transform your body into that of a vicious undersea predator, then hunt down your unfortunate foes with unerring accuracy.\",\"battle_text\":\"[player] threads hand seals together as [gender2] body shifts and distorts into the shape of a massive shark. The battlefield floods and [player] vanishes below the growing waters. When [opponent] least expects it, [player] launches from the murky depths in a flash of foam and teeth, grabbing [opponent] by the waist and dragging them into the water below.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"none\",\"effect_amount\":\"15\",\"effect_length\":\"2\"},{\"name\":\"Blood in the Waters\",\"rank\":\"3\",\"power\":\"2.9\",\"hand_seals\":\"513\",\"element\":\"Water\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"Demonstrate the mastery of your bloodline over the sea by conjuring an immense body of water, then flooding it with hungry sharks.\",\"battle_text\":\"[player] spits out an impossible volume of water, turning the battlefield into a wide sea. Sharp fins cut through the water around [opponent]. Suddenly, [player] leaps out from under the water with a small kunai causing a scratch on [opponent]&#039;s face. A single drop of blood falls into the water below and the hundreds of circling sharks move as one, surging in a vicious feeding frenzy to rip [opponent] apart and leave nothing behind.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"ninjutsu_nerf\",\"effect_amount\":\"15\",\"effect_length\":\"2\"}]'),
(41, 'Toxic Atmosphere', 14, 'Sand', '2', '[]', '[{\"power\":\"25\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"5\",\"effect\":\"heal\"}]', '[{\"name\":\"Entropy&#039;s Hand\",\"rank\":\"2\",\"power\":\"2.1\",\"hand_seals\":\"514\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Utilising the small holes throughout your arms you can turn a missed punch into an agonising death with ease.\",\"battle_text\":\"[player] throws a punch at [opponent] that is easily blocked, just as [gender2] predicted. From the countless tiny holes dotting [gender2] arm, a cloud of microbial assassins leaks. Invisible to [opponent]&#039;s eyes, they have no way of knowing what will come when the miniscule monsters start eating the flesh from [opponent]&#039;s own arms to agonising effect.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"drain_chakra\",\"effect_amount\":\"15\",\"effect_length\":\"2\"},{\"name\":\"Parasitic Override\",\"rank\":\"3\",\"power\":\"2.9\",\"hand_seals\":\"515\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"You have developed a second strain of bacterial battlers inside your body, who no longer eat away the flesh, but sneak in and consume the neural pathways of the unfortunate victim.\",\"battle_text\":\"[player]&#039;s mouth fills with terrifying bacteria. As [player] spits on [opponent], the bacteria [gender] created set to work. [opponent] doesn&#039;t realise at first but their hands no longer respond to their will. [player]&#039;s creation is hard at work destroying [opponent]&#039;s mind from the inside out. When their legs finally give out, [opponent] falls to the ground, with no external sign of injury to show what horror has taken place.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"taijutsu_nerf\",\"effect_amount\":\"15\",\"effect_length\":\"2\"}]'),
(42, 'Sombre Requiem', 28, 'Mist', '1', '[{\"power\":\"20\",\"effect\":\"regen\"}]', '[{\"power\":\"30\",\"effect\":\"genjutsu_boost\"},{\"power\":\"5\",\"effect\":\"heal\"},{\"power\":\"10\",\"effect\":\"ninjutsu_resist\"}]', '[{\"name\":\"Haunting Siren\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"509\",\"element\":\"None\",\"cooldown\":\"6\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Your song has an impossible pull on the living, stealing from their their very will to live.\",\"battle_text\":\"[player] pauses briefly, breathing in deep before humming a soft, sombre tune. [opponent] would not normally let something like this distract them, but as the tune breaks into song, they realise there&#039;s no point fighting back. [opponent]&#039;s eyes gloss over as a deep melancholy falls over them, slowing their movements to a saddening crawl.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"taijutsu_nerf\",\"effect_amount\":\"35\",\"effect_length\":\"3\"},{\"name\":\"Dirge of Remembrance\",\"rank\":\"3\",\"power\":\"3.5\",\"hand_seals\":\"510\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"Your song plays on the threads of life when sung right, impossible tones human vocal chords can&#039;t recreate. But when you do, you thin the veil between life and death.\",\"battle_text\":\"[player] starts a song with sweeping movements of [gender2] hands. [opponent] follows the graceful movements, and hears the morose tune that brings a great sadness over them. From the corner of their vision, tiny faceless figures emerge from nearby shadows. [opponent] could fight back, but in their heart they know there&#039;s no point.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"100\",\"effect_length\":\"1\"},{\"name\":\"Nightmare Crescendo\",\"rank\":\"4\",\"power\":\"4.4\",\"hand_seals\":\"511\",\"element\":\"None\",\"cooldown\":\"2\",\"purchase_cost\":\"10000\",\"use_cost\":\"200\",\"description\":\"You understand your songs now, they don&#039;t control minds or thin the veil. Your song connects with souls directly, a very dangerous tool.\",\"battle_text\":\"[opponent] tries desperately to strike [player], but as long as [gender2] sings no strike seems to land. They feel queasy as a faint blue glow leaks from their body into the air around [player]. As the song finishes, [player] inhales deeply and consumes the blue light, and with it a piece of [opponent]&#039;s very soul.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"38\",\"effect_length\":\"3\"}]'),
(43, 'Nature&#039;s Calamity', 29, 'Leaf', '1', '[{\"power\":\"20\",\"effect\":\"regen\"}]', '[{\"power\":\"30\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"5\",\"effect\":\"heal\"},{\"power\":\"10\",\"effect\":\"taijutsu_resist\"}]', '[{\"name\":\"Geyser Stream\",\"rank\":\"2\",\"power\":\"2.1\",\"hand_seals\":\"517\",\"element\":\"Water\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Using your mystical connection with the earth, call upon resources hidden under your opponent to utterly devastate them.\",\"battle_text\":\"[player] places a single hand on the ground as a faint blue hued chakra seeps from [gender2] skin into the soil. Seconds later, the ground beneath [opponent] bucklets and splits as a stream of pressurised water escapes from the earth, launching them high into the sky. As they fall, [opponent] realises the crater below them is slowly filling with a boiling water that scalds them as they plunge deep into it.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"15\",\"effect_length\":\"2\"},{\"name\":\"Pillars of Heaven\",\"rank\":\"3\",\"power\":\"3.5\",\"hand_seals\":\"518\",\"element\":\"Lightning\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"Combine the awesome powers of land and sky to isolate an opponent and leave them defenceless against your strength.\",\"battle_text\":\"[opponent] dashes forward but [player] had already seen this coming. The earth below [opponent] suddenly shoots up, lifting them up amongst the clouds. Soaring, [opponent] struggles as a sudden brutal wind picks up. Small rocks from the pillar are cast about like dozens of tiny shurikens. [opponent] is slashed all over until the wind breaks and a single bolt of lightning crashes through [opponent], casting them to the ground once more.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"},{\"name\":\"Gaia&#039;s Embrace\",\"rank\":\"4\",\"power\":\"4.4\",\"hand_seals\":\"519\",\"element\":\"Fire\",\"cooldown\":\"2\",\"purchase_cost\":\"10000\",\"use_cost\":\"200\",\"description\":\"True to your bloodline&#039;s name, your greatest power is mastery over the great calamities that plague the inhabitants of the world.\",\"battle_text\":\"[player] throws [gender2] hands into the air, and thick clouds gather overhead as a barrage of snow falls. Gale force winds pick up, casting the snow about in a blizzard [opponent] can barely see through. As [opponent] freezes, they search out a source of warmth in the distance. Crawling blind, [opponent]&#039;s frostbitten nerves barely alert them as the earth splits open, swallowing [opponent] and casting them into the fiery magma below.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"}]'),
(44, 'Impregnable Defenses', 30, 'Stone', '1', '[{\"power\":\"20\",\"effect\":\"regen\"}]', '[{\"power\":\"20\",\"effect\":\"taijutsu_boost\"},{\"power\":\"20\",\"effect\":\"ninjutsu_resist\"},{\"power\":\"20\",\"effect\":\"taijutsu_resist\"}]', '[{\"name\":\"Metallic Fist\",\"rank\":\"2\",\"power\":\"2.0\",\"hand_seals\":\"520\",\"element\":\"None\",\"cooldown\":\"2\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Your bloodline enables your to create a unique metal nigh impenetrable by mundane means. It also weighs an insane amount, making your punches really count.\",\"battle_text\":\"[player] coats [gender2] arm in a thick, metallic layer of a mineral [opponent] has never seen before. The nigh impenetrable makeup of this unique metal proves beyond [opponent]&#039;s ability to overcome. Waiting for the perfect moment to strike, [player] unleashes a punch which smashes all defences aside and crushes [opponent] with unbelievable force.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"10\",\"effect_length\":\"3\"},{\"name\":\"Devastating Impact\",\"rank\":\"3\",\"power\":\"3.5\",\"hand_seals\":\"521\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"As your powers grow, learn to not just cover individual limbs, but your entire body in unbreakable metal. A powerful offence and defence in the right hands.\",\"battle_text\":\"Leaping high into the air, [player] coats their entire body in a lustrous metallic layer as their shadow passes over [opponent]. Curling into a ball, [player] begins their descent at an alarming speed, picking up speed and wreathing themselves in a corona of flame. Moving so fast [opponent] has little chance to react [player] crashes into the earth, leaving only a crumpled body at the base of a massive crater in [gender2] wake.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"},{\"name\":\"Gift of Midas\",\"rank\":\"4\",\"power\":\"3.8\",\"hand_seals\":\"522\",\"element\":\"None\",\"cooldown\":\"4\",\"purchase_cost\":\"10000\",\"use_cost\":\"200\",\"description\":\"As your power grows, you can cover others in your metallic shell via touch. This power fuels the darkest rumours about your clan, many of which might be true.\",\"battle_text\":\"Moving with quicksilver agility, [player] ducks low and launches a barrage of blows at [opponent]&#039;s legs. With each strike, a patch of shining metal forms over their flesh, rigid and unbreakable. As [opponent]&#039;s movements start to slow, [player] doubles their speed and strength, striking with such ferocity that every inch of [opponent] is coated in a shell of metal they cannot escape from. All that remains is a motionless statue.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"speed_nerf\",\"effect_amount\":\"10\",\"effect_length\":\"2\"}]'),
(45, 'Frigid Tundra', 31, 'Mist', '1', '[{\"power\":\"20\",\"effect\":\"regen\"}]', '[{\"power\":\"25\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"15\",\"effect\":\"cast_speed_boost\"}]', '[{\"name\":\"Winter&#039;s Cruelty\",\"rank\":\"2\",\"power\":\"2.0\",\"hand_seals\":\"523\",\"element\":\"Water\",\"cooldown\":\"4\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Senbon coated in your bloodlineu2019s unique freezing chakra not only puncture vital spots on your opponent, but chill them to the core as well.\",\"battle_text\":\"[player] pulls a handful of senbon from [gender2] pouch, coating them in freezing cold chakra. As [player] kicks a spray of snow into [opponent]&#039;s eyes, [gender2] launches the senbon one by one into nerve clusters. [opponent] would scream out in pain if they weren&#039;t immobilised.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"taijutsu_nerf\",\"effect_amount\":\"15\",\"effect_length\":\"2\"},{\"name\":\"Rimestorm\",\"rank\":\"3\",\"power\":\"2.9\",\"hand_seals\":\"524\",\"element\":\"Water\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"By supercooling water in the air around your opponent, you can punish them for every move they dare to make.\",\"battle_text\":\"[player] focuses their chakra into the air, creating a scintillating dance of tiny ice crystals around [opponent]. [opponent] charges forward, realising too late that each crystal they touch freezes to them, weighing them down considerably. It takes another few moments for [opponent] to start struggling to breathe, as the glittering rime crystals start to freeze inside their lungs.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"15\",\"effect_length\":\"2\"},{\"name\":\"An Age of Ice\",\"rank\":\"4\",\"power\":\"4.4\",\"hand_seals\":\"525\",\"element\":\"Water\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"200\",\"description\":\"The frozen north is a land always warped by the cold. Your ancestors survived it, can your opponents?\",\"battle_text\":\"Tapping into their bloodline, [player] unleashes a wave of cold that freezes everything as far as the eye can see. [opponent] survives this, but is lost in the blizzard as they feel a strange rumbling underfoot. Suddenly, a massive crystalline horn pierces through the ice into [opponent]&#039;s chest, lifting them high into the sky. [player] offers a silent prayer to [gender2] frozen god as the mighty narwhal slips back into the icy realm below.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"}]'),
(46, 'Ancient&#039;s Strength', 34, 'Sand', '1', '[{\"power\":\"20\",\"effect\":\"regen\"}]', '[{\"power\":\"30\",\"effect\":\"taijutsu_boost\"},{\"power\":\"10\",\"effect\":\"speed_boost\"}]', '[{\"name\":\"War God&#039;s Bravado\",\"rank\":\"2\",\"power\":\"2.0\",\"hand_seals\":\"526\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"You have the power of a war god pumping through your veins, there&#039;s no contest of strength you could ever lose! Time to prove it!\",\"battle_text\":\"[player] issues a challenge to [opponent], a simple contest to see who is the strongest. Finding a large boulder nearby, [player] flexes [gender2] muscles and lifts the colossal stone overhead with a single hand. With a jovial laugh, [player] tosses the boulder to [opponent], trusting so light a stone should be no trouble for them. [player] is shocked when [opponent] is crushed underneath the massive weight.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"15\",\"effect_length\":\"2\"},{\"name\":\"War God&#039;s Legacy\",\"rank\":\"3\",\"power\":\"2.9\",\"hand_seals\":\"527\",\"element\":\"None\",\"cooldown\":\"4\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"Wield a legendary blade forged from a meteor that fell to earth at the dawn of time. Anything stuck by it doubles in weight, as does the blade itself. Only special individuals can wield such a weapon.\",\"battle_text\":\"[player] draws a massive black blade from their back and swings it like it weighs little more than paper despite it being thrice the size of a man and a thousand times as heavy. With each delicate blow, [opponent] feels the weight around the cut increase immensely. Before long they can barely stand. The blade grows heavier too, but [player] only laughs as they deliver a rapid barrage of blows that leaves [opponent] crushed under their own weight.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"cast_speed_nerf\",\"effect_amount\":\"15\",\"effect_length\":\"2\"},{\"name\":\"War God&#039;s Might\",\"rank\":\"4\",\"power\":\"4.4\",\"hand_seals\":\"528\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"200\",\"description\":\"Some revere delicate gods who pulled the earth towards heaven to craft mountains, or painted rivers across the land with a gentle brush stroke. You&#039;re not that kinda god!\",\"battle_text\":\"[player] informs [opponent] [gender2] can win the fight with one punch. [opponent]&#039;s disbelief is turned rapidly to shock when that very punch is delivered. [opponent] is launched off their feet and sent flying towards the horizon. [opponent]&#039;s long flight at last comes to a stop when they smash through the side of a mountain into its molten heart as the mountain explodes in a devastating display of [player]&#039;s awesome might.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"}]'),
(17, 'Death&#039;s Gaze', 23, 'Leaf', '1', '[{\"power\":\"10\",\"effect\":\"scout_range\"}]', '[{\"power\":\"30\",\"effect\":\"genjutsu_boost\"},{\"power\":\"10\",\"effect\":\"heal\"}]', '[{\"name\":\"Twilight Apocalypse\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"201\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Trap your opponent in a nightmarish illusion where the world around them ends, rapidly sapping their will to fight.\",\"battle_text\":\"[player]&#039;s right eye lights up with an ethereal blue glow, drawing [opponent]&#039;s attention. As eye contact is made, [opponent] feels an unmistakable shift. The sky grows dark, the earth begins to crack, and everywhere the desperate screams of innocent souls ring out to an uncaring universe. [opponent] tries as hard as they can to survive, but to no effect. For [opponent], it feels like days have passed. For [player], mere seconds.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"54\",\"effect_length\":\"2\"},{\"name\":\"Fate&#039;s Beckoning\",\"rank\":\"3\",\"power\":\"3.5\",\"hand_seals\":\"209\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"Play upon your opponent&#039;s darkest fears and show them the dark future that awaits them if they dare oppose you.\",\"battle_text\":\"[player]&#039;s left eye shines a brilliant red hue, which floods the battlefield. [opponent] can&#039;t look away, and as eye contact is made they feel a pain in their gut. As they look around [opponent] realises they are amidst their closest allies who have stood by them through all manner of trials. Suddenly, [opponent] realises they are chained in place. One by one their friends draw blades and take turns stabbing [opponent] before vanishing.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"54\",\"effect_length\":\"2\"},{\"name\":\"Breaking the Heavenly Law\",\"rank\":\"4\",\"power\":\"4.4\",\"hand_seals\":\"210\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"200\",\"description\":\"Your eyes contain potent illusions, but what if you could make them real?\",\"battle_text\":\"[player]&#039;s eyes radiate a sickening purple light as [gender2] gazes up at the sky. A leering, moon sized skeletal falls from the sky towards [opponent]. Realising this is a trick, [opponent] forms a seal and releases the illusion, the face in the sky doesn&#039;t vanish. [player] has pulled this terrifying threat from nightmares into reality. [opponent] screams as the skull crashes into the earth and consumes them in balefire.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"100\",\"effect_length\":\"1\"}]'),
(51, 'Godslayer', 38, 'Leaf', '1', '[{\"power\":\"20\",\"effect\":\"regen\"}]', '[{\"power\":\"30\",\"effect\":\"taijutsu_boost\"},{\"power\":\"10\",\"effect\":\"genjutsu_resist\"},{\"power\":\"10\",\"effect\":\"taijutsu_resist\"}]', '[{\"name\":\"Titan Breaker\",\"rank\":\"2\",\"power\":\"2.1\",\"hand_seals\":\"717\",\"element\":\"None\",\"cooldown\":\"4\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Draw upon the power of God Slayers past to deliver a blow that could fell the mightiest of foes.\",\"battle_text\":\"[player] forms a single hand seal as their chakra explodes around them. Shifting through countless hues, their body is imbued with the power of those who came before them. That chakra surrounds their fist with an explosive force, unleashed when [player] slams into [opponent] and knocks the wind right out of them.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"ninjutsu_nerf\",\"effect_amount\":\"15\",\"effect_length\":\"2\"},{\"name\":\"Crashing Heavens\",\"rank\":\"3\",\"power\":\"3.5\",\"hand_seals\":\"718\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"120\",\"description\":\"Calling on the boundless powers of your ancestors, you can deliver truly frightening attacks few can withstand.\",\"battle_text\":\"Shrouded in coruscating waves of multicoloured power, [player] walks slowly towards [opponent], the earth cracking underfoot. Vanishing in a blur of light, [player] launches into a ferocious volley of punches and kicks [opponent] can&#039;t keep up with.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"},{\"name\":\"Ancestral Legacy\",\"rank\":\"4\",\"power\":\"4.4\",\"hand_seals\":\"720\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"200\",\"description\":\"Your bloodline draws on the power of your ancestors to give you the strength to break the chains of divine oppression. Not even a god can stop you now.\",\"battle_text\":\"With a roar of such primal power that [opponent] staggers in fear, [player] rushes forward. Their multi-hued chakra explodes into hundreds of humanoid figures, the shapes of every ancestor who fought battles in days past. As one, the legion swarms on [opponent], delivering an onslaught of blows generations in the making.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"}]'),
(50, 'Phantasmal Stalker', 37, 'Cloud', '1', '[{\"power\":\"10\",\"effect\":\"stealth\"}]', '[{\"power\":\"30\",\"effect\":\"genjutsu_boost\"},{\"power\":\"10\",\"effect\":\"genjutsu_resist\"},{\"power\":\"10\",\"effect\":\"taijutsu_resist\"}]', '[{\"name\":\"Phantom Double\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"714\",\"element\":\"None\",\"cooldown\":\"4\",\"purchase_cost\":\"10000\",\"use_cost\":\"35\",\"description\":\"Your clan is born with two spirits inhabiting a single body. With effort, you can draw the second out for a ferocious combo!\",\"battle_text\":\"[player] suddenly falls forward, a shadowy duplicate of [gender2] body emerging from their body. Regaining their composure, [player] and their double team up and overcome [opponent]&#039;s defences. [opponent] realises this will not likely be an easy fight.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"genjutsu_nerf\",\"effect_amount\":\"50\",\"effect_length\":\"2\"},{\"name\":\"Shards of Self\",\"rank\":\"3\",\"power\":\"3.5\",\"hand_seals\":\"715\",\"element\":\"None\",\"cooldown\":\"6\",\"purchase_cost\":\"10000\",\"use_cost\":\"120\",\"description\":\"Your shadow sibling can indeed perform excellent teamwork with you, but with training they can learn to stand up for themselves.\",\"battle_text\":\"[player] throws a smoke bomb at the ground, obscuring [opponent]&#039;s vision. Undeterred, [opponent] continues the fight, trading blows but taking many in return. [player] watches from a distance, content to watch the futile battle play out from safety.\",\"use_type\":\"buff\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"genjutsu_boost\",\"effect_amount\":\"30\",\"effect_length\":\"3\"},{\"name\":\"Splintered Spirits\",\"rank\":\"4\",\"power\":\"4.4\",\"hand_seals\":\"716\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"200\",\"description\":\"Knowing how to separate one of your souls from your body has taught you much, even how to temporarily inflict this &#039;gift&#039; on others.\",\"battle_text\":\"With a single, devastating palm to the chest of [opponent] you briefly sever the connection between their spirit and self. Ejected from their mortal shell, their soul is defenceless as your own spectre leaps from your body to delivering a withering hail of blows.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"50\",\"effect_length\":\"2\"}]'),
(52, 'Terracotta Soldiers', 39, 'Stone', '1', '[{\"power\":\"10\",\"effect\":\"scout_range\"}]', '[{\"power\":\"30\",\"effect\":\"genjutsu_boost\"},{\"power\":\"10\",\"effect\":\"taijutsu_resist\"},{\"power\":\"10\",\"effect\":\"ninjutsu_resist\"}]', '[{\"name\":\"Stone Sentinel\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"721\",\"element\":\"None\",\"cooldown\":\"4\",\"purchase_cost\":\"10000\",\"use_cost\":\"35\",\"description\":\"You spent hours carving the handful of small, terracotta soldiers you carry with you. Sprinkling one with your blood, your illusions bring it to life.\",\"battle_text\":\"[player] pulls a tiny orange figure of a soldier from a pouch at [gender2] side. [player] then cuts their finger and sprinkles a few drops of blood on the figure, which grows to full size and bears its shield to stand between [player] and [opponent] as a watchful guardian.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"ninjutsu_nerf\",\"effect_amount\":\"33\",\"effect_length\":\"2\"},{\"name\":\"Empty Fortress Illusion\",\"rank\":\"3\",\"power\":\"3.5\",\"hand_seals\":\"722\",\"element\":\"None\",\"cooldown\":\"4\",\"purchase_cost\":\"10000\",\"use_cost\":\"120\",\"description\":\"With your ability to conjure stone golems and illusions to make them appear animate, it isn&#039;t hard to trick your opponent into fearing a threat that isn&#039;t even there.\",\"battle_text\":\"[player] throws down a small carving for a fortress, guarded by dozens of clay soldiers. With a dash of blood, the fortress and soldiers grow to full size, and [player] sits alone at the gate. [opponent] is too clever to fall for such a trick, and retreats, unwilling to fall into [player]&#039;s trap.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"taijutsu_nerf\",\"effect_amount\":\"33\",\"effect_length\":\"2\"},{\"name\":\"To Deceive the Heavens\",\"rank\":\"4\",\"power\":\"4.4\",\"hand_seals\":\"723\",\"element\":\"Fire\",\"cooldown\":\"2\",\"purchase_cost\":\"10000\",\"use_cost\":\"200\",\"description\":\"Your bloodline&#039;s true power simply causes clay figures to grow to life size. A simple trick a clever opponent can best. You have to be smarter.\",\"battle_text\":\"[player] animates a handful of terracotta soldiers, but [opponent] sees through the illusion and shatters the clay figures, completely ignoring the illusion [player] had cast. Just as [player] had planned, the shattered soldiers cover [opponent] in oil that was stored inside them. [opponent] panics all too late as [player] tosses a lighter into the oil, setting the battlefield ablaze.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"35\",\"effect_length\":\"3\"}]'),
(53, 'Ancient&#039;s Deception', 40, 'Sand', '1', '[{\"power\":\"5\",\"effect\":\"scout_range\"},{\"power\":\"10\",\"effect\":\"regen\"}]', '[{\"power\":\"15\",\"effect\":\"heal\"},{\"power\":\"20\",\"effect\":\"genjutsu_boost\"}]', '[{\"name\":\"Party Tricks\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"724\",\"element\":\"None\",\"cooldown\":\"6\",\"purchase_cost\":\"10000\",\"use_cost\":\"40\",\"description\":\"Your powers were stolen, long ago, from a trickster god. Time to show what that power can do.\",\"battle_text\":\"[player] narrowly avoids an attack from [opponent], but misplaces their footing and falls backwards over a small rock. [opponent] moves in to capitalise on this advantage, but by the time they reach [player], no one is there. The ground suddenly gives way, and [opponent] falls painfully into a hastily dug pit trap.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"taijutsu_nerf\",\"effect_amount\":\"33\",\"effect_length\":\"3\"},{\"name\":\"Now You See Me\",\"rank\":\"3\",\"power\":\"3.5\",\"hand_seals\":\"725\",\"element\":\"None\",\"cooldown\":\"2\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"The powers of a trickster god allow you to manipulate perception in impossible ways, blows that should be fatal pass right through you, to your enemies horror.\",\"battle_text\":\"[opponent] launches a vicious barrage of attacks towards [player], but [gender] doesn&#039;t move an inch. Each blow passes through [gender2] body as though it weren&#039;t really there. Laughing, [player] allows [opponent] to exhaust themselves entirely while they barely lift a finger.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"ninjutsu_nerf\",\"effect_amount\":\"50\",\"effect_length\":\"2\"},{\"name\":\"The Grand Illusion\",\"rank\":\"4\",\"power\":\"4.4\",\"hand_seals\":\"726\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"200\",\"description\":\"The knowledge that an attack has landed is the peak satisfaction one can find in battle. Your powers let you rip that away from any who stand against you.\",\"battle_text\":\"[player] pulls out a sword and clumsily swings it at [opponent], but [gender2] is easily disarmed. [opponent] wastes no time, reversing the blade and running it through [player]&#039;s chest. [gender2] laughter fills the air, and moments later [opponent] realises that somehow, they stabbed themselves with the stolen sword. [player] can&#039;t stop laughing at the situation before them.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"50\",\"effect_length\":\"2\"}]');
"
        );

        // Clans
        $this->execute("
        insert into clans (clan_id, village, name, bloodline_only, boost, boost_amount, points, leader, elder_1, elder_2, challenge_1, logo, motto, info)
values  (1, 'Leaf', 'Kobayashi', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (2, 'Sand', 'Tsukino', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (3, 'Leaf', 'Himura', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (4, 'Leaf', 'Sugi', 0, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (8, 'Stone', 'Kiku', 0, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (5, 'Cloud', 'Yokoyama', 0, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (6, 'Stone', 'Haniwa', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (7, 'Cloud', 'Aozora', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (9, 'Mist', 'Hashi', 0, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (10, 'Sand', 'Iijima', 0, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (11, 'Mist', 'Mizumaki', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (12, 'Leaf', 'Tsuruya', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (13, 'Stone', 'Yoshitomi', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (14, 'Sand', 'Kurosawa', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (15, 'Cloud', 'Tomioka', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (16, 'Leaf', 'Joshuya', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (17, 'Mist', 'Koizumi', 0, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (18, 'Sand', 'Kasuse', 0, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (19, 'Stone', 'Momotami', 0, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (20, 'Cloud', 'Uesugi', 0, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (21, 'Leaf', 'Nakano', 0, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (22, 'Mist', 'Kurushimi', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (23, 'Leaf', 'Shukumei', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (37, 'Cloud', 'Yomi', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (38, 'Leaf', 'Kamikoro', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (39, 'Stone', 'Jidou', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (40, 'Sand', 'Damasu', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (24, 'Cloud', 'Kibou', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (25, 'Stone', 'Hosoku', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (26, 'Sand', 'Zetsubou', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (27, 'Mist', 'Haninozuka', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (28, 'Mist', 'Maigo', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (29, 'Leaf', 'Baransu', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (30, 'Stone', 'Hagane', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (31, 'Mist', 'Koori', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (32, 'Sand', 'Chikara', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (33, 'Cloud', 'Fumetsu', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (34, 'Sand', 'Kokomotsu', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (35, 'Cloud', 'Misaki', 1, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
        (36, 'Mist', 'Mitzuku', 0, '', 0, 0, 0, 0, 0, '0', './images/default_avatar.png', '', '');");

        // Items
        $this->execute(
            "INSERT INTO `items` (`item_id`, `name`, `description`, `rank`, `purchase_type`, `purchase_cost`, `use_type`, `effect`, `effect_amount`) VALUES
    (1, 'Goggles', NULL, 1, 1, 500, 2, 'harden', 150),
(2, 'Kusarigama', NULL, 3, 1, 15000, 1, 'daze', 20),
(6, 'Kunai', NULL, 1, 1, 700, 1, 'residual_damage', 15),
(3, 'Incredibly Long Scarf', NULL, 1, 1, 500, 2, 'harden', 150),
(4, 'Healing Salve', NULL, 3, 1, 720, 3, 'heal', 28000),
(5, 'Katana', NULL, 3, 1, 25000, 1, 'residual_damage', 25),
(7, 'Ninjato', NULL, 2, 1, 3000, 1, 'residual_damage', 20),
(8, 'Bo Staff', NULL, 2, 1, 1800, 1, 'daze', 5),
(9, 'Mesh Armor', NULL, 2, 1, 2200, 2, 'harden', 400),
(10, 'Full Body Suit', NULL, 2, 1, 4000, 2, 'lighten', 5),
(11, 'Large Hexagonal Shuriken', NULL, 3, 1, 10000, 1, 'cripple', 14),
(12, 'Standard Flak Jacket', NULL, 3, 1, 7500, 2, 'harden', 1000),
(13, 'Covert Attire', 'Very lightweight armor that allows for swift movement.', 3, 1, 16000, 2, 'lighten', 5),
(14, 'Wound Disinfectant Spray', NULL, 2, 1, 240, 3, 'heal', 4800),
(15, 'Ancient Fan', NULL, 3, 1, 25000, 1, 'diffuse', 15),
(16, 'Chakra Blade', NULL, 4, 1, 30000, 1, 'element', 10),
(17, 'Crystal Pendant', 'A', 1, 2, 1000000, 4, 'unknown', 0),
(18, 'Fingerless Gloves', 'Fingerless gloves that lightly compress the hands to improve circulation without obstructing, boosting the wearer&#039;s ability to move their hands a', 3, 1, 8000, 2, 'cast_speed_boost', 5);
"
        );

        // Jutsu
        $this->execute(
            "INSERT INTO `jutsu` (`jutsu_id`, `name`, `jutsu_type`, `rank`, `power`, `hand_seals`, `element`, `parent_jutsu`, `purchase_type`, `purchase_cost`, `use_cost`, `use_type`, `cooldown`, `battle_text`, `description`, `effect`, `effect_amount`, `effect_length`) VALUES
    (1, 'Spinning Stars', 'taijutsu', 1, 1.5, '1433', 'None', 0, 2, 200, 10, 'projectile', 0, '[player] pulls out from [gender2] pouch a small handful of shuriken which they toss simultaneously. Aimed at [opponent]&#039;s trajectory, all varying in speed to make it harder to dodge.', 'A very basic move but a move that is highly used by many in one form or another. To put it simply, throwing multiple shuriken at once.', 'none', 0, 0),
(2, 'Synergized Channeling', 'ninjutsu', 1, 1.9, '12-8-1', 'None', 0, 2, 500, 10, 'projectile', 0, 'Entering a calm and relaxed state while building up increasing amounts of energy [player] release some of this energy in a well timed punch filled with chakra particles.', 'With many hours put into studying Chakra nature and increased time and effort one can learn to enhance the power of their abilities.', 'none', 10, 1),
(3, 'Standard Strike', 'taijutsu', 1, 1.2, '106', 'None', 0, 1, 0, 10, 'physical', 0, '[player] performs a swift punch to [opponent]&#039;s head.', 'A basic punch.', 'none', NULL, NULL),
(4, 'Basic Replacement', 'ninjutsu', 1, 1.2, '1-2', 'None', 0, 2, 100, 10, 'projectile', 0, '[player] avoids an attack at the last second by swapping places with a log in a cloud of smoke. In a moment of confusion, [opponent] is caught off guard by a high-speed attack.', 'A basic Ninjutsu.', 'taijutsu_nerf', 10, 2),
(6, 'Chakra Torrent', 'ninjutsu', 1, 1.5, '10-7', 'None', 0, 2, 500, 10, 'projectile', 0, 'Utilizing [gender2] inner flow of chakra [player] fire&#039;s out a wave of scything transparent blue energy from their body which leaves [opponent] wounded from the many cuts and bruises the Jutsu makes.', 'This ability requires a fair understanding of one&#039;s chakra flow as well as its nature, a good enough balance can lead to deadly attacks.', 'residual_damage', 15, 2),
(7, 'Triggered Explosion', 'ninjutsu', 2, 2.4, '9-7', 'None', 0, 2, 2500, 25, 'projectile', 1, '[player] lures [opponent] to a specific area before disappearing from [opponent]&#039;s line of sight. Standing on a branch high above, [player] activates the many paper bombs in the area that simultaneously explode, catching [opponent] within the blast radius.', 'A devastating Ninjutu that when triggered will cause a massive explosion from all the strategically placed paper bombs.', 'residual_damage', 10, 3),
(8, 'Kiddy Combo', 'taijutsu', 1, 1.5, '165', 'None', 0, 2, 500, 10, 'physical', 0, 'Taking a awkwardly positioned offense stance [player] throws a multitude of punches and kicks to [opponent]&#039;s body.', 'A basic barrage of punches and kicks that are slow, sloppy and in no way lethal. but, every Taijutsu user has to start out somewhere.', 'none', 5, 2),
(9, 'Deceiving Environment', 'genjutsu', 1, 1.8, '6-4-3-7', 'None', 0, 2, 800, 10, 'projectile', 0, '[player] quickly runs in the opposite direction of [opponent]. As [opponent] gives chase [player] transforms into a rock out of site to block the path, giving [opponent] only one route where they soon fall victim to a pitfall.', 'A technique that is used to confuse the enemy and lead them into a trap that was prepared earlier.', 'residual_damage', 55, 2),
(10, 'Multiple Clone Panic', 'genjutsu', 1, 1.5, '6-2-5-3', 'None', 0, 2, 500, 10, 'projectile', 0, 'In a matter of seconds [opponent] is surround by up to as many as ten clones of [player]. The clones move around [opponent], making them dizzy and lightheaded from the fast images.', 'This ability uses the simple method of clones to confuse and agitate the enemy.', 'residual_damage', 100, 1),
(11, 'Genjutsu Release', 'genjutsu', 2, 2.1, '9-6', 'None', 0, 2, 1500, 25, 'projectile', 0, '[player] forms a handseal and briefly interrupts the chakra to [gender2] mind, attempting to restore normal chakra flow.', 'A jutsu that restores normal chakra flow, releasing genjutsu.', 'release_genjutsu', 2.5, 1),
(12, 'Transformation Trickery', 'ninjutsu', 1, 1.3, '3-2-12', 'None', 0, 2, 225, 10, 'projectile', 0, '[player] runs into a nearby copse of trees, summoning [gender2] chakra to transform into a tree. As [opponent] enters the treeline, they strike hard each tree they pass, confident [player] is hiding somewhere within. [opponent]â€™s face bursts into shock when they realise the tree they went to attack is suddenly fighting back!', 'Use the power of disguise to get one over on your enemy.', 'genjutsu_nerf', 10, 2),
(13, 'Unexpected Sting', 'taijutsu', 1, 1.9, '100', 'None', 0, 2, 900, 10, 'physical', 1, '[player] sends [opponent] flying with a kick to the gut before revealing a hidden kunai, held on the right sandal sole by chakra, which is launched after the kick to critically wound [opponent].', 'A hidden weapon can be fatal in the right circumstances, combining it with a kick adds insult to injury.', 'residual_damage', 10, 3),
(14, 'Reeling Blow', 'taijutsu', 1, 1.8, '101', 'None', 0, 2, 800, 10, 'physical', 1, 'Giving [opponent] little to no time to react [player] sidesteps, curling their arm inward with their elbow pointed out and delivers one solid blow to [opponent]&#039;s gut.', 'With one swift movement this technique delivers a devastating blow to the gut, using your own elbow as a weapon.', 'none', NULL, NULL),
(15, 'Augmented Shuriken', 'ninjutsu', 2, 2.6, '3-8', 'None', 0, 2, 3000, 25, 'projectile', 0, '[player] throws two small shuriken in [opponent]&#039;s direction. chuckling at the feeble attempt, [opponent] twists to dodge both shuriken but is met with a surprise when the two shuriken expand in shape, being released from their transformation and revealing their true form as windmill shuriken.', 'Transforms two windmill Shuriken into normal shuriken that expand back to normal size when in enemies vicinity.', 'none', NULL, NULL),
(16, 'Ascending Hawk Strike', 'taijutsu', 2, 2.1, '102', 'None', 0, 2, 1500, 25, 'physical', 1, 'Moving at increased speeds [player] ducks just before reaching [opponent], with one movement [gender] lifts up one arm, palm open, and strikes right below [opponent]&#039;s chin causing their head to lift before being launched in the air.', 'If this technique is done right it can send an opponent flying high into the sky, easily open for further attack.', 'none', 12.5, 2),
(17, 'Chakra Needles', 'ninjutsu', 2, 2.4, '11-1-2', 'None', 0, 2, 2000, 25, 'projectile', 1, 'Taking in a huge breath [player] waits till [gender] can no longer hold [gender2] breath or [opponent] is at an optimal range, when the opportunity arises [player] fires a multitude of blue tinted chakra needles.', 'Using ones own inner chakra flow they can morph their chakra into a form that can be extremely sharp.', 'taijutsu_nerf', 10, 2),
(18, 'Chakra Distortion', 'ninjutsu', 2, 2.9, '6-3-12-10', 'None', 0, 2, 4000, 25, 'projectile', 2, '[player] forms a small amount of chakra in [gender2] palm and with this [gender] moves to strike [opponent]&#039;s chest at a perfect angle, when struck [opponent]&#039;s body and inner chakra flow is jumbled for a short period.', 'A ability that uses basic chakra understanding while understanding also how it can effect the body.', 'genjutsu_nerf', 9.5, 2),
(19, 'Projectile Barrage', 'taijutsu', 2, 2.4, '104', 'None', 0, 2, 2500, 25, 'physical', 1, 'Pulling out a small scroll [player] quickly opens it, the writing of the scroll facing [opponent]. letting the scroll hover in the air [player] slams [gender2] hands together causing hundreds of kunai and shuriken to shower [opponent].', 'Utilizing the handiness of a summoning scroll this jutsu allows for massive amounts of weaponry to be stored and fired.', 'taijutsu_nerf', 10, 2),
(20, 'Rhythmic Assault', 'taijutsu', 2, 2.6, '105', 'None', 0, 2, 3000, 25, 'physical', 1, '[player] starts by delivering a powerful side kick to [opponent]s Gut, While [opponent] is reeling in pain [player] calculates [opponent]s reaction and launches a series of powerful strikes to unprotected spots.', 'A series of powerful blows that any beginner in Taijutsu can pull off, these set of strikes can cause massive damage with the right pattern.', 'none', NULL, NULL),
(21, 'Smoke Mirror', 'genjutsu', 2, 2.6, '1-12', 'None', 0, 2, 3000, 25, 'projectile', 1, '[player] pulls out a smoke bomb from [gender2] pouch and tosses it in opponents vicinity. [player] then casts a Genjutsu that forces [opponent] to fight an exact copy of them self, slowly depleting [opponent]&#039;s energy.', 'A Genjutsu that requires the use of a smoke bomb to create an elaborate illusion.', 'residual_damage', 54, 2),
(22, 'Realm of Desolation', 'genjutsu', 2, 2.9, '11-8-1-5', 'None', 0, 2, 4000, 25, 'projectile', 2, 'A devilish grin is all [opponent] can see from [player] before they are enveloped in darkness. The stillness and silence worries [opponent] as they are met many distant growls, mumbles and laughs that slowly drive [opponent] insane.', 'Beginners level Genjutsu that slowly drives even some of the strongest willed people mad.', 'intelligence_nerf', 55, 2),
(29, 'Dragon&#039;s Scorn', 'ninjutsu', 3, 3.9, '12-10-5-6-3', 'Fire', 0, 2, 18000, 65, 'projectile', 2, '[player] lets the chakra within [gender2] body mold and take shape within the stomach before spewing out a linear line of fire from [gender2] mouth that runs across the ground and bathes the area in intensifying flames.', 'The power of this technique is only as powerful as its casters ability to control their element and properly disperse its flames', 'residual_damage', 25, 2),
(23, 'Wired Weaponry', 'taijutsu', 2, 2.9, '138', 'None', 0, 2, 4000, 25, 'physical', 2, '[player] Quickly tosses two wired Shuriken that zoom past [opponent] and wrap around a tree branch. [opponent], unaware of the wires is caught off guard when [player] uses another tree branch as leverage to snap the previous branch which flies at [opponent] with great speed.', 'Using an arsenal of gear you can always come up with some sort of tactic even in the most dire of times.', 'ninjutsu_nerf', 8.75, 2),
(24, 'Energy Flare', 'ninjutsu', 2, 2.1, '7-1-4', 'None', 0, 2, 1500, 25, 'projectile', 0, 'Focusing [gender2] chakra in one palm and closing [gender2] fingers around the now visible chakra, [player] releases the orb of concentrated chakra into the sky where it expands and explodes with a huge flash of light sending waves of chakra that pelt the area and [opponent].', 'An intense expansion of chakra that eventually explodes and sends out harmful pulses of chakra.', 'willpower_nerf', 5, 2),
(25, 'Caves of Disillusion', 'genjutsu', 2, 2.4, '5-1-3-8', 'None', 0, 2, 2500, 25, 'projectile', 1, '[player] tricks [opponent] into following them inside a nearby cave where [opponent] quickly loses track of [player]. Lost within the caves, [opponent] is suddenly barraged with projectiles from a carefully arranged ambush.', 'A jutsu that purposefully leads enemy in the wrong direction time after time till they are to exhausted to continue.', 'residual_damage', 100, 1),
(26, 'Searing Agony', 'genjutsu', 1, 1.2, '12', 'None', 0, 2, 100, 10, 'projectile', 0, ' [player] touches [opponent], connecting their flows of chakra. The unfamiliar presence enters [opponent]&#039;s mind generating pragmatic burning sensations.', 'The most simplistic of genjutsu, usable by anybody. Enter your opponents mind and make them believe their body is on fire!', 'residual_damage', 55, 2),
(27, 'Chakra Negation', 'genjutsu', 2, 2.1, '4-10-2', 'None', 0, 2, 2000, 25, 'projectile', 4, '[player] cast a Genjutsu upon [gender2] own body, one that decreases the effectiveness of any chakra based attack by activating key points in [player]&#039;s chakra network that are not easily opened using traditional methods.', 'Creates a negating field around the body that can reduce the effectiveness of oncoming jutsu.', 'ninjutsu_nerf', 35, 3),
(28, 'Aerial Hammer', 'taijutsu', 2, 2.4, '1112', 'None', 0, 2, 2000, 25, 'physical', 1, '[player] focuses chakra into the soles of [gender2] feet before launching into the air and using the downward momentum to deliver a solid kick to [opponent]&#039;s skull, setting them off balance and ill prepared for the oncoming battle ahead.', 'A solid blow that will leave opponent in a daze and decease their chances of holding the user in a successful genjutsu.', 'genjutsu_nerf', 6, 2),
(30, 'Intensifying Heat', 'ninjutsu', 3, 3.7, '10-5-3-4-8', 'Fire', 0, 2, 6000, 65, 'projectile', 2, '[player] increases the temperature in the surrounding area by emitting heat from [gender2] body. The heat soon becomes unbearable for [opponent] causing them to sweat profusely. [opponent]&#039;s coordination is soon jumbled while they can barely keep themselves from fainting.', 'A jutsu that emits a continues wave of heat from the casters body, steadily increasing the temperature in the surrounding area to unbearable heights.', 'none', 18.5, 4),
(31, 'Chilling Tides', 'ninjutsu', 3, 3.5, '3-5-10-4-1', 'Water', 0, 2, 6000, 65, 'projectile', 1, 'With one quick motion [player] performs the hand signs needed while near a source of water, from that source shoots a wave of freezing water that slams into enemy, causing average damage and leaving [opponent] in a state of hypothermia.', 'A technique using ones own water ability as well as the ability to change the waters temperature at opportune moments.', 'taijutsu_nerf', 20, 2),
(32, 'Liquid Bullet', 'ninjutsu', 3, 3.9, '7-3-9-2-11', 'Water', 0, 2, 18000, 65, 'projectile', 2, 'Without being near a source of water, [player] gathers small particles of liquid from under the earth to form stable orb of water which is fired at incredible speeds much like that of a bullet. When contact is made, [opponent] is sent flying into a nearby structure.', 'Forming water in a small spherical shape that can then be fired at incredibly high speeds which are near to impossible to dodge.', 'residual_damage', 25, 2),
(33, 'Dancing Winds', 'ninjutsu', 3, 3.9, '5-4-8-12-2', 'Wind', 0, 2, 18000, 65, 'projectile', 2, '[player] kicks up a huge dust storm that blinds [opponent]&#039;s vision. within this space of time [player] launches many timely but small crescent shaped wind strikes that cut at [opponent]&#039;s limbs causing severe gashes and hemorrhaging.', 'Many people have been fooled by the change in wind and its patterns, this ability is no different, making anyone helpless to its random nature.', 'residual_damage', 25, 2),
(34, 'Cleaving Moon Crescent', 'ninjutsu', 3, 3.9, '3-7-2-1', 'None', 6, 2, 14000, 65, 'projectile', 1, 'Coating [gender2] blade in a fine, thin layer of chakra, [player] charges the chakra to dangerously high levels. Slashing the empty space in front of themselves several times. [gender] lets loose an array of blue tinted crescent shaped chakra attacks, all rotating at deadly speeds and severely wounding [opponent] on contact.', 'With the help of a blade, chakra is formed and fired out in the shape of a crescent from said blade delivering a devastating blow from its sheer speed and rotation.', 'none', NULL, NULL),
(35, 'Poisonous Haze', 'ninjutsu', 3, 3.5, '6-1-5-12-8', 'None', 0, 2, 6000, 65, 'projectile', 2, '[player] lets out an expanding cloud of yellow poisonous fumes from [gender2] mouth, when the fumes enter the body of [opponent] by any means necessary the effects of nausea, dizziness and pain begin to set in. [opponent]&#039;s nerves are also damaged, leaving their chakra flow crippled.', 'A poisonous yellow cloud is spewed from the casters mouth, causing nausea and dizziness before damage the enemy&#039;s nerves.', 'ninjutsu_nerf', 20, 2),
(36, 'Contained Detonation', 'ninjutsu', 3, 3.5, '7-1-9-2-12', 'None', 7, 2, 8000, 65, 'projectile', 2, '[player] puts up a transparent barrier around [opponent] without their knowledge. Within seconds of [opponent] realizing this, [player] sets off a series of well placed tag bombs that result in a contained but devastating explosion that engulfs the area inside the barrier for several seconds.', 'Placing four highly explosive bomb tags in key locations, a barrier is put up to trap the enemy inside, substantially increasing the power of the contained blast tenfold.', 'residual_damage', 15, 3),
(37, 'Jolting Circuit', 'ninjutsu', 3, 3.5, '11-7-12-9', 'Lightning', 0, 2, 6000, 65, 'projectile', 1, 'Throwing many shuriken in [opponent]&#039;s direction, [player] shoots out a tiny, quick spark of lightning that comes in contact with the scattered shuriken dotted across the ground,. This creates a link between each and every shuriken, causing a massive surge in electricity that fries [opponent].', 'A technical jutsu that brings weapons into play and combines their metal structure with electrical output to deliver devastating volts.', 'genjutsu_nerf', 20, 2),
(38, 'Shallow Ground', 'ninjutsu', 3, 3.5, '3-2-7-10', 'Earth', 0, 2, 6000, 65, 'projectile', 1, '[player] shapes and molds the ground before them, loosening the earth and its minerals. [opponent] is caught of guard and quickly sinks into the ground before it returns to a solid state, leaving [opponent] trapped but also struggling for air with the earth pressed up against their chest.', 'being very simple yet very deadly at the same time this technique can be used to great effect against pursuers as well as a trap simply by trapping them in the earth.', 'taijutsu_nerf', 20, 2),
(39, 'Electrical Clones', 'ninjutsu', 3, 3.9, '4-8-11-3-2', 'Lightning', 0, 2, 18000, 65, 'projectile', 2, '[player] splits [gender2] chakra into three separate clones that all attack [opponent] in unison. Being completely unaware of the situation as [player] had hoped, [opponent] strikes one of the clones head on, causing it and the others to explode in a burst of electrical energy.', 'Much more advanced then a simple clone but not as advanced as a shadow clone, this clone will explode in a wave of electricity when destroyed.', 'residual_damage', 25, 2),
(40, 'Earthly Weapons', 'ninjutsu', 3, 3.9, '10-2-9-4-11', 'Earth', 0, 2, 18000, 65, 'projectile', 2, '[player] cast a basic jutsu that turns the ground below [opponent] into small makibishi like spikes, causing them to stumble. Taking the perceived advantage, [player] molds the earth and pulls out from it, twin throwing spears that [gender] lunges at [opponent] with deadly accuracy.', 'Using the materials in the ground one can make a variety of weapons and distracting traps without the need to buy such expensive equipment.', 'residual_damage', 25, 2),
(41, 'Propelling Burst', 'ninjutsu', 3, 3.7, '8-3-5-1', 'Wind', 0, 2, 6000, 65, 'projectile', 1, 'When the opportune moment arrives [player] releases [gender2] wind natured chakra in a huge exploding burst below [opponent]&#039;s feet, sending them skyward. With [opponent] vulnerable to attack, [player] strikes [opponent] with several well timed shuriken, covered in wind natured chakra.', 'Sends the enemy flying into the air where they are left open to a series of attacks.', 'none', 12.5, 3),
(42, 'Graceful Flying Falcon', 'taijutsu', 3, 3.4, '1008', 'None', 0, 2, 6000, 65, 'physical', 2, 'Seemingly if out from nowhere, [player] is seen within [opponent]&#039;s peripheral vision flying graceful with one leg extended. Before a timely reaction can be made [opponent] is slammed directly on the side of the head with a overwhelming kick which sends them flying.', 'A quick solid kick to the ememy&#039;s head that is often used as a surprise attack when one enters a battle.', 'genjutsu_nerf', 15, 2),
(43, 'Tracheal Smash', 'taijutsu', 3, 3.7, '1009', 'None', 0, 2, 9000, 65, 'physical', 2, '[player] delivers a powerful chop to [opponent]&#039;s throat, causing their airways to seize up as [opponent] drops to their knees, grasping their throat to try and get an ounce of breath. When the connection is remade the lack of oxygen leaves their body shaking, making hand sign forming difficult.', 'A simple looking technique but in reality is very complex, requiring the user to have pinpoint accuracy upon impact.', 'cast_speed_nerf', 10, 2),
(44, 'Somersault Spring', 'taijutsu', 3, 3.9, '1010', 'None', 16, 2, 12000, 65, 'physical', 3, '[player] advances forward by doing a series of somersaults that increase in speed, slowly building up power in the legs. On the last somersault, [player] bends [gender2] arms and legs down before shooting up and delivering a powerful double footed kick to [opponent]&#039;s chin.', 'Uses increased momentum and flexibility to send a violent kick under the enemy&#039;s chin.', 'none', 8, 2),
(45, 'Inverted World', 'genjutsu', 3, 3.7, '11-2-12-1-8', 'None', 0, 2, 10000, 65, 'projectile', 2, '[player] casts a genjutsu upon [opponent] which messes with their coordination causing them to wobble as they walk, after awhile [opponent] can feel the ground shift beneath their feet, the planet itself seems to turn upside down as [opponent] falls out of the atmosphere, their source of oxygen cut off.', 'A jutsu that give the enemy the sensation of being pulled out of the planet&#039;s atmosphere.', 'residual_damage', 54, 2),
(46, 'Consuming Blizzard', 'genjutsu', 3, 3.4, '3-1-5-11', 'None', 0, 2, 8000, 65, 'projectile', 1, 'Within second [opponent] finds a cold chill coming across their body, before they know it the ground is covered in ice and a huge blizzard has blanketed the area, seeing no way of escape, [opponent] waits for their inevitable fate to be sealed as their body temperature soon begins to fade.', 'A freezing winter storm is cast throughout the area, making the enemy believe they are slowly being frozen from the inside out, which causes them to lose their will to live.', 'residual_damage', 100, 1),
(47, 'Time Alteration', 'genjutsu', 3, 3.7, '4-1-7-3-12', 'None', 0, 2, 11000, 65, 'projectile', 2, 'Using an ink seal that [player] has placed on the ground [gender] casts a strong genjutsu over [opponent] which tricks them into believing they are aging rapidly, slowly draining the life force out of [opponent]&#039;s body and causing despair over their inevitable fading life.', 'By the time this genjutsu has faded the enemy will be in an extremely weakened state, barely able to even stand let alone fight.', 'residual_damage', 54, 2),
(48, 'Feasting Blade', 'genjutsu', 3, 3.6, '10-12-6-3-7', 'None', 0, 2, 14000, 65, 'projectile', 4, '[opponent] catches their eyes upon [player]&#039;s blade that pulsates a purple energy which quickly turns itself into a giant hideous monster of a thing. The beast preys on [opponent]&#039;s fear and causes mental blocks in their chakra network.', 'Using your intermediate skills over genjutsu you are able to personify a genjutsu into your small blade, which when looked at or cut with the enemy will be under the genjutsu&#039;s effects.', 'ninjutsu_nerf', 38, 3),
(49, 'Calmed Awareness', 'genjutsu', 3, 3, '9-1', 'None', 0, 2, 6000, 65, 'projectile', 0, '[player] enters a calming state as [gender] breaths deeply and closes [gender2] eyes. Letting a flow of energy ignite the brain and shatter the illusion that held them prisoner.', 'Due to your calmed state and vastly superior knowledge of how Genjutsu works you are more adept at releasing Genjutsu that have been cast upon you.', 'release_genjutsu', 10, 1),
(50, 'Triple Shadow Spiral', 'taijutsu', 3, 3.7, '1011', 'None', 1, 2, 8000, 65, 'physical', 2, '[player] tosses what appears to be one giant shuriken before [gender] adjusts [gender2] position so that the shuriken doubles back and spirals around [opponent] several times, wrapping [opponent] in a thin metal wire. Two new giant shuriken appear from the shadows of the first and strike [opponent] from all angles.', 'Utilizes the helpfulness of wire, wind speed, trickery and a multitude of other skills you have picked up over the course of your genin days to injure the enemy.', 'residual_damage', 12, 2),
(51, 'Distorted Systems', 'taijutsu', 3, 3.7, '1012', 'None', 13, 2, 14000, 65, 'physical', 3, 'Arming themselves with a senbon between each finger, [player] tosses them at key points in [opponent]&#039;s blind spots, hitting their mark each time and cutting off key points in [opponent]&#039;s chakra network, substantially draining their chakra and crippling them.', 'Using impressive accuracy and timing as well as a impressive proficiency over thrown weapons, one can easily cut off key points in the enemy&#039;s chakra flow.', 'drain_chakra', 5, 2),
(52, 'Inverted Swallow', 'taijutsu', 3, 3.9, '1013', 'None', 0, 2, 15000, 65, 'physical', 1, '[player] slams the hilt of [gender2] blade against [opponent]&#039;s chin to lift them up briefly. Before composure can be regained, [player] grabs [opponent] by the ankle and tosses them into the air where [player] lands a series of slashes with a fine edged katana, using the downward momentum, [player] hooks [opponent] with a Kusarigama and flings them into the ground.', 'Uses a Kusarigama as well as a Katana to pull off a complex and highly lethal weaponry technique.', 'none', NULL, NULL),
(53, 'Crawling Flesh', 'genjutsu', 3, 3.9, '3-9-5-12-6-1', 'None', 0, 2, 16000, 65, 'projectile', 3, '[opponent]&#039;s skin begins to show signs of irritation, causing them to scratch uncontrollably. As [opponent] continues the horrible sensation begins to become unbearable, causing them to rip into their skin and cause physical harm and pain just to stop the horrid creature within that tortures them.', 'A highly used Genjutsu in which the enemy believes there is something crawling under their skin, causing them to descend into madness and physically harm themselves.', 'residual_damage', 38, 3),
(54, 'Preliminary Seal', 'ninjutsu', 3, 3.5, '2-7-1-6-11', 'None', 18, 2, 10000, 65, 'projectile', 4, 'When the opportunity arises [player] slams five chakra soaked fingers on [gender2] right hand directly into [opponent]&#039;s gut, causing a temporary seal to form that burns the energy that is stored up inside of [opponent]&#039;s body, which signals immense waves of pain to shoot though the body.', 'A potent seal is engraved on the enemy to inhibit their physical energy.', 'taijutsu_nerf', 20, 2),
(55, 'Piercing Soundwave', 'genjutsu', 3, 3.4, '5-7-4-8', 'None', 0, 2, 5000, 65, 'projectile', 3, 'A low hum is heard throughout the immediate area causing [opponent] to hone in on the source of the sound, making them more susceptible to the ear splitting screech that shortly follows. [opponent] is left dazed and unable to function properly due to the mind altering effects of the sound.', 'Sends a shockwave of sound that damages the enemy&#039;s hearing while also entering the brain to severally injure their mental capabilities.', 'genjutsu_nerf', 54, 2),
(56, 'Shuriken Storm Illusion', 'genjutsu', 3, 3.6, '1-10-3-4-2', 'None', 0, 2, 13000, 65, 'projectile', 4, 'The sky above [opponent] begins to darken as shuriken fall like raindrops, the resulting waves of never ending shuriken make it difficult for [opponent] to land a decent blow due to the awkwardness of dodging the sharp projectiles, that, unbeknownst to [opponent] deliver no immediate threat.', 'After casting the illusion, a downpour of shuriken will begin to fall out of the sky, seemingly never stopping and making the fight difficult for the enemy.', 'taijutsu_nerf', 38, 3),
(57, 'Tears of the Dragon', 'taijutsu', 3, 3.5, '1014', 'None', 19, 2, 10000, 65, 'physical', 2, '[player] tosses a total of six small scrolls up into the sky, the scrolls unravel and unleash waves of small projectiles that seem to track [opponent]&#039;s movements as if they were self aware. The scale of projectiles increases as each scroll that was thrown into the air summons two new scrolls, soon blanketing the sky in parchment and the ground in metal.', 'Using a vast, almost endless supply of small scrolls one can summon a torrent of shuriken, kunai and senbon to rain on the enemy while tracking their movements.', 'ninjutsu_nerf', 15, 2),
(58, 'Ki of the Swift Rabbit', 'taijutsu', 3, 3.9, '1015', 'None', 20, 2, 17000, 65, 'physical', 4, '[player] enters a tranquil state that unlocks a power that enhances [gender2] talents for a short period. With this [player] unleashes a blur of punches that knock [opponent] into the air before sending them crashing into the earth with a drop kick to the gut, the lack in damage is made up for the extreme swiftness of the attack, only taking three seconds.', 'A hidden power is unlocked inside of the body, allowing for quicker movement and enhanced reflexes.', 'speed_nerf', 25, 1),
(59, 'Hachimon: Kai', 'taijutsu', 3, 3.5, '4-11', 'None', 0, 2, 1000, 65, 'buff', 4, '[player] crosses their arms and focuses deep within themselves, unlocking three of the eight inner gates of power within their body. Suddenly their skin turns crimson red and they gain immense energy that can be felt through the air.', 'A powerful but dangerous technique where the user gains greatly increased strength and speed by opening the 8 gates.', 'taijutsu_boost', 50, 2),
(60, 'Chakra Seal Release', 'ninjutsu', 3, 3.5, '8-10', 'None', 0, 2, 1000, 65, 'buff', 4, '[player] forms a handseal and releases an invisible seal, allowing vast reserves of untapped chakra to flow through [gender2] body.', 'User channels chakra from a hidden seal to increase their power.', 'ninjutsu_boost', 50, 2),
(61, 'Mental Vigor', 'genjutsu', 3, 3.5, '1-8', 'None', 0, 2, 1000, 65, 'buff', 4, '[player] pulls out a strange pill and swallows it. The pill temporarily opens new pathways in [gender2] brain, increasing [gender2] mental abilities greatly.', 'Use of a mentally stimulating pill to increase one&#039;s mental acumen.', 'genjutsu_boost', 50, 2),
(62, 'Gaia&#039;s Armory', 'ninjutsu', 4, 4.5, '10-2-9-4-11-1', 'Earth', 40, 2, 80000, 125, 'projectile', 1, '[player] slams [gender2] hands into the earth, pouring chakra into it and causing many weapons to be pushed up, crafted of underground mineral deposites. With countless weapons at [gender2] disposal, [player] begins an unpredictable assault on [opponent] few can withstand.', 'The user creates mighty earthen weapons few smiths can match.', 'residual_damage', 20, 2),
(63, 'Cry of the Flame King', 'ninjutsu', 4, 4.5, '12-10-5-6-3-9', 'Fire', 29, 2, 80000, 125, 'projectile', 1, '[player] focuses on a painful memory, using raw emotion as fuel for the flames of their rage. As white hot fire builds in their hands, [player] screams in pain and unleashes the inferno in an endless torrent that leaves nothing unscathed in its wake.', 'A jutsu that draws on painful memories to devastate the battlefield.', 'residual_damage', 20, 2),
(64, 'Ashen Circle', 'ninjutsu', 4, 4.5, '10-5-3-4-8-1', 'Fire', 30, 2, 80000, 125, 'barrier', 6, '[player] emits three rings of fire around them, each going slightly further than the last. While the attack seems ineffective, [opponent] is shocked when an insect flies over an ashen ring, immediately combusting in an incandescent flash.', 'Barrier - Several rings of flame scorch the ground, the remaining ash destroying any that touch it.', 'none', NULL, NULL),
(65, 'Wrath of the Sea King', 'ninjutsu', 4, 4.5, '7-3-9-2-11-12', 'Water', 32, 2, 80000, 125, 'projectile', 1, '[player] conjures a vast orb or deep blue water behind them, drawn from no visible source. As [opponent] readies their defences, [player] casts forth a single finger as the orb drains itself into a single beam with the incredible power of an entire sea to blast clean through [opponent].', 'A blast of water drawn from no source that devastates all defences.', 'residual_damage', 20, 2),
(66, 'Calm Waters', 'ninjutsu', 4, 4.5, '3-5-10-4-1-2', 'Water', 31, 2, 80000, 125, 'barrier', 6, '[player] surrounds themselves with an orb of still, crystalline blue water that limits vision significantly. Anything that enters the water mysterioustly vanishes as strange shadows in the water consume it in a flurry of teeth and fins.', 'Barrier - Conjures a wall of obscuring water about the user.', 'none', NULL, NULL),
(67, 'Rage of the Sky King', 'ninjutsu', 4, 4.5, '5-4-8-12-2-1', 'Wind', 33, 2, 80000, 125, 'projectile', 1, '[player] grins sadistically as they release a burst of their chakra, causing the air in front of them to be molded into barely visible needles thinner than human hair. [opponent] has little chance to react as the needles rain down in a devastating barrage.', 'A storm of near invisible needles that rains from above.', 'residual_damage', 20, 2),
(68, 'Spiralling Tempest', 'ninjutsu', 4, 4.5, '8-3-5-1-4', 'Wind', 0, 2, 80000, 125, 'barrier', 6, '[player] swiftly forms handseals and infuses chakra into the air around them, causing gusts of wind to form and spin into a massive tornado around [player], deflecting incoming attacks.', 'Barrier - Summons a massive tornado around the user.', 'none', NULL, NULL),
(69, 'Fury of the Storm King', 'ninjutsu', 4, 4.5, '11-7-12-9-1', 'Lightning', 37, 2, 80000, 125, 'projectile', 1, '[player] throws together a string of handseals as lightning surges out from their body. With the final seal, it coalesces into a raging bestial head which surges forward at insane speeds, its shocking jaws clasping around [opponent] in an explosion of energy.', 'The user summons a ferocious beast&#039;s head of lightning to consume their foe.', 'residual_damage', 20, 2),
(70, 'Heavenly Wreath', 'ninjutsu', 4, 4.5, '4-8-11-3-2-1', 'Lightning', 39, 2, 80000, 125, 'barrier', 6, '[player] forms a handseal and slams [gender2] thumb into [gender2] chest. At the point of impact, azure sparks fly before the air itself begins to hum with power, and a radiant cloak of lightning shrouds [player]&#039;s body, protecting it from harm.', 'Barrier - Wreaths the body in an aura of impenetrable lightning.', 'none', NULL, NULL),
(71, 'Aegis of Empires', 'ninjutsu', 4, 4.5, '3-2-7-10-1-4', 'Earth', 38, 2, 80000, 125, 'barrier', 6, '[player] smashes [gender2] palms into the earth, causing the ground to crack and cast up clouds of rock, dust and debris. Chakra allows the cloud to linger in the air, coalescing to stop any incoming attacks.', 'Barrier - The user slams the ground, creating an impenetrable cloud of debris.', 'none', NULL, NULL),
(72, 'Twin Dragon Kick', 'taijutsu', 4, 4.5, '100-7', 'None', 0, 2, 80000, 125, 'physical', 1, '[player] performs an impossibly fluid backflip, delivering a sharp kick to [opponent]&#039;s chin which lifts them off the ground. Pushing off of seemingly nothing, [player] follows up with a front flip, delivering a devastating heel kick that smashed [opponent] into the ground below.', 'The user performs two consecutive kicks with astounding grace.', 'none', NULL, NULL),
(73, 'Seven Circle Strikes', 'taijutsu', 4, 4.9, '100-85', 'None', 0, 2, 100000, 150, 'physical', 2, '[player] moves at lightning speeds, running in rings around [opponent]. Each ring they run leaves an afterimage which repeats the action, until seven cocerntric circles of afterimages encircle [opponent]. As one, the afterimages rush [opponent], and though only one [player] should be real, they distinctly feel seven devastating strikes.', 'A high speed attack which the user moves at incredible speed to create afterimages of themselves.', 'none', NULL, NULL),
(74, 'Explosive Kunai Seal', 'ninjutsu', 4, 4.9, '4-3-11-8-7-1', 'None', 0, 2, 100000, 150, 'projectile', 1, '[player] throws out a handful of kunai that [opponent] can effortlessly dodge. As each hits the ground, a black seal appears around each, spreading into a larger seal that traps [opponent] in place. They can do little but scream as the earth vibrates seconds before the seal unleashes a massive explosion about them.', 'A technique that uses kunai to trap the opponent in an exploding seal.', 'residual_damage', 35, 1),
(75, '100 Shuriken Ambush', 'ninjutsu', 4, 4.1, '12-1-4-6-7-3', 'None', 0, 2, 54000, 100, 'projectile', 0, '[player] turns and runs a short distance away from [opponent], luring them in. As [opponent] passes some suspicious looking rocks, [player] suddenly turns around and forms a blur of handseals. The rocks burst into shuriken that fly towards [opponent] from four directions, cloning themselves into a barrage of 100 shuriken.', 'A technique which multiplies countless shuriken.', 'residual_damage', 10, 2),
(76, 'Sigil of Sin', 'genjutsu', 4, 4.5, '10-12-6-3-7-1', 'None', 48, 2, 80000, 125, 'projectile', 1, '[player] paints a small sigil in the air, which floats ominously. The moment [opponent] looks at it, they are transported back to the moment of their greatest crime. Only this time, they are the victim.', 'A jutsu which turns an opponent&#039;s worst acts upon them.', 'residual_damage', 54, 2),
(77, 'Earthbreaker Assault', 'taijutsu', 4, 4.1, '127-100', 'None', 0, 2, 54000, 180, 'projectile', 0, '[player] dashes around [opponent] in a circle, landling several seemingly weak blows. Too late, [opponent] realises a thick cloth has been wrapped around their body. [player] leaps and spirals in the air, the force of which throws [opponent] up before slamming them into the ground with unbelievable force.', 'A powerful surprise assault that smashes the opponent into the ground.', 'none', NULL, NULL),
(78, 'Impossible Rain', 'genjutsu', 4, 4.5, '1-10-3-4-2-5', 'None', 56, 2, 80000, 125, 'projectile', 1, '[player] throws down a smoke bomb, then casts a handful of salt into the cloud. Blinded, [opponent]&#039;s sense of touch grows to compensate and slight though the change is it leaves them open to [player]&#039;s illusion. Each tiny bump from a falling grain of salt burns like droplets of lava as [opponent] stumbles out of the cloud.', 'A jutsu that harnesses a simple tactile sensation to create a potent illusion.', 'residual_damage', 54, 2),
(79, 'Nerve Destruction', 'genjutsu', 4, 4.9, '4-1-3-10-12-6', 'None', 0, 2, 100000, 150, 'projectile', 2, '[player] sets layers of illusions upon [opponent]. Each one is easily thwarted, building [opponent]&#039;s confidence. This is sorely misplaced however, when [player] places a single finger on [opponent]&#039;s forehead. A pulse of chakra ripples through [opponent]&#039;s body, setting every nerve alight in a flash of pain no mind is built to handle.', 'A genjutsu which momentarily sets every nerve in the foe&#039;s body on fire.', 'residual_damage', 100, 1),
(80, 'Drowning Torrent', 'genjutsu', 4, 4.1, '8-4-1-2-5-6', 'None', 0, 2, 54000, 100, 'projectile', 1, '[player] clicks their fingers in a nonchalant manner. [opponent] readies another attack, but realises their vision is getting a little blurry, their sense of balance is slightly off, and all soound seems distant. Before they realise what&#039;s happening, [opponent] claws at their throat, gasping for air that just won&#039;t come.', 'A genjutsu that simulates the feeling of drowning.', 'residual_damage', 54, 2),
(81, 'Afterimage Flash', 'taijutsu', 4, 4.5, '100-28', 'None', 0, 2, 80000, 125, 'physical', 2, '[player] summons all their power into a burst of astounding speed. Several afterimages appear in [gender2] wake, and as [player] moves back and forth, [opponent] has no way to tell which is the real one, which leaves them utterly unprepared for the high-speed blow [player] delivers.', 'A hyper-speed technique that uses blurred afterimages to confuse the opponent.', 'genjutsu_nerf', 10, 1),
(82, 'Phantom Palm', 'taijutsu', 4, 4.4, '100-41', 'None', 0, 2, 80000, 125, 'physical', 1, '[player]&#039;s movements flow together in an entrancing combination. Weaving around [opponent]&#039;s movements, [player] lands a single gentle palm strike directly on [opponent]&#039;s core. The blow seems harmless until [opponent] spasms with pain, coughing blood from their internal injuries.', 'A vicious attack designed to do internal damage.', 'residual_damage', 10, 2),
(83, 'Butcher&#039;s Lure', 'taijutsu', 4, 4.5, '100-42', 'None', 0, 2, 80000, 125, 'physical', 2, '[player] detonates a bomb that sends countless barbed kunai into the air. [opponent] is struck by several, and realises too late the kunai are attached to wires that lead back to [player]. With a single pull and a shower of blood, the kunai are brutally ripped free from [opponent].', 'A deadly attack using barbed kunai on wires to cause incredible damage.', 'none', NULL, NULL),
(84, 'Endless Barrage', 'taijutsu', 4, 4.1, '100-19', 'None', 0, 2, 54000, 100, 'physical', 0, '[player] corners [opponent] near a large boulder, before striking them which such force they bounce off it on impact. Each time [opponent] falls forward, [player] unleashes a new blow, building in strength and speed to impossible levels until at last [opponent] crashes clean through the stone.', 'A barrage of devastating blows.', 'none', NULL, NULL),
(85, 'Wisdom of the Ancients', 'genjutsu', 4, 4.5, '2-3-1-5-8-9', 'None', 0, 2, 80000, 125, 'projectile', 2, '[player] forms a single handseal as a faint mist wreathes the air around them. Breathing it in, [opponent] is caught aback by its sickly sweet smell. Their balance falters and for a moment they feel as though they are falling, then in an instant the whole sum of mortal knowledge is imparted to them. [opponent] falls to the ground twitching as blood leaks from their ears.', 'A frightening genjutsu that overwhelms the opponent&#039;s mind with knowledge.', 'residual_damage', 38, 3),
(86, 'The Price of Power', 'genjutsu', 4, 4.5, '8-7-9-1-3-2', 'None', 0, 2, 80000, 125, 'projectile', 1, '[player] creates an illusion of some innocent bystanders who are caught up in one of [opponent]&#039;s attacks. In the smallest moment they feel the tiniest pang of guilt, and [player] causes that to grow. Each act they have committed comes back to haunt them, each friend they have lost appears before them. It&#039;s all too much, and the weight of their crimes crushes [opponent]&#039;s will to fight entirely.', 'A cunning genjutsu that turns the smallest hint of guilt into devastating weakness.', 'residual_damage', 100, 1),
(87, 'Clone Combo', 'ninjutsu', 1, 1.2, '2-7', 'None', 0, 2, 175, 10, 'projectile', 0, '[player] forms a handseal which causes two identical copies of themselves to appear besides them. Running together in a zig-zag formation, [opponent] cannot tell which is [player] and which is [gender2] clones. As the clones attack, [opponent] can easily overpower them, but in doing so they are left vulnerable to [player]â€™s devastating counter attack.', 'Power in numbers', 'speed_nerf', 10, 2),
(88, 'Untouchable', 'taijutsu', 4, 4.5, '100-30', 'None', 0, 2, 80000, 125, 'buff', 6, '[player] floods chakra through their legs, pumping it into the muscle fibers. The legs swell slightly as the muscles expand to adapt to the increased energy flowing through them.', 'Infusing a large amount of chakra into the muscle fibers of your legs, you can greatly increase your speed of movement in combat.', 'speed_boost', 25, 3),
(89, 'Cast speed no jutsu', 'ninjutsu', 4, 4.5, '2-7-3-12', 'None', 0, 2, 80000, 125, 'buff', 6, '[player] floods chakra into their arms and mind, increasing their coordination and speed of making deft movements with their hands.', 'Infusing a large amount of chakra into your arms and mind, you can speed up your ability to form handseals.', 'cast_speed_boost', 25, 3);
"
        );

        $this->execute(
            "INSERT INTO `missions` (`mission_id`, `name`, `rank`, `mission_type`, `stages`, `money`) VALUES
            (1, 'Special Request', 1, 1, '[{\"action_type\":\"search\",\"location_radius\":\"2\",\"description\":\"Find a Jounin&#039;s hidden stash of questionable books within [location_radius] squares of the village.\"}]', 40),
            (2, 'Deliver Food', 1, 1, '[{\"action_type\":\"travel\",\"location_radius\":\"4\",\"description\":\"The Kage has sent you to deliver food to a small village in need at [action_data]\"}]', 30),
            (3, 'Retrieve the pet Llama!', 1, 1, '[{\"action_type\":\"travel\",\"location_radius\":\"4\",\"description\":\"Retrieve the distraught farm owner&#039;s best friend at [action_data], This might require some climbing tools.\"},{\"action_type\":\"travel\",\"location_radius\":\"2\",\"description\":\"After rescuing the Llama from a massive cliff edge and being headbutted by the animal as well you must return him to his owner at [action_data]\"}]', 55),
            (4, 'Form Team & Scout Area', 2, 1, '[{\"action_type\":\"travel\",\"location_radius\":\"1\",\"description\":\"Meet up with the team of scouts you have hand picked yourself at [action_data] outside of the village walls.\"},{\"action_type\":\"search\",\"location_radius\":\"3\",\"count\":\"3\",\"description\":\"Scout the surrounding [location_radius] squares outside your village and immediately report any suspicious activity that you come across. &#039;The Kage has given specific orders to not engage any hostile, this is purely a scouting mission&#039; you tell your team.\"}]', 125),
            (5, 'Teambuilding Exercise', 1, 3, '[{\"action_type\":\"travel\",\"location_radius\":\"1\",\"count\":\"50\",\"description\":\"Nothing builds friendships like mutual suffering. Pick up trash around the village as a team\"}]', 50),
            (6, 'Patrol Village Perimeter', 2, 1, '[{\"action_type\":\"search\",\"location_radius\":\"1\",\"count\":\"5\",\"description\":\"You have been tasked with patrolling the outer wall, ranging [location_radius] square around the village. In doing this you must look for any weaknesses and damages within the wall itself while also alerting to any suspicious activity just beyond the wall to the higher guard.\"}]', 75),
            (7, 'Tactical Espionage', 3, 1, '[{\"action_type\":\"travel\",\"location_radius\":\"2\",\"count\":\"2\",\"description\":\"Travel to [action_data] just outside your village to pick up your equipment for the journey. This will include your unmarked stealth gear, a dossier on your mission and an emergency pouch full of stuff you need if you are caught and captured.. which for your sake I hope you are not.\"},{\"action_type\":\"travel\",\"location_radius\":\"18\",\"count\":\"3\",\"description\":\"One of the places to travel to is located at [action_data]. You should remember your training and be as careful as you can, using every stealth jutsu to your advantage. Do not hesitate to quickly and quietly take down any enemy threat, but only if necessary, leave as little evidence as possible.\"}]', 140),
            (8, 'Study Clan Heritage', 1, 2, '[{\"action_type\":\"travel\",\"location_radius\":\"3\",\"count\":\"2\",\"description\":\"Travel to [action_data] with a seasoned member of your clan to learn more about the abilities and responsibilities that come with being a member of your clan. It may be boring but you may just end up learning a thing or two.\"}]', 50),
            (9, 'Fight Club', 3, 5, '[{\"action_type\":\"travel\",\"location_radius\":\"2\",\"description\":\"Travel to [action_data] to begin the series of fights..\"},{\"action_type\":\"combat\",\"action_data\":\"14\",\"description\":\"Defeat your opponents.\"},{\"action_type\":\"combat\",\"action_data\":\"15\",\"description\":\"Defeat your opponents.\"}]', 150),
            (10, 'Jonin Exam', 2, 4, '[{\"action_type\":\"search\",\"location_radius\":\"4\",\"description\":\"A ninja has escaped from your village, carrying a scroll of secret jutsu. Your task is to hunt down this ninja, apprehend them, and bring them in. Start by searching [location_radius] squares around your village for any signs of the ninja.\"},{\"action_type\":\"travel\",\"location_radius\":\"7\",\"description\":\"You found the shinobi meeting up with a ninja from another village, and learned they are going to rendezvous at [action_data]. Go intercept them and get the scroll back!\"},{\"action_type\":\"combat\",\"action_data\":\"14\",\"description\":\"You made it to the meeting but were intercepted by an enemy Shinobi. Defeat them before the rogue ninja escapes!\"},{\"action_type\":\"combat\",\"action_data\":\"15\",\"description\":\"You&#039;ve found the outlaw! Defeat him and take back the scroll.\"}]', 0),
            (11, 'ANBU Ambush', 4, 5, '[{\"action_type\":\"travel\",\"location_radius\":\"4\",\"description\":\"Retrieve enemy intel at [action_data].\"},{\"action_type\":\"combat\",\"action_data\":\"17\",\"description\":\"An ambush! Try to survive!\"},{\"action_type\":\"combat\",\"action_data\":\"18\",\"description\":\"An ambush! Try to survive!\"}]', 200);
            "
        );

        // Ranks
        $this->execute(
            "INSERT INTO `ranks` (`rank_id`, `name`, `base_level`, `max_level`, `base_stats`, `stats_per_level`, `health_gain`, `pool_gain`, `stat_cap`) VALUES
    (1, 'Akademi-sei', 1, 10, 50, 30, 45, 20, 1000),
(2, 'Genin', 11, 25, 320, 325, 503, 50, 7500),
(3, 'Chuunin', 26, 50, 3900, 1175, 1878, 110, 50000),
(4, 'Jonin', 51, 100, 32000, 2500, 4500, 200, 250000);"
        );

        // Villages
        $this->execute(
            "INSERT INTO `villages` (`village_id`, `name`, `location`, `points`, `leader`) VALUES
            (1, 'Stone', '5.3', 0, 0),
            (2, 'Cloud', '17.2', 0, 0),
            (3, 'Leaf', '9.6', 0, 0),
            (4, 'Sand', '3.8', 0, 0),
            (5, 'Mist', '16.10', 0, 0);"
        );

        // System Storage
    $this->execute(
        "INSERT INTO `system_storage` (`global_message`. `time`) VALUES ('', '" . time() . "');"
    );

        $this->execute("COMMIT;");
    }
}
