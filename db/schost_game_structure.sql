-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 27, 2021 at 07:36 AM
-- Server version: 5.5.65-MariaDB
-- PHP Version: 7.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `schost_game`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_opponents`
--

CREATE TABLE `ai_opponents` (
  `ai_id` int(11) NOT NULL,
  `rank` smallint(6) NOT NULL,
  `money` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `max_health` int(11) NOT NULL,
  `level` smallint(6) NOT NULL,
  `ninjutsu_skill` double(12,2) NOT NULL,
  `genjutsu_skill` double(12,2) NOT NULL,
  `taijutsu_skill` double(12,2) NOT NULL,
  `cast_speed` double(12,2) NOT NULL,
  `speed` double(12,2) NOT NULL,
  `strength` double(12,2) NOT NULL,
  `endurance` double(12,2) NOT NULL,
  `intelligence` double(12,2) NOT NULL,
  `willpower` double(12,2) NOT NULL,
  `moves` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `banned_ips`
--

CREATE TABLE `banned_ips` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(64) DEFAULT NULL,
  `ban_level` tinyint(4) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `battles`
--

CREATE TABLE `battles` (
  `battle_id` int(11) NOT NULL,
  `battle_type` smallint(6) NOT NULL DEFAULT '1',
  `player1` int(11) NOT NULL,
  `player2` int(11) NOT NULL,
  `player1_action` tinyint(4) DEFAULT '0',
  `player2_action` tinyint(4) DEFAULT '0',
  `player1_attack_type` varchar(32) NOT NULL,
  `player2_attack_type` varchar(32) NOT NULL,
  `player1_jutsu_id` int(11) DEFAULT NULL,
  `player2_jutsu_id` int(11) DEFAULT NULL,
  `player1_weapon_id` int(11) NOT NULL,
  `player2_weapon_id` int(11) NOT NULL,
  `player1_battle_text` varchar(500) DEFAULT NULL,
  `player2_battle_text` varchar(500) DEFAULT NULL,
  `battle_text` varchar(1000) NOT NULL,
  `active_effects` text NOT NULL,
  `active_genjutsu` text NOT NULL,
  `jutsu_cooldowns` varchar(500) NOT NULL,
  `player1_jutsu_used` text NOT NULL,
  `player2_jutsu_used` text NOT NULL,
  `turn_time` int(11) DEFAULT NULL,
  `winner` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `blacklist`
--

CREATE TABLE `blacklist` (
  `user_id` int(11) NOT NULL,
  `blocked_ids` text CHARACTER SET latin1 COLLATE latin1_spanish_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bloodlines`
--

CREATE TABLE `bloodlines` (
  `bloodline_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `clan_id` int(11) NOT NULL,
  `village` varchar(24) NOT NULL,
  `rank` varchar(32) NOT NULL,
  `passive_boosts` text NOT NULL,
  `combat_boosts` text NOT NULL,
  `jutsu` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `chat`
--

CREATE TABLE `chat` (
  `post_id` int(11) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `message` varchar(550) NOT NULL,
  `title` varchar(50) DEFAULT NULL,
  `village` varchar(50) DEFAULT NULL,
  `time` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `staff_level` smallint(6) NOT NULL DEFAULT '0',
  `user_color` smallint(6) NOT NULL DEFAULT '0',
  `edited` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `chat_edit_log`
--

CREATE TABLE `chat_edit_log` (
  `edit_id` int(11) NOT NULL,
  `editor_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `old_message` text NOT NULL,
  `new_message` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `clans`
--

CREATE TABLE `clans` (
  `clan_id` int(11) NOT NULL,
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
  `info` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `events_log`
--

CREATE TABLE `events_log` (
  `log_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `stage_id` int(11) NOT NULL,
  `stage_details` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `rank` tinyint(4) NOT NULL,
  `purchase_type` tinyint(4) NOT NULL,
  `purchase_cost` int(11) NOT NULL,
  `use_type` tinyint(4) NOT NULL,
  `effect` varchar(50) NOT NULL,
  `effect_amount` float NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `journals`
--

CREATE TABLE `journals` (
  `user_id` int(11) NOT NULL,
  `journal` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `jutsu`
--

CREATE TABLE `jutsu` (
  `jutsu_id` int(11) NOT NULL,
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
  `effect_length` tinyint(4) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `log_id` int(11) NOT NULL,
  `log_type` varchar(64) DEFAULT NULL,
  `log_title` varchar(100) DEFAULT NULL,
  `log_time` int(11) DEFAULT NULL,
  `log_contents` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `missions`
--

CREATE TABLE `missions` (
  `mission_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `rank` tinyint(4) NOT NULL,
  `mission_type` tinyint(4) NOT NULL,
  `stages` text NOT NULL,
  `money` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `news_posts`
--

CREATE TABLE `news_posts` (
  `post_id` int(11) NOT NULL,
  `sender` varchar(50) NOT NULL,
  `title` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `time` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Payments`
--

CREATE TABLE `Payments` (
  `id` int(11) NOT NULL,
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
  `address_status` varchar(24) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `premium_credit_exchange`
--

CREATE TABLE `premium_credit_exchange` (
  `id` int(11) NOT NULL,
  `seller` varchar(50) NOT NULL,
  `premium_credits` int(11) NOT NULL,
  `money` int(11) NOT NULL,
  `completed` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `private_messages`
--

CREATE TABLE `private_messages` (
  `message_id` int(11) NOT NULL,
  `sender` varchar(50) NOT NULL,
  `recipient` varchar(50) NOT NULL,
  `subject` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `time` int(11) NOT NULL,
  `message_read` tinyint(4) NOT NULL DEFAULT '0',
  `staff_level` smallint(6) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ranks`
--

CREATE TABLE `ranks` (
  `rank_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `base_level` smallint(6) NOT NULL,
  `max_level` smallint(6) NOT NULL,
  `base_stats` int(11) NOT NULL,
  `stats_per_level` int(11) NOT NULL,
  `health_gain` int(11) NOT NULL,
  `pool_gain` smallint(6) NOT NULL,
  `stat_cap` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
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
  `time` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `team_id` int(11) NOT NULL,
  `village` varchar(24) DEFAULT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '1',
  `name` varchar(40) DEFAULT NULL,
  `boost` varchar(64) DEFAULT NULL,
  `boost_amount` float(6,2) DEFAULT NULL,
  `points` int(11) DEFAULT NULL,
  `monthly_points` smallint(6) DEFAULT NULL,
  `leader` int(11) DEFAULT NULL,
  `members` varchar(200) NOT NULL,
  `mission_id` int(11) DEFAULT NULL,
  `mission_stage` text,
  `logo` varchar(200) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `username_log`
--

CREATE TABLE `username_log` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `old_name` varchar(40) NOT NULL,
  `new_name` varchar(40) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(40) NOT NULL,
  `password` varchar(40) NOT NULL,
  `email` varchar(100) NOT NULL,
  `staff_level` smallint(6) NOT NULL DEFAULT '0',
  `health` double(12,2) NOT NULL,
  `max_health` double(12,2) NOT NULL,
  `money` int(11) UNSIGNED NOT NULL,
  `premium_credits` int(11) NOT NULL DEFAULT '0',
  `premium_credits_purchased` int(11) NOT NULL DEFAULT '0',
  `forbidden_seal` varchar(64) NOT NULL,
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
  `cast_speed` double(12,2) NOT NULL,
  `speed` double(12,2) NOT NULL,
  `endurance` double(12,2) NOT NULL,
  `intelligence` double(12,2) NOT NULL,
  `willpower` double(12,2) NOT NULL,
  `mission_id` int(11) NOT NULL DEFAULT '0',
  `mission_stage` text NOT NULL,
  `last_login` int(11) NOT NULL DEFAULT '0',
  `last_update` int(11) NOT NULL DEFAULT '0',
  `last_active` int(11) NOT NULL DEFAULT '0',
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
  `clan_changes` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_bloodlines`
--

CREATE TABLE `user_bloodlines` (
  `user_id` int(11) NOT NULL,
  `bloodline_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `passive_boosts` text NOT NULL,
  `combat_boosts` text NOT NULL,
  `jutsu` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_inventory`
--

CREATE TABLE `user_inventory` (
  `user_id` int(11) NOT NULL,
  `jutsu` text NOT NULL,
  `items` text NOT NULL,
  `bloodline_jutsu` varchar(500) NOT NULL,
  `equipped_jutsu` varchar(500) DEFAULT NULL,
  `equipped_items` varchar(500) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `villages`
--

CREATE TABLE `villages` (
  `village_id` int(11) NOT NULL,
  `name` varchar(32) DEFAULT NULL,
  `location` varchar(8) DEFAULT NULL,
  `points` int(11) DEFAULT '0',
  `leader` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_opponents`
--
ALTER TABLE `ai_opponents`
  ADD PRIMARY KEY (`ai_id`);

--
-- Indexes for table `banned_ips`
--
ALTER TABLE `banned_ips`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `battles`
--
ALTER TABLE `battles`
  ADD PRIMARY KEY (`battle_id`);

--
-- Indexes for table `blacklist`
--
ALTER TABLE `blacklist`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `bloodlines`
--
ALTER TABLE `bloodlines`
  ADD PRIMARY KEY (`bloodline_id`);

--
-- Indexes for table `chat`
--
ALTER TABLE `chat`
  ADD PRIMARY KEY (`post_id`);

--
-- Indexes for table `chat_edit_log`
--
ALTER TABLE `chat_edit_log`
  ADD PRIMARY KEY (`edit_id`);

--
-- Indexes for table `clans`
--
ALTER TABLE `clans`
  ADD PRIMARY KEY (`clan_id`);

--
-- Indexes for table `events_log`
--
ALTER TABLE `events_log`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `journals`
--
ALTER TABLE `journals`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `jutsu`
--
ALTER TABLE `jutsu`
  ADD PRIMARY KEY (`jutsu_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `missions`
--
ALTER TABLE `missions`
  ADD PRIMARY KEY (`mission_id`);

--
-- Indexes for table `news_posts`
--
ALTER TABLE `news_posts`
  ADD PRIMARY KEY (`post_id`);

--
-- Indexes for table `Payments`
--
ALTER TABLE `Payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `premium_credit_exchange`
--
ALTER TABLE `premium_credit_exchange`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `private_messages`
--
ALTER TABLE `private_messages`
  ADD PRIMARY KEY (`message_id`);

--
-- Indexes for table `ranks`
--
ALTER TABLE `ranks`
  ADD PRIMARY KEY (`rank_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`team_id`);

--
-- Indexes for table `username_log`
--
ALTER TABLE `username_log`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_bloodlines`
--
ALTER TABLE `user_bloodlines`
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `user_inventory`
--
ALTER TABLE `user_inventory`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `villages`
--
ALTER TABLE `villages`
  ADD PRIMARY KEY (`village_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_opponents`
--
ALTER TABLE `ai_opponents`
  MODIFY `ai_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `banned_ips`
--
ALTER TABLE `banned_ips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `battles`
--
ALTER TABLE `battles`
  MODIFY `battle_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bloodlines`
--
ALTER TABLE `bloodlines`
  MODIFY `bloodline_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat`
--
ALTER TABLE `chat`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_edit_log`
--
ALTER TABLE `chat_edit_log`
  MODIFY `edit_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clans`
--
ALTER TABLE `clans`
  MODIFY `clan_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events_log`
--
ALTER TABLE `events_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jutsu`
--
ALTER TABLE `jutsu`
  MODIFY `jutsu_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `missions`
--
ALTER TABLE `missions`
  MODIFY `mission_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news_posts`
--
ALTER TABLE `news_posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Payments`
--
ALTER TABLE `Payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `premium_credit_exchange`
--
ALTER TABLE `premium_credit_exchange`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `private_messages`
--
ALTER TABLE `private_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ranks`
--
ALTER TABLE `ranks`
  MODIFY `rank_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `team_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `username_log`
--
ALTER TABLE `username_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `villages`
--
ALTER TABLE `villages`
  MODIFY `village_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
