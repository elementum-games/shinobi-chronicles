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
  `password` varchar(256) NOT NULL,
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

-- Add villages
INSERT INTO `villages` (`village_id`, `name`, `location`, `points`, `leader`) VALUES
(1, 'Stone', '5.3', 0, 0),
(2, 'Cloud', '17.2', 0, 0),
(3, 'Leaf', '9.6', 0, 0),
(4, 'Sand', '3.8', 0, 0),
(5, 'Mist', '16.10', 0, 0);

-- Add ranks
INSERT INTO `ranks` (`rank_id`, `name`, `base_level`, `max_level`, `base_stats`, `stats_per_level`, `health_gain`, `pool_gain`, `stat_cap`) VALUES
(1, 'Akademi-sei', 1, 10, 0, 20, 20, 10, 500),
(2, 'Genin', 11, 20, 250, 100, 125, 40, 4000),
(3, 'Chuunin', 21, 40, 1250, 400, 400, 100, 20000),
(4, 'Jonin', 41, 99, 10000, 1300, 1050, 200, 87500);

-- Add items
INSERT INTO `items` (`item_id`, `name`, `rank`, `purchase_type`, `purchase_cost`, `use_type`, `effect`, `effect_amount`) VALUES
(1, 'Goggles', 1, 1, 500, 2, 'harden', 150),
(2, 'Kusarigama', 3, 1, 15000, 1, 'daze', 20),
(6, 'Kunai', 1, 1, 700, 1, 'residual_damage', 15),
(3, 'Incredibly Long Scarf', 1, 1, 500, 2, 'harden', 150),
(4, 'Healing Salve', 3, 1, 400, 3, 'heal', 3000),
(5, 'Katana', 3, 1, 22500, 1, 'residual_damage', 25),
(7, 'Ninjato', 2, 1, 3000, 1, 'residual_damage', 20),
(8, 'Bo Staff', 2, 1, 1800, 1, 'daze', 5),
(9, 'Mesh Armor', 2, 1, 2200, 2, 'harden', 400),
(10, 'Full Body Suit', 2, 1, 4000, 2, 'lighten', 5),
(11, 'Large Hexagonal Shuriken', 3, 1, 10000, 1, 'cripple', 14),
(12, 'Standard Flak Jacket', 3, 1, 7500, 2, 'harden', 1000),
(13, 'Covert Attire', 3, 1, 16000, 2, 'lighten', 15),
(14, 'Wound Disinfectant Spray', 2, 1, 150, 3, 'heal', 850),
(15, 'Ancient Fan', 3, 1, 50000, 1, 'diffuse', 15),
(16, 'Chakra Blade', 4, 1, 50000, 1, 'element', 10);

-- Add bloodlines
INSERT INTO `bloodlines` (`bloodline_id`, `name`, `clan_id`, `village`, `rank`, `passive_boosts`, `combat_boosts`, `jutsu`) VALUES
(1, 'Shadow Manipulator', 1, 'Leaf', '2', '[{\"power\":\"10\",\"effect\":\"stealth\"}]', '[{\"power\":\"5\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"10\",\"effect\":\"genjutsu_boost\"}]', '[{\"name\":\"Shadow Spear\",\"rank\":\"2\",\"power\":\"1.8\",\"hand_seals\":\"120\",\"element\":\"none\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"An ability that turns the caster&#039;s shadow into needle like shape to stab oncoming enemies.\",\"battle_text\":\"[player] stretches [gender2] shadow in [opponent]&#039;s direction before the shadow jumps from the ground and forms into a pitch black spike that pierces [opponent].\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"30\",\"effect_length\":\"2\"},{\"name\":\"Devouring Darkness\",\"rank\":\"3\",\"power\":\"3.5\",\"hand_seals\":\"121\",\"element\":\"none\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"This Jutsu casts a large shadow over the sun, engulfing everything in darkness.\",\"battle_text\":\"[player] raises [gender2] hand up, palm open, as a menacing shadow covers the sun. [opponent] is bathed in darkness, a darkness that leaves them unable to move their body in the slightest.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"55\",\"effect_length\":\"2\"}]'),
(2, 'Marionette Maniac', 2, 'Sand', '2', '[]', '[{\"power\":\"20\",\"effect\":\"taijutsu_boost\"},{\"power\":\"10\",\"effect\":\"ninjutsu_resist\"}]', '[{\"name\":\"Dancing Strings\",\"rank\":\"2\",\"power\":\"2.3\",\"hand_seals\":\"15\",\"element\":\"none\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Using two marionettes against one opponent seems fair, especially when those marionettes use hidden weapons.\",\"battle_text\":\"Sitting high above on a branch sticking out from a tall tree, [player] moves [gender2] fingers around, watching in joy as [opponent] hopelessly fends off two bladed Marionettes that slash repeatedly at [opponent].\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"speed_nerf\",\"effect_amount\":\"15\",\"effect_length\":\"2\"},{\"name\":\"Circle of Flames\",\"rank\":\"3\",\"power\":\"3.4\",\"hand_seals\":\"16\",\"element\":\"none\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"A devastating attack that requires the expertise of controlling many marionettes at once.\",\"battle_text\":\"Many marionettes all controlled by the same line of chakra quickly surround [opponent] in a circular shape. All at once they ignite the oil in their mouths and spew out massive amounts of flames that cover the area.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"10\",\"effect_length\":\"2\"}]'),
(3, 'Fiendish Whispers', 3, 'Mist', '2', '[{\"power\":\"5\",\"effect\":\"regen\"},{\"power\":\"10\",\"effect\":\"scout_range\"}]', '[{\"power\":\"15\",\"effect\":\"genjutsu_boost\"}]', '[{\"name\":\"Hypnotizing Melody\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"12\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"A bewitching technique that can overtake a enemy&#039;s free will and cause them to inflict self harm.\",\"battle_text\":\"Using the Haunting melody that comes from [player]&#039;s flute, [gender2] is able to send [opponent] in a trance like state where they purposefully harm themselves.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"55\",\"effect_length\":\"2\"},{\"name\":\"Last Lullaby\",\"rank\":\"3\",\"power\":\"3.5\",\"hand_seals\":\"104\",\"element\":\"none\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"Deadly and beautiful in its own right, anyone caught hearing this tune will meet a cruel end.\",\"battle_text\":\"[player] begins to play a slow and beautiful lullaby, one that is unknown to most. [opponent] drops to their knees, feeling the sensation that their own soul and life force is being drained from their body as they slowly slip into a deep slumber.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"40\",\"effect_length\":\"3\"}]'),
(4, 'Excess Chakra', 12, 'Leaf', '4', '[{\"power\":\"10\",\"effect\":\"regen\"}]', '[{\"power\":\"10\",\"effect\":\"ninjutsu_boost\"}]', '[{\"name\":\"Chakra Battle Gear\",\"rank\":\"2\",\"power\":\"2.3\",\"hand_seals\":\"155\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"With so much extra chakra in ones system they can mold it and form it into amazing weapons and even armor.\",\"battle_text\":\"[player] coats [gender2] body in chakra like armor before forming a crude blade out of chakra, with this [gender] rushes forward. [gender] slashes and cuts [opponent] as if they were butter, stealing their chakra.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"absorb_chakra\",\"effect_amount\":\"10\",\"effect_length\":\"2\"}]'),
(5, 'Morphing Limbs', 13, 'Stone', '4', '[]', '[{\"power\":\"15\",\"effect\":\"taijutsu_boost\"},{\"power\":\"5\",\"effect\":\"taijutsu_resist\"}]', '[{\"name\":\"Ceramic Hammer\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"111\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Having limbs that can change shape can come in handy sometimes.\",\"battle_text\":\"[player] shapes and forms [gender2] hand like wet clay into a hammer shape before [gender2] Hand solidifies, with this, [player] smashes [opponent] over the head with great force.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"none\",\"effect_amount\":\"12\",\"effect_length\":\"2\"}]'),
(6, 'Desert Wanderer', 14, 'Sand', '4', '[]', '[{\"power\":\"15\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"5\",\"effect\":\"cast_speed_boost\"}]', '[{\"name\":\"Red Sands\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"112\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Sand that can blind your enemy while getting your point across as well with.. Explosions.\",\"battle_text\":\"[player] throws [gender2] arms up as a massive red sandstorm surrounds [opponent]. Before [opponent] can escape the haze they are caught up in a massive explosion from the sand being mixed with gunpowder.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"3\",\"effect\":\"none\"}]'),
(7, 'Electric Beast', 15, 'Cloud', '4', '[]', '[{\"power\":\"10\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"10\",\"effect\":\"heal\"}]', '[{\"name\":\"Static Discharge\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"113\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"small sparks of energy circulate around the body that can be built up and discharged.\",\"battle_text\":\"[player] builds up more momentum before charging at [opponent] in a rage and slamming a potent fist strike to [opponent]&#039;s gut sending waves of electricity out.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"none\",\"effect_amount\":\"8\",\"effect_length\":\"3\"}]'),
(8, 'Rejuvenating Waters', 3, 'Mist', '3', '[{\"power\":\"10\",\"effect\":\"regen\"}]', '[{\"power\":\"15\",\"effect\":\"heal\"}]', '[{\"name\":\"Tainted Spring\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"114\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"What looks like an attempt to summon forth a creature or object is just a ruse to trick someone into not suspecting it as genjutsu.\",\"battle_text\":\" [player] places both hands firmly on the ground and summons forth a water spring, but unlike others this springs water is black and thick, eating away at [opponent]&#039;s flesh as they sink further in.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"40\",\"effect_length\":\"3\"}]'),
(9, 'Fiery Passion', 4, 'Leaf', '3', '[]', '[{\"power\":\"15\",\"effect\":\"taijutsu_boost\"},{\"power\":\"10\",\"effect\":\"speed_boost\"}]', '[{\"name\":\"Furious Hurricane\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"17\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Fury and determination fuels the power of this deadly combo.\",\"battle_text\":\"Burning with a deep desire to prove their power, [player] goes in a frenzy and kicks [opponent] several times moving in a tornado like pattern before delivering a final kick, sending [opponent] off into the sunset.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"}]'),
(10, 'Sensory Destroyer', 5, 'Cloud', '3', '[{\"power\":\"10\",\"effect\":\"scout_range\"}]', '[{\"power\":\"15\",\"effect\":\"genjutsu_boost\"}]', '[{\"name\":\"Internal Shock\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"18\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"A genjutsu that fools someone into thinking all of their bodily systems were shut down.\",\"battle_text\":\"[player] seemingly sends an electric shock though [opponent]&#039;s body which shuts down all their senses and major organs, Leaving [opponent] in a sate of extreme panic when they snap from the Genjutsu.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"55\",\"effect_length\":\"2\"}]'),
(11, 'Earthly Minerals', 6, 'Stone', '3', '[]', '[{\"power\":\"15\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"10\",\"effect\":\"cast_speed_boost\"}]', '[{\"name\":\"Gravel Pit\",\"rank\":\"2\",\"power\":\"2.3\",\"hand_seals\":\"19\",\"element\":\"Earth\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Shifting the earth topsoil, one can cause an enemy to sink into the ground.. permanently.\",\"battle_text\":\"[player] places both hands on the ground as earth begins to shift and a giant whirlpool of gravel sucks [opponent] down into the abyss below. The earth then hardens again, becoming what it originally was.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"speed_nerf\",\"effect_amount\":\"10\",\"effect_length\":\"2\"}]'),
(12, 'Beast Tamer', 15, 'Cloud', '2', '[{\"power\":\"10\",\"effect\":\"scout_range\"}]', '[{\"power\":\"15\",\"effect\":\"taijutsu_resist\"},{\"power\":\"15\",\"effect\":\"speed_boost\"}]', '[{\"name\":\"Beastly Strike\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"122\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"A multiple angled strike from a multitude of animals.\",\"battle_text\":\"Giving no warming whatsoever several vicious wolves jump out from hidden spots in the area, striking [opponent] from many different angles at once while also aiming for vital areas of [opponent]&#039;s body.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"none\",\"effect_amount\":\"10\",\"effect_length\":\"3\"},{\"name\":\"Menacing Alpha\",\"rank\":\"3\",\"power\":\"3.5\",\"hand_seals\":\"123\",\"element\":\"none\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"Summons forth a demonic looking wolf which helps greatly in battle.\",\"battle_text\":\"[player] places both palms on the ground after making a summoning circle. [player] summons forth a large, crimson colored wolf. Embers seep from its mouth as small black ribbons draped around its body flutter in the air. The wolf charges at [gender2] opponent and rends their flesh.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"}]'),
(13, 'Ritual Winds', 14, 'Sand', '3', '[]', '[{\"power\":\"15\",\"effect\":\"genjutsu_boost\"},{\"power\":\"10\",\"effect\":\"ninjutsu_resist\"}]', '[{\"name\":\"Hypnotic Dance\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"4.3\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"A jutsu using the power of dance and two small handheld fans to cast a spell over the opponent\",\"battle_text\":\"[Player] performs a ritualistic type dance with two small fans, [opponent] can not stray their eyes from the hypnotic dance that soon sends them into a false sense of security, one that slowly drains their will to fight.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"55\",\"effect_length\":\"2\"}]'),
(14, 'Liquid of Power', 17, 'Mist', '3', '[]', '[{\"power\":\"20\",\"effect\":\"taijutsu_boost\"},{\"power\":\"5\",\"effect\":\"heal\"}]', '[{\"name\":\"Refreshing Waters\",\"rank\":\"2\",\"power\":\"2.3\",\"hand_seals\":\"27\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"With one sip of this special water it can make a person Insanely powerful for a few moments.\",\"battle_text\":\"[player] reaches in [gender2] pouch and pulls out a gourd of water before guzzling some down, at that moment, [player] moves at insane speeds and sends a bone shattering punch to [opponent]&#039;s gut.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"10\",\"effect_length\":\"2\"}]'),
(15, 'Detonating Masterpiece', 6, 'Stone', '2', '[{\"power\":\"10\",\"effect\":\"scout_range\"}]', '[{\"power\":\"20\",\"effect\":\"ninjutsu_boost\"}]', '[{\"name\":\"Sneaking Serpent\",\"rank\":\"2\",\"power\":\"2.3\",\"hand_seals\":\"30\",\"element\":\"none\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"A sneak attack to opponents blind spot with deadly results.\",\"battle_text\":\"As [player] distracts [opponent] by firing a barrage Clay shuriken a snake made out of clay and high powered explosives slithers underground. The snake pops up behind [opponent] and wraps around their leg right before self destructing.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"speed_nerf\",\"effect_amount\":\"10\",\"effect_length\":\"2\"},{\"name\":\"Scorched Landscape\",\"rank\":\"3\",\"power\":\"2.9\",\"hand_seals\":\"31\",\"element\":\"none\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"An explosion on catastrophic proportions that leaves the landscape devastated.\",\"battle_text\":\"[player] seeps his power into the ground below, filling it with many small explosives. After luring [opponent] in the trap, [player] makes a quick escape out of the area before triggering all the explosives at once, causing an earth shattering explosion.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"22\",\"effect_length\":\"2\"}]'),
(16, 'Demonic Weaponry', 22, 'Mist', '1', '[{\"power\":\"10\",\"effect\":\"regen\"}]', '[{\"power\":\"20\",\"effect\":\"taijutsu_boost\"},{\"power\":\"5\",\"effect\":\"heal\"}]', '[{\"name\":\"Psychotic Slash\",\"rank\":\"2\",\"power\":\"2.4\",\"hand_seals\":\"200\",\"element\":\"none\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"The past of the Bloodmist Exams re-enters the mind, causing a mass of overjoyed emotions and adrenaline to enter the body.\",\"battle_text\":\"[player] grins sadistically, a smile only shown when [gender] has remembered the events that occurred long ago when graduating the academy bathed in blood. With one swift swing [player] brings [gender2] great broadsword blade downward across [opponent]&#039;s chest attempting to split them in half.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"10\",\"effect_length\":\"1\"},{\"name\":\"Threading needle\",\"rank\":\"3\",\"power\":\"2.7\",\"hand_seals\":\"207\",\"element\":\"none\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"When just plain crazy is not enough, you have to go full on psycho and so do your weapons.\",\"battle_text\":\"Without warning [player] strikes [opponent] several times with a small needle like blade, a thread attached to its hilt. Every section of the thread drips blood from passing though [opponent]&#039;s body multiple times, [player] tugs [gender2] blade as he watches [opponent] scream in agony.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"20\",\"effect_length\":\"2\"},{\"name\":\"Rebirth of blood\",\"rank\":\"4\",\"power\":\"4.5\",\"hand_seals\":\"208\",\"element\":\"none\",\"cooldown\":\"2\",\"purchase_cost\":\"10000\",\"use_cost\":\"300\",\"description\":\"A deadly dance of swords that will leave the enemy most likely in pieces\",\"battle_text\":\"[player] disappears into a thick mist that surrounds the area. [opponent], though prepared, becomes on edge sensing [player] nowhere, Just then blood sprays and spreads from [opponent]&#039;s body as they collapse, the mist, now forms back into a human figure that is [player] holding a blood soaked blade.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"}]'),
(18, 'Weeping Skies', 24, 'Cloud', '1', '[]', '[{\"power\":\"30\",\"effect\":\"taijutsu_boost\"},{\"power\":\"10\",\"effect\":\"speed_boost\"},{\"power\":\"10\",\"effect\":\"ninjutsu_resist\"}]', '[{\"name\":\"Supersonic Fist\",\"rank\":\"2\",\"power\":\"2.3\",\"hand_seals\":\"202\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"A skill that only uses one singular strike to an enemies chest but devastates their body in the process.\",\"battle_text\":\"[player] build up massive amounts of energy in a singular fist before running at speeds no ordinary human could survive and slams [gender2] now open palm against [opponent]&#039;s chest, causing insurmountable internal damage.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"drain_chakra\",\"effect_amount\":\"10\",\"effect_length\":\"2\"},{\"name\":\"Trailing Thunder\",\"rank\":\"3\",\"power\":\"3.5\",\"hand_seals\":\"205\",\"element\":\"Lightning\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"With fast paced speed even sound can not keep up with this combo of strikes.\",\"battle_text\":\"[player] lets massive amounts of electricity surge throughout [gender2] body, once at its peek [player] begins to encircle [opponent]. when [opponent] attempts to block it is obvious the attack is over as fast as it began, one hundred strikes in under one second.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"none\",\"effect_amount\":\"15\",\"effect_length\":\"2\"},{\"name\":\"Reverberating Crash\",\"rank\":\"4\",\"power\":\"4.5\",\"hand_seals\":\"206\",\"element\":\"Lightning\",\"cooldown\":\"2\",\"purchase_cost\":\"10000\",\"use_cost\":\"300\",\"description\":\"The ultimate combo of strength, speed and endurance. Only ninja of advanced caliber would be able to master such a skill.\",\"battle_text\":\"[player] screams out as [gender] shoots into the air at impossible speeds. [opponent] readies himself in vain as the ground all around sparks and rain slowly drops from the clouds above. All is calm for only a millisecond before the ground explodes in a flash of yellow light, [player]&#039;s fist slamming directly into the ground.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"}]'),
(19, 'Gravitational Law', 25, 'Stone', '1', '[{\"power\":\"10\",\"effect\":\"regen\"}]', '[{\"power\":\"20\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"5\",\"effect\":\"cast_speed_boost\"}]', '[{\"name\":\"Crushing Vortex\",\"rank\":\"2\",\"power\":\"2.1\",\"hand_seals\":\"203\",\"element\":\"none\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"while in control of the areas gravitational pull, one can create a never ending gravity feud.\",\"battle_text\":\" [player] holds one hand out, removing the gravity from the area, making many objects and [opponent] float in the air. [player] then closes [gender2] palm, intensifying the gravity and crushing [opponent] in a huge orb of debris.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"speed_nerf\",\"effect_amount\":\"15\",\"effect_length\":\"2\"},{\"name\":\"Gravity Shift\",\"rank\":\"3\",\"power\":\"2.9\",\"hand_seals\":\"311\",\"element\":\"none\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"Shifts between heavy gravity and no gravity at all, completely neutralizing any free will the enemy possessed.\",\"battle_text\":\"[player] removes any presence of gravity, letting [player] easily dodge [opponent]&#039;s attack before kicking them as a counter strike, sending them barreling in the air. Once high enough [player] shifts the gravity to a bone crushing degree, sending opponent crashing into the ground. [player] keeps intensifying the gravity while hearing [opponent]&#039;s screams of pain.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"10\",\"effect_length\":\"3\"},{\"name\":\"Fiery Impact\",\"rank\":\"4\",\"power\":\"3.5\",\"hand_seals\":\"306\",\"element\":\"none\",\"cooldown\":\"2\",\"purchase_cost\":\"10000\",\"use_cost\":\"300\",\"description\":\"With massive amounts of focus a deadly house sized meteor can be summoned and pulled down into the atmosphere.\",\"battle_text\":\"[player] holds both hands up to the sky, using [gender2] gravity altering powers to pull a meteor the size of a house down into the atmosphere as it begins to burn brightly, the meteor, with great speed crashes into the ground causing a massive explosion that devastates the area and [opponent] while also leaving the landscape engulfed in flames.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"20\",\"effect_length\":\"3\"}]'),
(20, 'Smoldering Sands', 26, 'Sand', '1', '[{\"power\":\"10\",\"effect\":\"stealth\"}]', '[{\"power\":\"15\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"5\",\"effect\":\"taijutsu_resist\"},{\"power\":\"5\",\"effect\":\"ninjutsu_resist\"}]', '[{\"name\":\"Shores of Flames\",\"rank\":\"2\",\"power\":\"2.1\",\"hand_seals\":\"204\",\"element\":\"none\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"The enemy is buried under layer after layer of hot, scolding sand.\",\"battle_text\":\"[opponent] moves to strike [player], but as [opponent] does they quickly realize they are stuck within a sand trap. With one quick motion [player] launches waves of sand, extremely hot to the touch, that soon buries [opponent] alive.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"speed_nerf\",\"effect_amount\":\"10\",\"effect_length\":\"2\"},{\"name\":\"Devouring Dunes\",\"rank\":\"3\",\"power\":\"3.1\",\"hand_seals\":\"211\",\"element\":\"none\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"Evaporates many vital liquids and minerals within the enemy&#039;s body, completely immobilizing them and making them easy prey for creatures lurking beneath the sand.\",\"battle_text\":\"[player] intensifies the heat in [opponent]&#039;s body to such a degree that they become completely dehydrated, stepping toward his now paralyzed foe, [player] picks up [opponent] by the arm before tossing the rag doll like form of [opponent] into the jaws of a giant sand worm like creature.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"drain_chakra\",\"effect_amount\":\"20\",\"effect_length\":\"1\"},{\"name\":\"Sand Cyclone\",\"rank\":\"4\",\"power\":\"4.5\",\"hand_seals\":\"212\",\"element\":\"Wind\",\"cooldown\":\"2\",\"purchase_cost\":\"10000\",\"use_cost\":\"300\",\"description\":\"using sand powers as well as wind powers a huge storm, causing several sand tornados can arise.\",\"battle_text\":\"[player] slams both of [gender2] palms together as sand begins to slowly flutter around the area. Within an instant the winds pick up pace and the sand generates humongous sand tornados that [opponent] tries to avoid but has no success in, [opponent] is quickly torn to shreds by all of the tornados joining in a central area.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"none\",\"effect_amount\":\"15\",\"effect_length\":\"3\"}]'),
(26, ' Malevolent Tattoo', 11, 'Mist', '4', '[{\"power\":\"5\",\"effect\":\"regen\"}]', '[{\"power\":\"15\",\"effect\":\"ninjutsu_boost\"}]', '[{\"name\":\"Peeked Power\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"305\",\"element\":\"none\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"The tattoo on the body slowly stores up chakra, once full, a powerful blast can be unleashed.\",\"battle_text\":\"[player] waits and bides their time as [gender2] tattoo slowly starts to fill with crimson energy, once it has reached it&#039;s peek, [player] holds out [gender2] palms and fires a huge wave of chakra energy aimed at [opponent].\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"}]'),
(21, 'Cursed waters', 11, 'Mist', '3', '[]', '[{\"power\":\"10\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"10\",\"effect\":\"cast_speed_boost\"},{\"power\":\"5\",\"effect\":\"ninjutsu_resist\"}]', '[{\"name\":\"Waiting Abyss\",\"rank\":\"2\",\"power\":\"2.1\",\"hand_seals\":\"300\",\"element\":\"none\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"A jutsu that pulls the enemy down into a deep, seemingly endless body of water.\",\"battle_text\":\"[player] floods a small area with water that [opponent] easily dodges, but when [player] and [opponent] meet atop the flooded water plain, two horrify hands reach from the water and grab [opponent]&#039;s ankles, dragging them down into the depths.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"speed_nerf\",\"effect_amount\":\"15\",\"effect_length\":\"2\"}]'),
(22, 'Hidden Weapons', 13, 'Stone', '3', '[{\"power\":\"10\",\"effect\":\"scout_range\"}]', '[{\"power\":\"15\",\"effect\":\"taijutsu_boost\"}]', '[{\"name\":\"Scenery Deception\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"301\",\"element\":\"none\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Cleverly hides weapons as trees, bushes, stones and more. All in a ready position to fire when released.\",\"battle_text\":\"A multitude of Stone boulders and small trees begin to fade, replaced by the hurling speed of hundreds upon hundreds of shuriken, kunai and large scaled shuriken, which trap [opponent] with no where to run.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"none\",\"effect_amount\":\"15\",\"effect_length\":\"2\"}]'),
(23, 'Destructive Rage', 20, 'Cloud', '3', '[]', '[{\"power\":\"25\",\"effect\":\"taijutsu_boost\"}]', '[{\"name\":\"Burning Hate\",\"rank\":\"2\",\"power\":\"2.4\",\"hand_seals\":\"302\",\"element\":\"none\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"With great rage, speed and power is greatly increased to deal major damage.\",\"battle_text\":\"Filled with immense hate and blinding rage from events in [gender2] life, [player] gains momentous speed and strength, letting it show as [gender] burrows many punches and kicks in [opponent]&#039;s body, leaving them writhing in pain.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"10\",\"effect_length\":\"1\"}]'),
(49, 'Captain&#039;s Boot', 31, 'Mist', '5', '[]', '[{\"power\":\"25\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"5\",\"effect\":\"cast_speed_boost\"},{\"power\":\"5\",\"effect\":\"heal\"}]', '[{\"name\":\"Getting The Boot\",\"rank\":\"4\",\"power\":\"4.2\",\"hand_seals\":\"666\",\"element\":\"Lightning\",\"use_cost\":\"250\",\"description\":\"How far will the boot fit?\",\"battle_text\":\"[player] locks [gender2] boot back into a 90 degree position, pausing for a moment while chakra builds up surrounding the boot. [player] launches [gender2] boot forward into the anus of [opponent] causing them to be severely staggered. The mass of force causes the tearing of flesh and bone. [opponent] suffers from tremendous bleeding.\",\"use_type\":\"physical\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"25\",\"effect_length\":\"2\"}]'),
(24, 'Tranquil Meadow', 1, 'Leaf', '3', '[{\"power\":\"10\",\"effect\":\"regen\"}]', '[{\"power\":\"15\",\"effect\":\"genjutsu_boost\"}]', '[{\"name\":\"Withering life force\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"303\",\"element\":\"none\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"A deadly pollen seeps around in the air that does not effect the user of the jutsu but it does effect the enemy to a fatal degree.\",\"battle_text\":\"A sweet scent lingers in the air of the created meadow as [opponent] and [player] continue their fight. Within a short while [opponent] becomes dizzy, barely able to stand upon their feet, their vision blurs as the poison the field had created begins to take effect.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"55\",\"effect_length\":\"2\"}]'),
(25, 'Serpent&#039;s Fang', 18, 'Sand', '3', '[{\"power\":\"5\",\"effect\":\"regen\"}]', '[{\"power\":\"15\",\"effect\":\"taijutsu_boost\"},{\"power\":\"5\",\"effect\":\"ninjutsu_resist\"}]', '[{\"name\":\"Chains of Deception\",\"rank\":\"2\",\"power\":\"2.3\",\"hand_seals\":\"304\",\"element\":\"none\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"With the various chains buried under the ground, weapons can easily be moved around the area.\",\"battle_text\":\"Underneath the ground a system of chains move continuously like snakes. Once every now and then a chain will jump up from under the ground, a kama attached to its end which strikes [opponent] before lunging underground again, this process continues for several minutes.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"10\",\"effect_length\":\"2\"}]'),
(27, 'Elemental Neutralizer', 6, 'Stone', '4', '[]', '[{\"power\":\"15\",\"effect\":\"ninjutsu_resist\"},{\"power\":\"5\",\"effect\":\"heal\"}]', '[{\"name\":\"Potent Seal\",\"rank\":\"2\",\"power\":\"2.0\",\"hand_seals\":\"307\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"A Jutsu that places a seal on an enemy&#039;s body that will severely weaken their Jutsu.\",\"battle_text\":\"Pressing [gender2] index and middle finger on [opponent]&#039;s shoulder when they get close, [player] forms a crescent like seal on said shoulder. The seal makes it hard for [opponent] to cast Ninjutsu as well as sending pain shooting though their body.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"ninjutsu_nerf\",\"effect_amount\":\"20\",\"effect_length\":\"2\"}]'),
(28, 'Heightened Reflexes', 4, 'Leaf', '4', '[]', '[{\"power\":\"20\",\"effect\":\"taijutsu_boost\"},{\"power\":\"5\",\"effect\":\"ninjutsu_resist\"}]', '[{\"name\":\"Timed Blows\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"308\",\"element\":\"none\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Moving the body in such a way that attacks are quickly avoided and a solid counterattack is given.\",\"battle_text\":\"[player] ducks and dodges the strike and blows that [opponent] tries to land. [player] bends [gender2] body in a multitude of inhuman like ways while also delivering a few perfectly timed strikes of [gender2] own.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"none\",\"effect_amount\":\"10\",\"effect_length\":\"2\"}]'),
(29, 'Sonata of Souls', 5, 'Cloud', '4', '[]', '[{\"power\":\"15\",\"effect\":\"genjutsu_boost\"},{\"power\":\"5\",\"effect\":\"ninjutsu_resist\"}]', '[{\"name\":\"Illusion&#039;s Trick\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"309\",\"element\":\"none\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"A well thought out counter measure against the enemy&#039;s moves that lead to their downfall.\",\"battle_text\":\"When [opponent] launches a form of attack at [player], [gender] quickly retaliates by using a special jutsu that absorbs [opponent]&#039;s attack. expecting the attack to be sent back, [opponent] prepares themselves, but is only met by a realization that they were the one absorbed.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"55\",\"effect_length\":\"2\"}]'),
(30, 'Illusionary Madness', 10, 'Sand', '4', '[]', '[{\"power\":\"15\",\"effect\":\"genjutsu_boost\"},{\"power\":\"5\",\"effect\":\"genjutsu_resist\"}]', '[{\"name\":\"Illusionary Madness\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"310\",\"element\":\"none\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"An endless desert that is also filled with bodies of fallen and skeletons to destroy enemies morale.\",\"battle_text\":\"Quickly and before his [opponent] has time to react, [player] creates a vast desert, one of which [opponent] feels obligated to walk though even though the sites of other collapsed bodies prove otherwise. [opponent] continues on, exhausting their body all the more.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"taijutsu_nerf\",\"effect_amount\":\"50\",\"effect_length\":\"2\"}]'),
(47, 'Eyes of God', 33, 'Cloud', '1', '[{\"power\":\"10\",\"effect\":\"scout_range\"}]', '[{\"power\":\"15\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"10\",\"effect\":\"genjutsu_resist\"}]', '[{\"name\":\"Molecular Disintegration\",\"rank\":\"2\",\"power\":\"2.1\",\"hand_seals\":\"529\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Control the flow of your power and transfer it to your palms, giving you the power to dissolve the very earth itself with one flick of a wrist.\",\"battle_text\":\"Without a moments notice [player] turns [gender2] attention to [opponent], while [opponent] tries their best to dodge, they are no match for [player]&#039;s eyes and is struck both on the arm and leg with [player]&#039;s open palm, causing muscles, cells and various tissue to break down or explode, causing immense anguish.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"10\",\"effect_length\":\"2\"},{\"name\":\"Divine Beasts\",\"rank\":\"3\",\"power\":\"3.5\",\"hand_seals\":\"530\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"Release the two god like beasts that lay dormant inside your eyes, they are crying out to their master so they can hunt and have some fun.\",\"battle_text\":\"Two enormous beasts are summoned by [player] from [gender2] eyes. One beast has the appearance of a hyena, many blue flames circling its body that represent seals, the other is a gigantic ferret, clad in crimson armor and a sickle for a tail. Both of these beast together ripe, tear and obliterate [opponent] using any means necessary before a giant aqua blue Chakra sphere engulfs the entire area and implodes devouring all the landscape it touches.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"},{\"name\":\"God&#039;s Awakening\",\"rank\":\"4\",\"power\":\"4.0\",\"hand_seals\":\"531\",\"element\":\"None\",\"cooldown\":\"3\",\"purchase_cost\":\"10000\",\"use_cost\":\"350\",\"description\":\"Channeling chakra to your eyes to awaken into the ultimate form of dojutsu. Once awakened, Sui Riu, a dragon of rain decends from the heavens.\",\"battle_text\":\"Having awakened the true form of the Kamigan, [player] chants a verse in an ancient language, summoning Sui Riu the rain dragon. A torrent of blood rain suddenly washes away [opponent]&#039;s attacks. [player] channels [gender2] chakra through the ancient beast, readying for the final offense. A volley of lightning bolts erupt from the dragon&#039;s mouth vaporising anyone within range.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"ninjutsu_boost\",\"effect_amount\":\"15\",\"effect_length\":\"3\"}]'),
(31, 'Kage of Adorableness', 27, 'Mist', '5', '[{\"power\":\"500\",\"effect\":\"regen\"},{\"power\":\"200\",\"effect\":\"scout_range\"},{\"power\":\"1\",\"effect\":\"stealth\"}]', '[{\"power\":\"500\",\"effect\":\"ninjutsu_resist\"},{\"power\":\"500\",\"effect\":\"genjutsu_resist\"},{\"power\":\"500\",\"effect\":\"taijutsu_resist\"}]', '[{\"name\":\"Banana of Doom\",\"rank\":\"2\",\"power\":2.5,\"hand_seals\":\"9000\",\"element\":\"Cute\",\"purchase_cost\":\"10000\",\"use_cost\":30,\"description\":\"Their death shall be a slippery one.....\",\"battle_text\":\"[player] pulls out a blade in the shape and color of a banana, leaving [opponent] puzzled. Just then LenKagamine rushes into battle, takes the blade and tag teams with [player] and [gender2] chainsaw to slice [opponent] to ribbons.... before they then explode into bunnies...\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"100\",\"effect_length\":\"8\"},{\"name\":\"Happy Rainbow Cannon\",\"rank\":\"3\",\"power\":3.5,\"hand_seals\":\"9001\",\"element\":\"All\",\"cooldown\":\"2\",\"purchase_cost\":\"10000\",\"use_cost\":100,\"description\":\"Fires a cannon fully of happy&#039;s\",\"battle_text\":\"[player] pulls out a massive planet sized cannon and annihilates [opponent] with fluffy rainbows of rainbowness.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"cast_speed_nerf\",\"effect_amount\":\"100\",\"effect_length\":\"5\"},{\"name\":\"Fountain of Youth\",\"rank\":\"4\",\"hand_seals\":\"9002\",\"element\":\"Life\",\"cooldown\":\"50\",\"purchase_cost\":\"1000000\",\"use_cost\":null,\"description\":\"Turn everyone into kids!\",\"battle_text\":\"[player] throws a bucket of water on [opponent] turning them into a toddler.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"none\",\"effect_amount\":\"5000\",\"effect_length\":\"100\",\"power\":4.5}]'),
(32, 'Shattering Glass', 14, 'Sand', '3', '[]', '[{\"power\":\"20\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"5\",\"effect\":\"genjutsu_resist\"}]', '[{\"name\":\"Shattered Hailstorm\",\"rank\":\"2\",\"power\":\"1.7\",\"hand_seals\":\"500\",\"element\":\"none\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Forms glass in the sky where it soon shatters and rains down upon the enemy.\",\"battle_text\":\"[player] releases [gender2] chakra as well as small particles of sand up into the atmosphere, combining both together to create a large orb of glass. Within seconds [player] makes a gesture that causes the glass to shatter and rain down many small razor sharp pieces on [opponent].\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"20\",\"effect_length\":\"3\"}]'),
(33, 'Descendants of Blood', 3, 'Mist', '3', '[{\"power\":\"10\",\"effect\":\"scout_range\"}]', '[{\"power\":\"10\",\"effect\":\"genjutsu_boost\"}]', '[{\"name\":\"Scent of Decay\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"501\",\"element\":\"none\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"A complex bloodline ritual used to summon corpses from the earth that look real but are indeed near perfect Illusion\",\"battle_text\":\"[player] performs a complex ritual known only to special individuals within the clan. Placing one palm on the ground [player] summons several ninja corpses that crawl from the earth and lunge at [opponent], striking with a multitude of broken weapons.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"100\",\"effect_length\":\"1\"}]'),
(34, 'Energy Manipulation', 12, 'Leaf', '3', '[{\"power\":\"10\",\"effect\":\"regen\"}]', '[{\"power\":\"15\",\"effect\":\"ninjutsu_boost\"}]', '[{\"name\":\"Energy Egg\",\"rank\":\"2\",\"power\":\"2.0\",\"hand_seals\":\"502\",\"element\":\"none\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Traps enemy within a transparent oval of energy that protrudes spikes on the inside.\",\"battle_text\":\"Using [gender2] Energy to surround [opponent], [player] forms a solid and transparent oval shaped barrier around them. Even with all of [opponent]&#039;s efforts they are unable to break free before being slowly pierced by the slow moving spikes that form within the egg shaped prison.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"taijutsu_nerf\",\"effect_amount\":\"15\",\"effect_length\":\"2\"}]'),
(35, 'Molded Energy', 20, 'Cloud', '3', '[]', '[{\"power\":\"20\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"5\",\"effect\":\"cast_speed_boost\"}]', '[{\"name\":\"Barrage of Lasers\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"504\",\"element\":\"none\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Many neon violet lasers appear in the form of blades before being fired directly at the enemy.\",\"battle_text\":\"[player] morphs [gender2] special energy in such a way as to create many singular blades that float in front of [player]. Within moments, [opponent] finds themselves trying to dodge the many neon violet blades of energy that fly at them.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"}]'),
(36, 'Cavalcade of Souls', 19, 'Stone', '3', '[{\"power\":\"5\",\"effect\":\"regen\"}]', '[{\"power\":\"15\",\"effect\":\"genjutsu_boost\"},{\"power\":\"5\",\"effect\":\"ninjutsu_resist\"}]', '[{\"name\":\"Stalking Spirits\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"503\",\"element\":\"none\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Many souls lurk throughout the area stalking their opponent and waiting for a moment of weakness to possess their body.\",\"battle_text\":\"Faint images can be seen surrounding [opponent] as panic overcomes them. [opponent] tries to keep their eyes focused on all of the spirits, but once their guard has slipped for only a moment they are quickly possessed, their body no longer their own but is now [player]&#039;s body to toy with as [gender] sees fit.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"40\",\"effect_length\":\"3\"}]'),
(37, 'Superconductive Metals', 15, 'Cloud', '2', '[]', '[{\"power\":\"20\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"10\",\"effect\":\"ninjutsu_resist\"}]', '[{\"name\":\"Connected Circuit\",\"rank\":\"2\",\"power\":\"2.1\",\"hand_seals\":\"505\",\"element\":\"none\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Using many small minerals of metal under the ground, a field of undying electricity is made.\",\"battle_text\":\"[player] pulls out a kunai, stabbing it directly into the ground and sending a current though the weapon which spreads to the earth below, sending signals to small chucks of metal. The ground lights up a bright yellow before exploding with a huge electrical surge that fries the immediate area.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"taijutsu_nerf\",\"effect_amount\":\"15\",\"effect_length\":\"2\"},{\"name\":\"Titanium Tomb\",\"rank\":\"3\",\"power\":\"3.0\",\"hand_seals\":\"506\",\"element\":\"none\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"Seal the enemy within a giant titanium pillar that reaches toward the sky. Electricity offers a deadly combination.\",\"battle_text\":\"Five small white pillars fall from the sky and land five meters in every direction around [opponent]. [player] then summons down a humongous hollowed out titanium pillar which traps [opponent] within, at that point each pillar begins conducting electricity before ricocheting the blast at the titanium pillar, increasing the electrical heat to a body melting degree.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"20\",\"effect_length\":\"2\"}]'),
(38, 'Autumn&#039;s Rebirth', 12, 'Leaf', '2', '[{\"power\":\"10\",\"effect\":\"regen\"}]', '[{\"power\":\"10\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"10\",\"effect\":\"genjutsu_resist\"}]', '[{\"name\":\"Fire of Revival\",\"rank\":\"2\",\"power\":\"2.2\",\"hand_seals\":\"507\",\"element\":\"Fire\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"A jutsu that summons a ferocious fire to devour the landscape, but its wraith is far from over.\",\"battle_text\":\"[player] looks up, seeing the many leaves flutter around the forest of seemingly endless trees and takes in a deep breath, reveling in the moment. [opponent] sensing the moment of weakness, moves to strike, but is met with a unfavorable trap. The leaves pick up speed and soon begin to burn, setting the whole forest ablaze in seconds and catching [opponent] in the ensuing embers.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"10\",\"effect_length\":\"2\"},{\"name\":\"Forest of Renewal\",\"rank\":\"3\",\"power\":\"3.5\",\"hand_seals\":\"508\",\"element\":\"none\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"Bring birth back to the forest as if flames have never devoured it, but with the added bonus of trapping the enemy within, their life force draining.\",\"battle_text\":\"Once the flames of the Tremendous fire had died down [player] takes in a deep breath and bending down towards the earth they exhale. Their breath causing the earth to once again breath life. lush grass covers the area as [opponent] lies motionless, within seconds the forest begins to develop and [opponent] is now one with the ever growing forest, which feeds off [opponent]&#039;s life force to survive.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"}]'),
(39, 'Evoked Inugami', 13, 'Stone', '2', '[{\"power\":\"5\",\"effect\":\"regen\"}]', '[{\"power\":\"20\",\"effect\":\"genjutsu_boost\"},{\"power\":\"5\",\"effect\":\"taijutsu_resist\"}]', '[{\"name\":\"Shadow Canines Assault\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"516\",\"element\":\"none\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Creates the illusion of bloodhounds attacking your the enemy.\",\"battle_text\":\"[player] channels [gender2] dark powers into [opponent]&#039;s mind. The hatred and darkness soon begin to take over as Black bloodhounds seem to emerge from the shadows driven by revenge. The hounds simultaneously attack [opponent] with vicious bared fangs.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"55\",\"effect_length\":\"2\"},{\"name\":\"Ritual of the Inugami\",\"rank\":\"3\",\"power\":\"3.5\",\"hand_seals\":\"517\",\"element\":\"none\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"Simulates the ritual for evoking a giant Inugami to bring terror and dread upon the enemy.\",\"battle_text\":\"[player] takes full control of [opponent]&#039;s mind. [opponent] suddenly finds themselves buried neck deep in solid ground, desolated and abandoned. A giant Inugami uses it&#039;s sharp claws to cut [opponent]&#039;s throat in an act of vengeance towards humanity.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"ninjutsu_nerf\",\"effect_amount\":\"55\",\"effect_length\":\"2\"}]');
INSERT INTO `bloodlines` (`bloodline_id`, `name`, `clan_id`, `village`, `rank`, `passive_boosts`, `combat_boosts`, `jutsu`) VALUES
(40, 'Monstrous Transformation', 11, 'Mist', '2', '[{\"power\":\"10\",\"effect\":\"scout_range\"}]', '[{\"power\":\"20\",\"effect\":\"ninjutsu_boost\"}]', '[{\"name\":\"Aqua Torpedo\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"512\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Transforming one&#039;s self into a shark to gain enough momentum and deliver a blow similar in style to a torpedo.\",\"battle_text\":\"Transforming into a shark, [player] dives deep under the surface of the water as [opponent] looks for any signs of bubbles to signal breath. suddenly surging from underneath a blast of water launches [opponent] into the air. Taking advantage, [player] springs from the water wrapping [gender2] massive jaw around [opponent&#039;]s waist, sinking [gender2] teeth in.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"none\",\"effect_amount\":\"15\",\"effect_length\":\"2\"},{\"name\":\"Devouring Frenzy\",\"rank\":\"3\",\"power\":\"2.9\",\"hand_seals\":\"513\",\"element\":\"Water\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"Demonstrating extreme ability and master over water jutsu as well as the furiousness of a shark.\",\"battle_text\":\"[player] releases a torrent of water that pushes [opponent] back into a larger body of water, [player] cast a series of jutsu, one that traps [opponent] in a undying whirlpool, the other is cast when user submerges themself, summoning one hundred ferocious and hungry sharks. With the help of [player] the sharks tear [opponent] to pieces.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"ninjutsu_nerf\",\"effect_amount\":\"15\",\"effect_length\":\"2\"}]'),
(41, 'Toxic Atmosphere', 14, 'Sand', '2', '[]', '[{\"power\":\"25\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"5\",\"effect\":\"heal\"}]', '[{\"name\":\"Parasitic organisms\",\"rank\":\"2\",\"power\":\"2.1\",\"hand_seals\":\"514\",\"element\":\"none\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"A deadly jutsu, do to it&#039;s harmless nature because of unseen organisms.\",\"battle_text\":\"[player] lets loose from [gender2] mouth a pungent smelling cloud of haze, the haze encompasses the area, appearing to do nothing until reaching [opponent]. Tiny unseen organisms burrow under [opponent]&#039;s skin, feasting on their flesh as a look of extreme agony appears on [opponent]&#039;s face.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"drain_chakra\",\"effect_amount\":\"15\",\"effect_length\":\"2\"},{\"name\":\"Neutralized Functions\",\"rank\":\"3\",\"power\":\"2.9\",\"hand_seals\":\"515\",\"element\":\"none\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"Once the parasites have satisfied their hunger and have sustained their livelihood they move on to severely handicapping the enemy&#039;s body.\",\"battle_text\":\"Although [opponent] thinks the presence of the parasite is gone they soon find to be in no such luck. The parasites, now nourished and thriving begin to severely damage [opponent]&#039;s chakra and bodily systems. Once complete all the parasites die, tainting [opponent]&#039;s body as they do.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"taijutsu_nerf\",\"effect_amount\":\"15\",\"effect_length\":\"2\"}]'),
(42, 'Somber Requiem', 28, 'Mist', '1', '[]', '[{\"power\":\"20\",\"effect\":\"genjutsu_boost\"},{\"power\":\"5\",\"effect\":\"ninjutsu_resist\"},{\"power\":\"5\",\"effect\":\"heal\"}]', '[{\"name\":\"Eternal Slumber\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"509\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"A special music box, it plays a tune used to lull the enemy into a near inescapable sleep.\",\"battle_text\":\"Retaining the look of malice upon [gender2] face, [player] holds in [gender2] hands a small music box that when opened plays a tune similar to a lullaby. Unable to resist and feeling the effect of drowsiness, [opponent] soon falls into a deep slumber, haunted by images they can not awake from. [player] grins, knowing the fun has just begun.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"taijutsu_nerf\",\"effect_amount\":\"55\",\"effect_length\":\"2\"},{\"name\":\"Forgotten Ones\",\"rank\":\"3\",\"power\":\"3.5\",\"hand_seals\":\"510\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"The forgotten Children want a new play mate, it&#039;s time to appease them.\",\"battle_text\":\"When [opponent] finally awakens from their sleep, they find that they are no longer where they once were and now surround by children, with varying masks. [opponent]&#039;s face turns to horror upon removal of the masks, revealing faces of those that supposedly died long long ago. &quot;You&#039;re one of us now&quot; they squeak with joy at [opponent]&#039;s now childlike appearance much similar to them.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"100\",\"effect_length\":\"1\"},{\"name\":\"Faded Memories\",\"rank\":\"4\",\"power\":\"4.5\",\"hand_seals\":\"511\",\"element\":\"None\",\"cooldown\":\"2\",\"purchase_cost\":\"10000\",\"use_cost\":\"300\",\"description\":\"People are always curious of how the music box plays so many various musical notes, they must never know the answer of course, without paying the price...\",\"battle_text\":\"For one final time [player] opens the music box which plays a different tune, one that feels.. Empty. As the song plays on, [opponent] is bound in cuffs, their body wrapped in black linen before they are summoned to a room with no windows, doors or light. only a faint tune can be heard before their ears rest upon a string of words. &#039;Your memories are no longer your own, you&#039;re now part of my music.&#039;\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"absorb_chakra\",\"effect_amount\":\"35\",\"effect_length\":\"3\"}]'),
(43, 'Nature&#039;s Calamity', 29, 'Leaf', '1', '[{\"power\":\"5\",\"effect\":\"regen\"}]', '[{\"power\":\"10\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"10\",\"effect\":\"taijutsu_resist\"},{\"power\":\"10\",\"effect\":\"heal\"}]', '[{\"name\":\"Rose Garden\",\"rank\":\"2\",\"power\":\"2.1\",\"hand_seals\":\"517\",\"element\":\"none\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Slowly encloses the enemy in a field of roses which secrete a deadly toxin when contact is made with it&#039;s thorns.\",\"battle_text\":\"[player] enhances the growth rate of the ground and traps [opponent] in a lard field of different colored roses, the thorns of which all secrete a deadly poison. [opponent] is wrapped within the deadly garden of roses, where only muffled screams can be heard though they beauty of the roses.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"15\",\"effect_length\":\"2\"},{\"name\":\"Devouring Landscape\",\"rank\":\"3\",\"power\":\"3.5\",\"hand_seals\":\"518\",\"element\":\"none\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"Engulfs the enemy within the very earth itself, dooming them to fall to it&#039;s core.\",\"battle_text\":\"Placing [gender2] left hand on the surface of the ground [player] opens up a gigantic crevasse below [opponent]&#039;s feet that appears in the shape of a mouth leading to the depth of a fiery core. As [opponent] falls the crevasse slowly closes, cutting off light from the world above and sending them spiraling to the fiery landscape below.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"},{\"name\":\"Harmonic Balance\",\"rank\":\"4\",\"power\":\"4.5\",\"hand_seals\":\"519\",\"element\":\"none\",\"cooldown\":\"2\",\"purchase_cost\":\"10000\",\"use_cost\":\"300\",\"description\":\"After countless days and nights of practice, a power that rivals that of a sage is obtained.\",\"battle_text\":\"Remaining in a calm and relaxed state while [opponent] is distracted by other means, [player]&#039;s eyes turn a deep purple as a green chakra forms around [gender2] body. [player]&#039;s skin becomes textured like wood and [gender2] senses are heightened to an unimaginable level, gaining power even to that of a sage.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"}]'),
(44, 'Impregnable Defenses', 30, 'Stone', '1', '[]', '[{\"power\":\"10\",\"effect\":\"taijutsu_resist\"},{\"power\":\"10\",\"effect\":\"ninjutsu_resist\"},{\"power\":\"10\",\"effect\":\"genjutsu_resist\"}]', '[{\"name\":\"Tempered Edge\",\"rank\":\"2\",\"power\":\"2.0\",\"hand_seals\":\"520\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Using mineral properties within the bloodstream one is able to manipulate their body, transforming limbs to that of perfected weapons.\",\"battle_text\":\"Rushing forward with no regard for safety, [player] lunges [gender2] fist into [opponent]&#039;s gut. The steel like texture enshrouding [player], contorts and morphs into a sharp blade that extends though [opponent]&#039;s body, not stopping till the tip of the blade pins them into a tree trunk.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"10\",\"effect_length\":\"3\"},{\"name\":\"Enriched Blood\",\"rank\":\"3\",\"power\":\"3.5\",\"hand_seals\":\"521\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"Equaling the temperature of molten metal that solidifies in seconds when bodily contact is made, your blood is a lethal weapon.\",\"battle_text\":\"[player] purposefully lowers [gender2] defenses so [gender2] body can be riddled with many cuts, splattering a portion of their blood across [opponent]&#039;s feet in the process, which solidifies into a hard steel within seconds. With [opponent] trapped, [player] takes the advantage to slash [opponent] multiple times with blades made from [gender2] own blood.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"none\",\"effect_amount\":\"10\",\"effect_length\":\"2\"},{\"name\":\"Unbreakable Willpower\",\"rank\":\"4\",\"power\":\"3.4\",\"hand_seals\":\"522\",\"element\":\"None\",\"cooldown\":\"2\",\"purchase_cost\":\"10000\",\"use_cost\":\"300\",\"description\":\"Having fought many battles, your body is conditioned to last in a fight longer then anyone else.\",\"battle_text\":\"[opponent]&#039;s body has grown weak not just from the ongoing fight but also from the amount of energy that had been secretly drained from their body. [opponent]&#039;s body has grown so weak they can barely move, taking the advantage [player] coats [gender2] fist in steel and sends [opponent] flying with one intense punch.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"drain_chakra\",\"effect_amount\":\"12.5\",\"effect_length\":\"3\"}]'),
(45, 'Frigid Tundra', 31, 'Mist', '1', '[]', '[{\"power\":\"30\",\"effect\":\"ninjutsu_boost\"},{\"power\":\"10\",\"effect\":\"cast_speed_boost\"},{\"power\":\"5\",\"effect\":\"heal\"}]', '[{\"name\":\"Icicle Shrapnel\",\"rank\":\"2\",\"power\":\"2.0\",\"hand_seals\":\"523\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"A deadly explosion of senbon shaped icicles that severely cripple opponent by targeting vital areas.\",\"battle_text\":\"[player] launches a multitude of razor sharp ice shuriken, using them as a distraction, [player] secretly forms a small trail of clear ice along the ground. When reaching it&#039;s target, the ice expands and explodes causing icicles to shower [opponent] and strike critical areas along [opponent]&#039;s body.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"taijutsu_nerf\",\"effect_amount\":\"15\",\"effect_length\":\"2\"},{\"name\":\"Crippling Cold\",\"rank\":\"3\",\"power\":\"2.9\",\"hand_seals\":\"524\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"Traps the opponent within a blizzard that slows them immensely and leaves them open for several attacks.\",\"battle_text\":\"[player] disperses [gender2] chakra into the sky causing a massive storm of wind and snow that blankets the surrounding area and severely cripple mobility for [opponent]. Without warning, [opponent]&#039;s tendons and vital areas are cut with continued strikes from a blade made out of ice.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"15\",\"effect_length\":\"2\"},{\"name\":\"Awakening Storm\",\"rank\":\"4\",\"power\":\"4.5\",\"hand_seals\":\"525\",\"element\":\"None\",\"cooldown\":\"2\",\"purchase_cost\":\"10000\",\"use_cost\":\"300\",\"description\":\"Ice shaped spears skewer opponent in place before they are crushed beneath the belly of a gigantic whale.\",\"battle_text\":\"Ice shaped spears skewer opponent in place before they are crushed beneath the belly of a gigantic whale.\",\"use_type\":\"projectile\",\"jutsu_type\":\"ninjutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"}]'),
(46, 'Ancient&#039;s Strength', 34, 'Sand', '1', '[]', '[{\"power\":\"35\",\"effect\":\"taijutsu_boost\"},{\"power\":\"10\",\"effect\":\"heal\"},{\"power\":\"5\",\"effect\":\"speed_boost\"}]', '[{\"name\":\"Crushing Power\",\"rank\":\"2\",\"power\":\"2.0\",\"hand_seals\":\"526\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"Using great strength, the earth itself is altered and soon with enough build up of flammable gases from below an explosion is imminent.\",\"battle_text\":\"Pumping massive power into their muscular frame, [player] slams the ground with [gender2] fist causing the very earth to shake and the land itself to split apart. Gasses rise from the cracks soon coming in contact with a small amount of static electricity that leads to a catastrophic explosion.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"15\",\"effect_length\":\"2\"},{\"name\":\"Unfathomable Density\",\"rank\":\"3\",\"power\":\"2.9\",\"hand_seals\":\"527\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"A weapon that can only be wielded by the strongest of people, due to it&#039;s incredible weight. It has the power to increase the weight of anything it cuts.\",\"battle_text\":\"Using a scroll [gender] placed down on the ground [player] summons a jet black Naginata, three times the size of [opponent] and a thousand times heavier. [player] weilds the weapon with ease, each small cut leads to [opponent]&#039;s body increasing in density which in turn makes it harder for them to dodge said cuts.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"cast_speed_nerf\",\"effect_amount\":\"15\",\"effect_length\":\"2\"},{\"name\":\"Might of the Gods\",\"rank\":\"4\",\"power\":\"4.5\",\"hand_seals\":\"528\",\"element\":\"None\",\"cooldown\":\"2\",\"purchase_cost\":\"10000\",\"use_cost\":\"300\",\"description\":\"A technique that paralyzes opponent before sending them flying into a nearby mountain.\",\"battle_text\":\"[player] delivers a blow to [opponent]&#039;s forehead, which utterly annihilates their chakra network. [opponent] collapses, their body paralyzed. With [opponent]&#039;s movement hindered, [player] takes the time to gather enough energy and slams the ground, sending [opponent] spiraling into the air at such high velocity that when they collide with the mountain, the very mountain itself explodes.\",\"use_type\":\"physical\",\"jutsu_type\":\"taijutsu\",\"purchase_type\":\"2\",\"effect\":\"none\"}]'),
(17, 'Death&#039;s Gaze', 23, 'Leaf', '1', '[{\"power\":\"15\",\"effect\":\"scout_range\"}]', '[{\"power\":\"20\",\"effect\":\"genjutsu_boost\"},{\"power\":\"5\",\"effect\":\"intelligence_boost\"}]', '[{\"name\":\"Luminescent Landscape\",\"rank\":\"2\",\"power\":\"2.5\",\"hand_seals\":\"201\",\"element\":\"None\",\"purchase_cost\":\"10000\",\"use_cost\":\"30\",\"description\":\"With the power over their eye the temperature and toxicity of the area can be changed in an instant.\",\"battle_text\":\"Without hesitation, [player] pulls off the bandage covering [gender2] Right eye. In an instant the area becomes a radiated wasteland before the air becomes increasingly cold, making it near to impossible for [opponent] to breath.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"55\",\"effect_length\":\"2\"},{\"name\":\"Dimensional Gateway\",\"rank\":\"3\",\"power\":\"3.5\",\"hand_seals\":\"209\",\"element\":\"None\",\"cooldown\":\"1\",\"purchase_cost\":\"10000\",\"use_cost\":\"100\",\"description\":\"Opening up gateways from other worlds that contain deadly and life altering scene&#039;s. \",\"battle_text\":\"Three giant gates appear behind [opponent] before they slowly begin to open. Toxic fumes come out from one and solar radiation another, the third one, reveals something so horrid and disturbing that [opponent]&#039;s body turns pale, skin cold to the touch, they have all but lost the will to live.\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"55\",\"effect_length\":\"2\"},{\"name\":\"Inescapable Fate\",\"rank\":\"4\",\"power\":\"4.5\",\"hand_seals\":\"210\",\"element\":\"None\",\"cooldown\":\"2\",\"purchase_cost\":\"10000\",\"use_cost\":\"300\",\"description\":\"One must make direct eye contact with the user of this skill for it to have any effect.\",\"battle_text\":\"When [opponent]&#039;s eyes finally glance upon the solitary eye of [player] they are immediately transfixed. [player] motions [opponent] to walk over against their will as [player] places one hand on [opponent]&#039;s forehead, summoning forth a thousand clones of [opponent]. [player] whispers one last phrase before vanishing. &#039;they die one by one, when the last takes its final breath.. so shall you.&#039;\",\"use_type\":\"projectile\",\"jutsu_type\":\"genjutsu\",\"purchase_type\":\"2\",\"effect\":\"residual_damage\",\"effect_amount\":\"55\",\"effect_length\":\"2\"}]');


-- Add jutsu
INSERT INTO `jutsu` (`jutsu_id`, `name`, `jutsu_type`, `rank`, `power`, `hand_seals`, `element`, `parent_jutsu`, `purchase_type`, `purchase_cost`, `use_cost`, `use_type`, `cooldown`, `battle_text`, `description`, `effect`, `effect_amount`, `effect_length`) VALUES
(1, 'Spinning Stars', 'taijutsu', 1, 1.5, '1433', 'None', 0, 2, 200, 10, 'projectile', 0, '[player] pulls out from [gender2] pouch a small handful of shuriken which they toss simultaneously. Aimed at [opponent]&#039;s trajectory, all varying in speed to make it harder to dodge.', 'A very basic move but a move that is highly used by many in one form or another. To put it simply, throwing multiple shuriken at once.', 'none', 0, 0),
(2, 'Synergized Channeling', 'ninjutsu', 1, 1.5, '12-8-1', 'None', 0, 2, 200, 10, 'projectile', 0, 'Entering a calm and relaxed state while building up increasing amounts of energy [player] release some of this energy in a well timed punch filled with chakra particles.', 'With many hours put into studying Chakra nature and increased time and effort one can learn to enhance the power of their abilities.', 'none', 10, 1),
(3, 'Standard Strike', 'taijutsu', 1, 1.2, '106', 'None', 0, 1, 0, 8, 'physical', 0, '[player] performs a swift punch to [opponent]&#039;s head.', 'A basic punch.', 'none', NULL, NULL),
(4, 'Basic Replacement', 'ninjutsu', 1, 1.2, '1-2', 'None', 0, 2, 100, 8, 'projectile', 0, '[player]&#039;s position is switched with that of a log when [opponent] attacks. [player] then strikes from behind. ', 'A basic Ninjutsu.', 'taijutsu_nerf', 10, 2),
(6, 'Chakra Torrent', 'ninjutsu', 1, 1.5, '10-7', 'None', 0, 2, 500, 10, 'projectile', 0, 'Utilizing [gender2] inner flow of chakra [player] fire&#039;s out a wave of scything transparent blue energy from their body which leaves [opponent] wounded from the many cuts and bruises the Jutsu makes.', 'This ability requires a fair understanding of one&#039;s chakra flow as well as its nature, a good enough balance can lead to deadly attacks.', 'residual_damage', 4, 2),
(7, 'Triggered Explosion', 'ninjutsu', 2, 2.4, '9-7', 'None', 0, 2, 2500, 23, 'projectile', 1, '[player] lures [opponent] to a specific area before disappearing from [opponent]&#039;s line of sight. Standing on a branch high above, [player] activates the many paper bombs in the area that simultaneously explode, catching [opponent] within the blast radius.', 'A devastating Ninjutu that when triggered will cause a massive explosion from all the strategically placed paper bombs.', 'residual_damage', 10, 3),
(8, 'Kiddy Combo', 'taijutsu', 1, 1.5, '165', 'None', 0, 2, 500, 10, 'physical', 0, 'Taking a awkwardly positioned offense stance [player] throws a multitude of punches and kicks to [opponent]&#039;s body.', 'A basic barrage of punches and kicks that are slow, sloppy and in no way lethal. but, every Taijutsu user has to start out somewhere.', 'none', 5, 2),
(9, 'Deceiving Environment', 'genjutsu', 1, 1.8, '6-4-3-7', 'None', 0, 2, 800, 11, 'projectile', 0, '[player] quickly runs in the opposite direction of [opponent]. As [opponent] gives chase [player] transforms into a rock out of site to block the path, giving [opponent] only one route where they soon fall victim to a pitfall.', 'A technique that is used to confuse the enemy and lead them into a trap that was prepared earlier.', 'residual_damage', 55, 2),
(10, 'Multiple Clone Panic', 'genjutsu', 1, 1.5, '6-2-5-3', 'None', 0, 2, 500, 10, 'projectile', 0, 'In a matter of seconds [opponent] is surround by up to as many as ten clones of [player]. The clones move around [opponent], making them dizzy and lightheaded from the fast images.', 'This ability uses the simple method of clones to confuse and agitate the enemy.', 'residual_damage', 100, 1),
(11, 'Genjutsu Release', 'genjutsu', 2, 2.1, '9-6', 'None', 0, 2, 1500, 15, 'projectile', 0, '[player] forms a handseal and briefly interrupts the chakra to [gender2] mind, attempting to restore normal chakra flow.', 'A jutsu that restores normal chakra flow, releasing genjutsu.', 'release_genjutsu', 2.5, 1),
(12, 'Transformation Trickery', 'ninjutsu', 1, 1.8, '3-2-12', 'None', 0, 2, 800, 11, 'projectile', 1, '[player] ducks unseen behind a tree as [gender] transforms into a squirrel. [opponent] franticly looks around, completely oblivious to the creature closing in till its to late and they are struck with a point blank chakra strike from behind.', 'Taking on the form of a small creature, sneaking up on a enemy that is not aware of their surroundings can be very unforgiving.', 'genjutsu_nerf', 2, 2),
(13, 'Unexpected Sting', 'taijutsu', 1, 1.9, '100', 'None', 0, 2, 900, 12, 'physical', 1, '[player] sends [opponent] flying with a kick to the gut before revealing a hidden kunai, held on the right sandal sole by chakra, which is launched after the kick to critically wound [opponent].', 'A hidden weapon can be fatal in the right circumstances, combining it with a kick adds insult to injury.', 'residual_damage', 6, 3),
(14, 'Reeling Blow', 'taijutsu', 1, 1.8, '101', 'None', 0, 2, 800, 11, 'physical', 1, 'Giving [opponent] little to no time to react [player] sidesteps, curling their arm inward with their elbow pointed out and delivers one solid blow to [opponent]&#039;s gut.', 'With one swift movement this technique delivers a devastating blow to the gut, using your own elbow as a weapon.', 'none', NULL, NULL),
(15, 'Augmented Shuriken', 'ninjutsu', 2, 2.6, '3-8', 'None', 0, 2, 3500, 28, 'projectile', 0, '[player] throws two small shuriken in [opponent]&#039;s direction. chuckling at the feeble attempt, [opponent] twists to dodge both shuriken but is met with a surprise when the two shuriken expand in shape, being released from their transformation and revealing their true form as windmill shuriken.', 'Transforms two windmill Shuriken into normal shuriken that expand back to normal size when in enemies vicinity.', 'none', NULL, NULL),
(16, 'Ascending Hawk Strike', 'taijutsu', 2, 2.1, '102', 'None', 0, 2, 1500, 15, 'physical', 1, 'Moving at increased speeds [player] ducks just before reaching [opponent], with one movement [gender] lifts up one arm, palm open, and strikes right below [opponent]&#039;s chin causing their head to lift before being launched in the air.', 'If this technique is done right it can send an opponent flying high into the sky, easily open for further attack.', 'none', 12.5, 2),
(17, 'Chakra Needles', 'ninjutsu', 2, 2.4, '11-1-2', 'None', 0, 2, 2000, 23, 'projectile', 1, 'Taking in a huge breath [player] waits till [gender] can no longer hold [gender2] breath or [opponent] is at an optimal range, when the opportunity arises [player] fires a multitude of blue tinted chakra needles.', 'Using ones own inner chakra flow they can morph their chakra into a form that can be extremely sharp.', 'taijutsu_nerf', 10, 2),
(18, 'Chakra Distortion', 'ninjutsu', 2, 2.9, '6-3-12-10', 'None', 0, 2, 5000, 40, 'projectile', 2, '[player] forms a small amount of chakra in [gender2] palm and with this [gender] moves to strike [opponent]&#039;s chest at a perfect angle, when struck [opponent]&#039;s body and inner chakra flow is jumbled for a short period.', 'A ability that uses basic chakra understanding while understanding also how it can effect the body.', 'genjutsu_nerf', 9.5, 2),
(19, 'Projectile Barrage', 'taijutsu', 2, 2.4, '104', 'None', 0, 2, 2500, 23, 'physical', 1, 'Pulling out a small scroll [player] quickly opens it, the writing of the scroll facing [opponent]. letting the scroll hover in the air [player] slams [gender2] hands together causing hundreds of kunai and shuriken to shower [opponent].', 'Utilizing the handiness of a summoning scroll this jutsu allows for massive amounts of weaponry to be stored and fired.', 'taijutsu_nerf', 10, 2),
(20, 'Rhythmic Assault', 'taijutsu', 2, 2.6, '105', 'None', 0, 2, 3500, 28, 'physical', 1, '[player] starts by delivering a powerful side kick to [opponent]s Gut, While [opponent] is reeling in pain [player] calculates [opponent]s reaction and launches a series of powerful strikes to unprotected spots.', 'A series of powerful blows that any beginner in Taijutsu can pull off, these set of strikes can cause massive damage with the right pattern.', 'none', NULL, NULL),
(21, 'Smoke Mirror', 'genjutsu', 2, 2.6, '1-12', 'None', 0, 2, 3500, 28, 'projectile', 1, '[player] pulls out a smoke bomb from [gender2] pouch and tosses it in opponents vicinity. [player] then casts a Genjutsu that forces [opponent] to fight an exact copy of them self, slowly depleting [opponent]&#039;s energy.', 'A Genjutsu that requires the use of a smoke bomb to create an elaborate illusion.', 'residual_damage', 55, 2),
(22, 'Realm of Desolation', 'genjutsu', 2, 2.9, '11-8-1-5', 'None', 0, 2, 5000, 40, 'projectile', 2, 'A devilish grin is all [opponent] can see from [player] before they are enveloped in darkness. The stillness and silence worries [opponent] as they are met many distant growls, mumbles and laughs that slowly drive [opponent] insane.', 'Beginners level Genjutsu that slowly drives even some of the strongest willed people mad.', 'intelligence_nerf', 55, 2),
(29, 'Dragon&#039;s Scorn', 'ninjutsu', 3, 3.9, '12-10-5-6-3', 'Fire', 0, 2, 25000, 160, 'projectile', 2, '[player] lets the chakra within [gender2] body mold and take shape within the stomach before spewing out a linear line of fire from [gender2] mouth that runs across the ground and bathes the area in intensifying flames.', 'The power of this technique is only as powerful as its casters ability to control their element and properly disperse its flames', 'residual_damage', 25, 2),
(23, 'Wired Weaponry', 'taijutsu', 2, 2.9, '138', 'None', 0, 2, 5000, 40, 'physical', 2, '[player] Quickly tosses two wired Shuriken that zoom past [opponent] and wrap around a tree branch. [opponent], unaware of the wires is caught off guard when [player] uses another tree branch as leverage to snap the previous branch which flies at [opponent] with great speed.', 'Using an arsenal of gear you can always come up with some sort of tactic even in the most dire of times.', 'ninjutsu_nerf', 8.75, 2),
(24, 'Energy Flare', 'ninjutsu', 2, 2.1, '7-1-4', 'None', 0, 2, 1500, 15, 'projectile', 0, 'Focusing [gender2] chakra in one palm and closing [gender2] fingers around the now visible chakra, [player] releases the orb of concentrated chakra into the sky where it expands and explodes with a huge flash of light sending waves of chakra that pelt the area and [opponent].', 'An intense expansion of chakra that eventually explodes and sends out harmful pulses of chakra.', 'willpower_nerf', 5, 2),
(25, 'Caves of Disillusion', 'genjutsu', 2, 2.4, '5-1-3-8', 'None', 0, 2, 2500, 23, 'projectile', 1, '[player] tricks [opponent] into following them inside a nearby cave where [opponent] quickly loses track of [player]. Lost within the caves, [opponent] is suddenly barraged with projectiles from a carefully arranged ambush.', 'A jutsu that purposefully leads enemy in the wrong direction time after time till they are to exhausted to continue.', 'residual_damage', 100, 1),
(26, 'Searing Agony', 'genjutsu', 1, 1.2, '12', 'None', 0, 2, 100, 8, 'projectile', 0, ' [player] touches [opponent], connecting their flows of chakra. The unfamiliar presence enters [opponent]&#039;s mind generating pragmatic burning sensations.', 'The most simplistic of genjutsu, usable by anybody. Enter your opponents mind and make them believe their body is on fire!', 'residual_damage', 55, 2),
(27, 'Chakra Negation', 'genjutsu', 2, 2.1, '4-10-2', 'None', 0, 2, 2000, 15, 'projectile', 1, '[player] cast a Genjutsu upon [gender2] own body, one that decreases the effectiveness of any chakra based attack by activating key points in [player]&#039;s chakra network that are not easily opened using traditional methods.', 'Creates a negating field around the body that can reduce the effectiveness of oncoming jutsu.', 'ninjutsu_nerf', 35, 3),
(28, 'Aerial Hammer', 'taijutsu', 2, 2.4, '1112', 'None', 0, 2, 2000, 23, 'physical', 1, '[player] focuses chakra into the soles of [gender2] feet before launching into the air and using the downward momentum to deliver a solid kick to [opponent]&#039;s skull, setting them off balance and ill prepared for the oncoming battle ahead.', 'A solid blow that will leave opponent in a daze and decease their chances of holding the user in a successful genjutsu.', 'genjutsu_nerf', 6, 2),
(30, 'Intensifying Heat', 'ninjutsu', 3, 3.4, '10-5-3-4-8', 'Fire', 0, 2, 8000, 110, 'projectile', 2, '[player] increases the temperature in the surrounding area by emitting heat from [gender2] body. The heat soon becomes unbearable for [opponent] causing them to sweat profusely. [opponent]&#039;s coordination is soon jumbled while they can barely keep themselves from fainting.', 'A jutsu that emits a continues wave of heat from the casters body, steadily increasing the temperature in the surrounding area to unbearable heights.', 'none', 18.5, 4),
(31, 'Chilling Tides', 'ninjutsu', 3, 3.4, '3-5-10-4-1', 'Water', 0, 2, 8000, 110, 'projectile', 1, 'With one quick motion [player] performs the hand signs needed while near a source of water, from that source shoots a wave of freezing water that slams into enemy, causing average damage and leaving [opponent] in a state of hypothermia.', 'A technique using ones own water ability as well as the ability to change the waters temperature at opportune moments.', 'taijutsu_nerf', 18, 3),
(32, 'Liquid Bullet', 'ninjutsu', 3, 3.9, '7-3-9-2-11', 'Water', 0, 2, 25000, 160, 'projectile', 2, 'Without being near a source of water, [player] gathers small particles of liquid from under the earth to form stable orb of water which is fired at incredible speeds much like that of a bullet. When contact is made, [opponent] is sent flying into a nearby structure.', 'Forming water in a small spherical shape that can then be fired at incredibly high speeds which are near to impossible to dodge.', 'drain_chakra', 10, 2),
(33, 'Dancing Winds', 'ninjutsu', 3, 3.9, '5-4-8-12-2', 'Wind', 0, 2, 25000, 160, 'projectile', 2, '[player] kicks up a huge dust storm that blinds [opponent]&#039;s vision. within this space of time [player] launches many timely but small crescent shaped wind strikes that cut at [opponent]&#039;s limbs causing severe gashes and hemorrhaging.', 'Many people have been fooled by the change in wind and its patterns, this ability is no different, making anyone helpless to its random nature.', 'residual_damage', 20, 2),
(34, 'Cleaving Moon Crescent', 'ninjutsu', 3, 3.9, '3-7-2-1', 'None', 6, 2, 18000, 160, 'projectile', 3, 'Coating [gender2] blade in a fine, thin layer of chakra, [player] charges the chakra to dangerously high levels. Slashing the empty space in front of themselves several times. [gender] lets loose an array of blue tinted crescent shaped chakra attacks, all rotating at deadly speeds and severely wounding [opponent] on contact.', 'With the help of a blade, chakra is formed and fired out in the shape of a crescent from said blade delivering a devastating blow from its sheer speed and rotation.', 'none', NULL, NULL),
(35, 'Poisonous Haze', 'ninjutsu', 3, 3.4, '6-1-5-12-8', 'None', 0, 2, 8000, 110, 'projectile', 2, '[player] lets out an expanding cloud of yellow poisonous fumes from [gender2] mouth, when the fumes enter the body of [opponent] by any means necessary the effects of nausea, dizziness and pain begin to set in. [opponent]&#039;s nerves are also damaged, leaving their chakra flow crippled.', 'A poisonous yellow cloud is spewed from the casters mouth, causing nausea and dizziness before damage the enemy&#039;s nerves.', 'ninjutsu_nerf', 20.5, 2),
(36, 'Contained Detonation', 'ninjutsu', 3, 3.4, '7-1-9-2-12', 'None', 7, 2, 10000, 110, 'projectile', 2, '[player] puts up a transparent barrier around [opponent] without their knowledge. Within seconds of [opponent] realizing this, [player] sets off a series of well placed tag bombs that result in a contained but devastating explosion that engulfs the area inside the barrier for several seconds.', 'Placing four highly explosive bomb tags in key locations, a barrier is put up to trap the enemy inside, substantially increasing the power of the contained blast tenfold.', 'residual_damage', 15, 3),
(37, 'Jolting Circuit', 'ninjutsu', 3, 3.4, '11-7-12-9', 'Lightning', 0, 2, 8000, 110, 'projectile', 1, 'Throwing many shuriken in [opponent]&#039;s direction, [player] shoots out a tiny, quick spark of lightning that comes in contact with the scattered shuriken dotted across the ground,. This creates a link between each and every shuriken, causing a massive surge in electricity that fries [opponent].', 'A technical jutsu that brings weapons into play and combines their metal structure with electrical output to deliver devastating volts.', 'willpower_nerf', 14.5, 3),
(38, 'Shallow Ground', 'ninjutsu', 3, 3.4, '3-2-7-10', 'Earth', 0, 2, 8000, 110, 'projectile', 1, '[player] shapes and molds the ground before them, loosening the earth and its minerals. [opponent] is caught of guard and quickly sinks into the ground before it returns to a solid state, leaving [opponent] trapped but also struggling for air with the earth pressed up against their chest.', 'being very simple yet very deadly at the same time this technique can be used to great effect against pursuers as well as a trap simply by trapping them in the earth.', 'taijutsu_nerf', 15, 3),
(39, 'Electrical Clones', 'ninjutsu', 3, 3.9, '4-8-11-3-2', 'Lightning', 0, 2, 25000, 160, 'projectile', 2, '[player] splits [gender2] chakra into three separate clones that all attack [opponent] in unison. Being completely unaware of the situation as [player] had hoped, [opponent] strikes one of the clones head on, causing it and the others to explode in a burst of electrical energy.', 'Much more advanced then a simple clone but not as advanced as a shadow clone, this clone will explode in a wave of electricity when destroyed.', 'residual_damage', 10, 2),
(40, 'Earthly Weapons', 'ninjutsu', 3, 3.9, '10-2-9-4-11', 'Earth', 0, 2, 25000, 160, 'projectile', 2, '[player] cast a basic jutsu that turns the ground below [opponent] into small makibishi like spikes, causing them to stumble. Taking the perceived advantage, [player] molds the earth and pulls out from it, twin throwing spears that [gender] lunges at [opponent] with deadly accuracy.', 'Using the materials in the ground one can make a variety of weapons and distracting traps without the need to buy such expensive equipment.', 'residual_damage', 22.5, 2),
(41, 'Propelling Burst', 'ninjutsu', 3, 3.4, '8-3-5-1', 'Wind', 0, 2, 8000, 110, 'projectile', 1, 'When the opportune moment arrives [player] releases [gender2] wind natured chakra in a huge exploding burst below [opponent]&#039;s feet, sending them skyward. With [opponent] vulnerable to attack, [player] strikes [opponent] with several well timed shuriken, covered in wind natured chakra.', 'Sends the enemy flying into the air where they are left open to a series of attacks.', 'none', 12.5, 3),
(42, 'Graceful Flying Falcon', 'taijutsu', 3, 3.4, '1008', 'None', 0, 2, 8000, 110, 'physical', 2, 'Seemingly if out from nowhere, [player] is seen within [opponent]&#039;s peripheral vision flying graceful with one leg extended. Before a timely reaction can be made [opponent] is slammed directly on the side of the head with a overwhelming kick which sends them flying.', 'A quick solid kick to the ememy&#039;s head that is often used as a surprise attack when one enters a battle.', 'intelligence_nerf', 11.25, 2),
(43, 'Tracheal Smash', 'taijutsu', 3, 3.7, '1009', 'None', 0, 2, 12000, 140, 'physical', 2, '[player] delivers a powerful chop to [opponent]&#039;s throat, causing their airways to seize up as [opponent] drops to their knees, grasping their throat to try and get an ounce of breath. When the connection is remade the lack of oxygen leaves their body shaking, making hand sign forming difficult.', 'A simple looking technique but in reality is very complex, requiring the user to have pinpoint accuracy upon impact.', 'cast_speed_nerf', 10, 2),
(44, 'Somersault Spring', 'taijutsu', 3, 3.9, '1010', 'None', 16, 2, 16000, 160, 'physical', 3, '[player] advances forward by doing a series of somersaults that increase in speed, slowly building up power in the legs. On the last somersault, [player] bends [gender2] arms and legs down before shooting up and delivering a powerful double footed kick to [opponent]&#039;s chin.', 'Uses increased momentum and flexibility to send a violent kick under the enemy&#039;s chin.', 'none', 8, 2),
(45, 'Inverted World', 'genjutsu', 3, 3.7, '11-2-12-1-8', 'None', 0, 2, 9000, 140, 'projectile', 2, '[player] casts a genjutsu upon [opponent] which messes with their coordination causing them to wobble as they walk, after awhile [opponent] can feel the ground shift beneath their feet, the planet itself seems to turn upside down as [opponent] falls out of the atmosphere, their source of oxygen cut off.', 'A jutsu that give the enemy the sensation of being pulled out of the planet&#039;s atmosphere.', 'residual_damage', 100, 1),
(46, 'Consuming Blizzard', 'genjutsu', 3, 3.4, '3-1-5-11', 'None', 0, 2, 10000, 110, 'projectile', 3, 'Within second [opponent] finds a cold chill coming across their body, before they know it the ground is covered in ice and a huge blizzard has blanketed the area, seeing no way of escape, [opponent] waits for their inevitable fate to be sealed as their body temperature soon begins to fade.', 'A freezing winter storm is cast throughout the area, making the enemy believe they are slowly being frozen from the inside out, which causes them to lose their will to live.', 'drain_chakra', 50, 2),
(47, 'Time Alteration', 'genjutsu', 3, 3.4, '4-1-7-3-12', 'None', 0, 2, 12000, 110, 'projectile', 3, 'Using an ink seal that [player] has placed on the ground [gender] casts a strong genjutsu over [opponent] which tricks them into believing they are aging rapidly, slowly draining the life force out of [opponent]&#039;s body and causing despair over their inevitable fading life.', 'By the time this genjutsu has faded the enemy will be in an extremely weakened state, barely able to even stand let alone fight.', 'drain_stamina', 50, 2),
(48, 'Feasting Blade', 'genjutsu', 3, 3.9, '10-12-6-3-7', 'None', 46, 2, 17500, 160, 'projectile', 3, '[opponent] catches their eyes upon [player]&#039;s blade that pulsates a purple energy which quickly turns itself into a giant hideous monster of a thing. The beast preys on [opponent]&#039;s fear and causes mental blocks in their chakra network.', 'Using your intermediate skills over genjutsu you are able to personify a genjutsu into your small blade, which when looked at or cut with the enemy will be under the genjutsu&#039;s effects.', 'ninjutsu_nerf', 40, 3),
(49, 'Calmed Awareness', 'genjutsu', 3, 3, '9-1', 'None', 0, 2, 8000, 45, 'projectile', 0, '[player] enters a calming state as [gender] breaths deeply and closes [gender2] eyes. Letting a flow of energy ignite the brain and shatter the illusion that held them prisoner.', 'Due to your calmed state and vastly superior knowledge of how Genjutsu works you are more adept at releasing Genjutsu that have been cast upon you.', 'release_genjutsu', 10, 1),
(50, 'Triple Shadow Spiral', 'taijutsu', 3, 3.7, '1011', 'None', 1, 2, 10000, 140, 'physical', 2, '[player] tosses what appears to be one giant shuriken before [gender] adjusts [gender2] position so that the shuriken doubles back and spirals around [opponent] several times, wrapping [opponent] in a thin metal wire. Two new giant shuriken appear from the shadows of the first and strike [opponent] from all angles.', 'Utilizes the helpfulness of wire, wind speed, trickery and a multitude of other skills you have picked up over the course of your genin days to injure the enemy.', 'residual_damage', 12, 2),
(51, 'Distorted Systems', 'taijutsu', 3, 3.7, '1012', 'None', 13, 2, 18500, 140, 'physical', 3, 'Arming themselves with a senbon between each finger, [player] tosses them at key points in [opponent]&#039;s blind spots, hitting their mark each time and cutting off key points in [opponent]&#039;s chakra network, substantially draining their chakra and crippling them.', 'Using impressive accuracy and timing as well as a impressive proficiency over thrown weapons, one can easily cut off key points in the enemy&#039;s chakra flow.', 'drain_chakra', 8, 2),
(52, 'Inverted Swallow', 'taijutsu', 3, 3.9, '1013', 'None', 0, 2, 20000, 160, 'physical', 1, '[player] slams the hilt of [gender2] blade against [opponent]&#039;s chin to lift them up briefly. Before composure can be regained, [player] grabs [opponent] by the ankle and tosses them into the air where [player] lands a series of slashes with a fine edged katana, using the downward momentum, [player] hooks [opponent] with a Kusarigama and flings them into the ground.', 'Uses a Kusarigama as well as a Katana to pull off a complex and highly lethal weaponry technique.', 'none', NULL, NULL),
(53, 'Crawling Flesh', 'genjutsu', 3, 3.9, '3-9-5-12-6-1', 'None', 55, 2, 21000, 160, 'projectile', 3, '[opponent]&#039;s skin begins to show signs of irritation, causing them to scratch uncontrollably. As [opponent] continues the horrible sensation begins to become unbearable, causing them to rip into their skin and cause physical harm and pain just to stop the horrid creature within that tortures them.', 'A highly used Genjutsu in which the enemy believes there is something crawling under their skin, causing them to descend into madness and physically harm themselves.', 'residual_damage', 55, 2),
(54, 'Preliminary Seal', 'ninjutsu', 3, 3.4, '2-7-1-6-11', 'None', 18, 2, 14000, 110, 'projectile', 4, 'When the opportunity arises [player] slams five chakra soaked fingers on [gender2] right hand directly into [opponent]&#039;s gut, causing a temporary seal to form that burns the energy that is stored up inside of [opponent]&#039;s body, which signals immense waves of pain to shoot though the body.', 'A potent seal is engraved on the enemy to slowly leak the energy that is stored up within their body.', 'drain_stamina', 11, 2),
(55, 'Piercing Soundwave', 'genjutsu', 3, 3.2, '5-7-4-8', 'None', 0, 2, 7500, 75, 'projectile', 2, 'A low hum is heard throughout the immediate area causing [opponent] to hone in on the source of the sound, making them more susceptible to the ear splitting screech that shortly follows. [opponent] is left dazed and unable to function properly due to the mind altering effects of the sound.', 'Sends a shockwave of sound that damages the enemy&#039;s hearing while also entering the brain to severally injure their mental capabilities.', 'intelligence_nerf', 55, 2),
(56, 'Shuriken Storm Illusion', 'genjutsu', 3, 3.7, '1-10-3-4-2', 'None', 0, 2, 15000, 140, 'projectile', 3, 'The sky above [opponent] begins to darken as shuriken fall like raindrops, the resulting waves of never ending shuriken make it difficult for [opponent] to land a decent blow due to the awkwardness of dodging the sharp projectiles, that, unbeknownst to [opponent] deliver no immediate threat.', 'After casting the illusion, a downpour of shuriken will begin to fall out of the sky, seemingly never stopping and making the fight difficult for the enemy.', 'taijutsu_nerf', 40, 3),
(57, 'Tears of the Dragon', 'taijutsu', 3, 3.7, '1014', 'None', 19, 2, 14000, 140, 'physical', 2, '[player] tosses a total of six small scrolls up into the sky, the scrolls unravel and unleash waves of small projectiles that seem to track [opponent]&#039;s movements as if they were self aware. The scale of projectiles increases as each scroll that was thrown into the air summons two new scrolls, soon blanketing the sky in parchment and the ground in metal.', 'Using a vast, almost endless supply of small scrolls one can summon a torrent of shuriken, kunai and senbon to rain on the enemy while tracking their movements.', 'ninjutsu_nerf', 14.25, 2),
(58, 'Ki of the Swift Rabbit', 'taijutsu', 3, 3.9, '1015', 'None', 20, 2, 24000, 160, 'physical', 4, '[player] enters a tranquil state that unlocks a power that enhances [gender2] talents for a short period. With this [player] unleashes a blur of punches that knock [opponent] into the air before sending them crashing into the earth with a drop kick to the gut, the lack in damage is made up for the extreme swiftness of the attack, only taking three seconds.', 'A hidden power is unlocked inside of the body, allowing for quicker movement and enhanced reflexes.', 'speed_nerf', 25, 1),
(59, 'Hachimon: Kai', 'taijutsu', 3, 3.5, '4-11', 'None', 0, 2, 1000, 20, 'buff', 0, '[player] crosses their arms and focuses deep within themselves, unlocking three of the eight inner gates of power within their body. Suddenly their skin turns crimson red and they gain immense energy that can be felt through the air.', 'A powerful but dangerous technique where the user gains greatly increased strength and speed by opening the 8 gates.', 'taijutsu_boost', 50, 2),
(60, 'Chakra Seal Release', 'ninjutsu', 3, 3.5, '8-10', 'None', 0, 2, 1000, 20, 'buff', 0, '[player] forms a handseal and releases an invisible seal, allowing vast reserves of untapped chakra to flow through [gender2] body.', 'User channels chakra from a hidden seal to increase their power.', 'ninjutsu_boost', 50, 2),
(61, 'Mental Vigor', 'genjutsu', 3, 3.5, '1-8', 'None', 0, 2, 1000, 20, 'buff', 0, '[player] pulls out a strange pill and swallows it. The pill temporarily opens new pathways in [gender2] brain, increasing [gender2] mental abilities greatly.', 'Use of a mentally stimulating pill to increase one&#039;s mental acumen.', 'genjutsu_boost', 50, 2),
(62, 'Gaia&#039;s Armory', 'ninjutsu', 4, 4.5, '10-2-9-4-11-1', 'Earth', 40, 2, 35000, 275, 'projectile', 1, '[player] slams his hands into the earth, pouring chakra into it and causing many weapons to be shaped from the ground, ready to be picked up by [player] and used in a massive assault. Using the myriad of at [gender2] disposal to incapacitate [opponent].', 'The user creates weapons out of the earth, needing very little chakra and stronger than most crafted weapons.', 'residual_damage', 25, 2),
(63, 'Flames of the Wicked', 'ninjutsu', 4, 4.5, '12-10-5-6-3-9', 'Fire', 29, 2, 35000, 275, 'projectile', 1, '[player] focuses on a painful memory, using the pain from that experience as fuel for the flames of their rage; focusing those flames into the palms of their hands. The flames reach blinding levels before being shot in a relentless torrent of flames that scorch all in its path.', 'A jutsu utilizing the rage from a painful memory inside the user to scorch the enemy in a massive inferno. The more painful the experience, the brighter the flame.', 'none', NULL, NULL),
(64, 'Flame of the Guardsman', 'ninjutsu', 4, 4.5, '10-5-3-4-8-1', 'Fire', 30, 2, 35000, 275, 'barrier', 4, '[player] sends a continuous pulse of flames in every direction from their body. These flames burn anything and everything, including enemy attacks.', 'Several pulses of flames are emitted, the flames burn anything that attempts to cross it.', 'none', NULL, NULL),
(65, 'Hyper Hydron Osmosis Beam', 'ninjutsu', 4, 4.5, '7-3-9-2-11-12', 'Water', 32, 2, 35000, 275, 'projectile', 1, '[player] focuses chakra at the tip of their finger. Pointing at [opponent], [player] uses [gender2] chakra to condense the moisture in the air, forming a ball. Growing until itâ€™s powerful enough, [player] releases it in a continuous jet of highly pressurized water, which rips through [opponent].', 'A highly pressurized beam of water that can drill through anything, given enough time.', 'none', NULL, NULL),
(66, 'Calm Waters', 'ninjutsu', 4, 4.5, '3-5-10-4-1-2', 'Water', 31, 2, 35000, 275, 'barrier', 4, '[player] quickly creates a vortex of water, any attack absorbed by this is sent to the nearest body of water.', 'Barrier - The calmest waters can hide the darkest monsters.', 'none', NULL, NULL),
(67, 'Death Waltz', 'ninjutsu', 4, 4.5, '5-4-8-12-2-1', 'Wind', 33, 2, 35000, 275, 'projectile', 1, '[player] grins sadistically as they release a burst of their chakra, causing the air in front of them to be molded into barely visible needles are thinner than thread. [player] shoots these to pierce [opponent] body, injecting air into the blood stream with each landed blow.', 'A storm of needles that inject air into the victimâ€™s blood.', 'none', NULL, NULL),
(68, 'Spinning Tempest', 'ninjutsu', 4, 4.5, '8-3-5-1-4', 'Wind', 0, 2, 35000, 275, 'barrier', 4, '[player] swiftly forms handseals and infuses chakra into the air around them, causing gusts of wind to form and spin into a massive tornado around [player], deflecting attacks.', 'Barrier - A massive tornado that deflects almost any attack.', 'none', NULL, NULL),
(69, 'Eye of the Storm', 'ninjutsu', 4, 4.5, '11-7-12-9-1', 'Lightning', 37, 2, 35000, 275, 'projectile', 1, '[player] lets loose a flurry of wired shuriken, one for each finger. Using precise control [player] sends electrical chakra loose through the wire, charging the shuriken with chakra. The shuriken fly randomly at high speeds around [opponent], each one that comes close or makes contact letting loose another powerful shock.', 'The user uses wired kunai to create the perfect storm.', 'none', NULL, NULL),
(70, 'Festival of Light', 'ninjutsu', 4, 4.5, '4-8-11-3-2-1', 'Lightning', 39, 2, 35000, 275, 'barrier', 4, '[player] releases a hoard of clones, each one fit to burst with electricity. These clones attack [opponent] in unison, never letting [gender2] take a break and throwing themselves in front of any attack launched and exploding in a blinding burst of electricity. Buying [player] some time.', 'Barrier - The user lets a hoard of Electrical clones loose upon their opponent.', 'none', NULL, NULL),
(71, 'Burden of the Pharaoh', 'ninjutsu', 4, 4.5, '3-2-7-10-1-4', 'Earth', 38, 2, 35000, 275, 'barrier', 4, '[player] slams kicks the ground hard, pouring chakra from the soul of their foot. This chakra causes several slabs of earth shaped like large hands, ready to catch any attack.', 'Barrier - User summons a wall of hands from the ground, to catch any attack', 'none', NULL, NULL),
(72, 'Ascending Hurricane Kick', 'taijutsu', 4, 4.5, '100-7', 'None', 0, 2, 35000, 275, 'physical', 1, 'Mad with rage [player] charges underneath [opponent] dealing him a high kick to the chin while [opponent] is in mid air [player] places hands on ground and launches self into the air while rapidly spinning upside down and dealing a series of round house kicks to [opponent].', 'The user rapidly spins on hands upside down dealing a series of round house kicks to opponent sending them even higher                into the air with each kick.', 'none', NULL, NULL),
(73, 'Stone Henge Assault', 'taijutsu', 4, 4.9, '100-85', 'None', 0, 2, 50000, 400, 'physical', 2, '[player] starts to move very fast in a circular motion around [opponent]. The increased acceleration causes afterimages to begin appearing. Unable to discern where [player] is, [opponent] takes a sharp lunge at [player] but severely misses. [player] using the new found opportunity launches at [gender2] now vulnerable target with a flurry of attacks.', 'A high speed attack which the user moves at incredible speed to create afterimages of themself.', 'none', NULL, NULL),
(74, 'Exploding Kunai Seal', 'ninjutsu', 4, 4.9, '4-3-11-8-7-1', 'None', 0, 2, 50000, 400, 'projectile', 1, '[player] infuses chakra into the tags on five kunai and throws them onto the ground in a circle around [opponent]. Instantly seals shoot from the kunai to the center of the circle, immobilizing [opponent]. [player] rapidly forms several handseals and the seal begins to glow, before bursting into a violent explosion.', 'A technique that uses kunai to trap the opponent in an exploding seal.', 'none', NULL, NULL),
(75, '100 Shuriken Ambush', 'ninjutsu', 4, 4.1, '12-1-4-6-7-3', 'None', 0, 2, 25000, 180, 'projectile', 0, '[player] turns and runs away a short distance, luring [opponent] in. As [opponent] passes some suspicious looking rock formations [player] suddenly turns around and forms a blur of handseals. The rocks burst into shuriken that fly towards [opponent] from four directions, cloning themselves into a barrage of 100 shuriken.', 'A replicating shuriken', 'residual_damage', 10, 2),
(76, 'Purple Demon Assault', 'genjutsu', 4, 4.5, '10-12-6-3-7-1', 'None', 48, 2, 35000, 275, 'projectile', 1, 'As [opponent] is terrified by the hideous purple demon, [player] ominously points [gender2] hand at [opponent] and the beast instantly tears towards them in a sprint, rending its claws through their flesh in an agonizing assault.', 'A jutsu that uses the Feasting Blade demon to torment the opponent.', 'residual_damage', 55, 2),
(77, 'Earthbreaker Assault', 'taijutsu', 4, 4.1, '127-100', 'None', 0, 2, 25000, 180, 'projectile', 0, 'Matching [gender2] speed with that of [opponent], [player] matches blow for blow with a close combat technique against [opponent]. Seeing [opponent] falter, [player] suddenly increases their speed catching [opponent] off guard and landing a direct hit, sending [opponent] crashing into the ground bringing up mounds of debris in the process.', 'A powerful surprise assault that smashes the opponent into the ground.', 'none', NULL, NULL),
(78, 'Needle Storm Illusion', 'genjutsu', 4, 4.5, '1-10-3-4-2-5', 'None', 56, 2, 35000, 275, 'projectile', 1, 'As [opponent] is overwhelmed by the rain of shuriken, player grins an evil smile and molds chakra into [opponent]&#039;s brain. Suddenly the thousands of shuriken raining down abruptly stop and turn into needles, then come flying at [opponent] and impale them with agonizing pain.', 'A jutsu that turns the endless rain of Shuriken Storm Illusion into a lethal barrage.', 'residual_damage', 55, 2),
(79, 'Nerve Destruction', 'genjutsu', 4, 4.9, '4-1-3-10-12-6', 'None', 0, 2, 50000, 400, 'projectile', 2, '[player] casts a weak genjutsu on [opponent], turning invisible. [opponent] easily breaks the shoddy illusion but [player] is already behind [opponent] with [gender2] hand on the back of [opponent]&#039;s head. [player] floods [opponent]&#039;s nervous system with an agonizing attack that feels as if [opponent]&#039;s entire body is being torn apart.', 'A short-lasting but intense agonizing genjutsu.', 'residual_damage', 100, 1),
(80, 'Drowning Torrent', 'genjutsu', 4, 4.1, '8-4-1-2-5-6', 'None', 0, 2, 25000, 180, 'projectile', 1, '[player] stares directly into [opponent]&#039;s eyes while forming handseals, and gradually [opponent] starts to get an ominous feeling. Suddenly the ground beneath them appears to open up into a bottomless whirlpool of water that drags them down underneath, making it impossible to breathe. [opponent] chokes and gasps for air, flailing wildly.', 'A genjutsu that simulates the feeling of drowning.', 'residual_damage', 55, 2),
(81, 'Afterimage Flash', 'taijutsu', 4, 4.5, '100-28', 'None', 0, 2, 35000, 275, 'physical', 2, 'Releasing a burst of chakra through [gender2] legs, [player] crushes the weight seals placed around their body, thus increasing their speed to extraordinary levels. [player] then rushes [opponent] stirring up an array of afterimages that begin to put [opponent]&#039;s senses into disarray.', 'A hyper-speed technique that uses blurred afterimages to confuse the opponent.', 'genjutsu_nerf', 10, 1),
(82, 'Phantom Palm', 'taijutsu', 4, 4.5, '100-41', 'None', 0, 2, 35000, 275, 'physical', 1, 'Visible chakra swarms around [player]&#039;s head as ghastly power dances just under the skin. [player] rushes at [opponent] making the slightest touch to [opponent]&#039;s  midsection causing a sudden release of pent up charka to incur and rupture [opponent]&#039;s organs.', 'An attack using hidden chakra to cause internal damage.', 'residual_damage', 10, 2),
(83, 'Ravaging Cyclone', 'taijutsu', 4, 4.5, '100-42', 'None', 0, 2, 35000, 275, 'physical', 2, 'Placing  their weapons in a vice like grip, [player] spins on [gender2] heels quickly channeling [gender2] energy and gaining a maelstrom of chakra over [gender2] form. Stopping at once [player] stomps into the ground redirecting their momentum and corkscrewing through the air at breakneck speeds. Upon collision [opponent] is blasted through the air from the concussive force.', 'A spinning techniquye using the power of rotation to cause a vicious blast.', 'none', NULL, NULL),
(84, 'Endless Barrage', 'taijutsu', 4, 4.1, '100-19', 'None', 0, 2, 25000, 180, 'physical', 0, '[player] throws a high speed barrage of standard strikes at [gender2] opponent. With each standard strike released it knocks back [opponent]â€™s attack movement. The pent up energy builds within [player] as [gender] continues the onslaught against [opponent], before finally sending a devastating blow to [opponent]â€™s side.', 'A barrage of continuous attacks.', 'none', NULL, NULL),
(85, 'Pale Odyssey', 'genjutsu', 4, 4.5, '2-3-1-5-8-9', 'None', 0, 2, 35000, 275, 'projectile', 2, 'Grabbing [opponent] by their head [player] stares deeply into the eyes of [opponent] causing their perception of reality to melt away only to be renewed with an explosion of primordial knowledge never before experienced by man. [opponent] begins to foam at the mouth as they go into shock from the sheer amount of terror they experience.', 'A mental overload genjutsu that overwhelms the opponent&#039;s mind with knowledge.', 'residual_damage', 40, 3),
(86, 'Shinobi&#039;s Tragedy', 'genjutsu', 4, 4.5, '8-7-9-1-3-2', 'None', 0, 2, 35000, 275, 'projectile', 1, '[player] gets up close and personal to [opponent], pressing two fingers directly to the forehead and injecting chakra directly to the brain through the skull. Channeling the pent up grief of living the cold life of a real shinobi, [player] induces [opponent] into a brain dead state making them live through their own worst nightmares. [opponent] screams in maniacal laughter as their mind breaks down under the strain.', 'An intense taijutsu channeling the pain of a shinobi to cause a mental breakdown.', 'residual_damage', 100, 1),
(87, 'Volatile Excretion', 'ninjutsu', 1, 1, '2-7-3-9-6', 'None', 0, 2, 200, 100, 'projectile', 1, 'Using [gender2] chakra, [player] begins churing the contents of [gender2] stomach. After a few moments, the chakra is forced out of the anus creating a vial stench. [opponent] inhales some of the fumes and becomes practically imobilized. ', 'Let them smell you from a mile away.', 'speed_nerf', 10, 3),
(88, 'Illusionary Shadow', 'taijutsu', 4, 3.8, '100-30', 'None', 0, 2, 35000, 275, 'barrier', 4, '[player] sprints around causing illusionary shadows to appear and confuse [opponent].', 'Barrier - Afterimages created via fast movement speed become the targets for enemy attacks.', 'none', NULL, NULL),
(89, 'Earth Devastation  ', 'ninjutsu', 4, 8, '2-7-3-12', 'None', 0, 3, 10000000, 2500, 'physical', 0, '[player] slams his/her hands to the ground creating cracks in the Earth&#039;s surface causing fire to infuse with the stones to create a hard metallic projectile that hits [opponent] multiple times. ', 'Devastating earthquakes that crack the earth&#039;s surface to cause fire &amp; stone to form a steel projectile towards an oppnent ', 'residual_damage', 25, 5);

-- add missions
INSERT INTO `missions` (`mission_id`, `name`, `rank`, `mission_type`, `stages`, `money`) VALUES
(1, 'Special Request', 1, 1, '[{\"action_type\":\"search\",\"location_radius\":\"2\",\"description\":\"Find a Jounin&#039;s hidden stash of questionable books within [location_radius] squares of the village.\"}]', 40),
(2, 'Deliver Food', 1, 1, '[{\"action_type\":\"travel\",\"location_radius\":\"4\",\"description\":\"The Kage has sent you to deliver food to a small village in need at [action_data]\"}]', 30),
(3, 'Retrieve the pet Llama!', 1, 1, '[{\"action_type\":\"travel\",\"location_radius\":\"4\",\"description\":\"Retrieve the distraught farm owner&#039;s best friend at [action_data], This might require some climbing tools.\"},{\"action_type\":\"travel\",\"location_radius\":\"2\",\"description\":\"After rescuing the Llama from a massive cliff edge and being headbutted by the animal as well you must return him to his owner at [action_data]\"}]', 55),
(4, 'Form Team & Scout Area', 2, 1, '[{\"action_type\":\"travel\",\"location_radius\":\"1\",\"description\":\"Meet up with the team of scouts you have hand picked yourself at [action_data] outside of the village walls.\"},{\"action_type\":\"search\",\"location_radius\":\"3\",\"count\":\"3\",\"description\":\"Scout the surrounding [location_radius] squares outside your village and immediately report any suspicious activity that you come across. &#039;The Kage has given specific orders to not engage any hostile, this is purely a scouting mission&#039; you tell your team.\"}]', 125),
(5, 'Teambuilding Exercise', 1, 3, '[{\"action_type\":\"travel\",\"location_radius\":\"1\",\"count\":\"50\",\"description\":\"Nothing builds friendships like mutual suffering. Pick up trash around the village as a team\"}]', 50),
(6, 'Patrol Village Perimeter', 2, 1, '[{\"action_type\":\"search\",\"location_radius\":\"1\",\"count\":\"5\",\"description\":\"You have been tasked with patrolling the outer wall, ranging [location_radius] square around the village. In doing this you must look for any weaknesses and damages within the wall itself while also alerting to any suspicious activity just beyond the wall to the higher guard.\"}]', 75),
(7, 'Tactical Espionage', 3, 1, '[{\"action_type\":\"travel\",\"location_radius\":\"2\",\"count\":\"2\",\"description\":\"Travel to [action_data] just outside your village to pick up your equipment for the journey. This will include your unmarked stealth gear, a dossier on your mission and an emergency pouch full of stuff you need if you are caught and captured.. which for your sake I hope you are not.\"},{\"action_type\":\"travel\",\"location_radius\":\"18\",\"count\":\"3\",\"description\":\"One of the places to travel to is located at [action_data]. You should remember your training and be as careful as you can, using every stealth jutsu to your advantage. Do not hesitate to quickly and quietly take down any enemy threat, but only if necessary, leave as little evidence as possible.\"}]', 140),
(8, 'Study Clan Heritage', 1, 2, '[{\"action_type\":\"travel\",\"location_radius\":\"3\",\"count\":\"2\",\"description\":\"Travel to [action_data] with a seasoned member of your clan to learn more about the abilities and responsibilities that come with being a member of your clan. It may be boring but you may just end up learning a thing or two.\"}]', 50),
(9, 'Fight Club', 3, 5, '[{\"action_type\":\"travel\",\"location_radius\":\"2\",\"description\":\"Travel to [action_data] to begin the series of fights..\"},{\"action_type\":\"combat\",\"action_data\":\"14\",\"description\":\"Defeat your opponents.\"},{\"action_type\":\"combat\",\"action_data\":\"15\",\"description\":\"Defeat your opponents.\"}]', 100),
(10, 'Jonin Exam', 2, 4, '[{\"action_type\":\"search\",\"location_radius\":\"4\",\"description\":\"A ninja has escaped from your village, carrying a scroll of secret jutsu. Your task is to hunt down this ninja, apprehend them, and bring them in. Start by searching [location_radius] squares around your village for any signs of the ninja.\"},{\"action_type\":\"travel\",\"location_radius\":\"7\",\"description\":\"You found the shinobi meeting up with a ninja from another village, and learned they are going to rendezvous at [action_data]. Go intercept them and get the scroll back!\"},{\"action_type\":\"combat\",\"action_data\":\"14\",\"description\":\"You made it to the meeting but were intercepted by an enemy Shinobi. Defeat them before the rogue ninja escapes!\"},{\"action_type\":\"combat\",\"action_data\":\"15\",\"description\":\"You&#039;ve found the outlaw! Defeat him and take back the scroll.\"}]', 0),
(11, 'ANBU Ambush', 4, 5, '[{\"action_type\":\"travel\",\"location_radius\":\"4\",\"description\":\"Retrieve enemy intel at [action_data].\"},{\"action_type\":\"combat\",\"action_data\":\"17\",\"description\":\"An ambush! Try to survive!\"},{\"action_type\":\"combat\",\"action_data\":\"18\",\"description\":\"An ambush! Try to survive!\"}]', 200);


-- Add NPC opponents
INSERT INTO `ai_opponents` (`ai_id`, `rank`, `money`, `name`, `max_health`, `level`, `ninjutsu_skill`, `genjutsu_skill`, `taijutsu_skill`, `cast_speed`, `speed`, `strength`, `endurance`, `intelligence`, `willpower`, `moves`) VALUES
(1, 1, 20, 'Annoying Crow', 45, 1, 1.00, 1.00, 15.00, 5.00, 5.00, 10.00, 10.00, 10.00, 10.00, '[{\"battle_text\":\"[opponent] pecks at [player]&#039;s head with its beak.\",\"power\":\"1.2\",\"jutsu_type\":\"taijutsu\"}]'),
(2, 1, 35, 'Academy Bully', 180, 5, 10.00, 10.00, 70.00, 10.00, 70.00, 10.00, 10.00, 10.00, 10.00, '[{\"battle_text\":\"[opponent] repeatedly punches [player] in the gut after throwing sand in [gender2] eyes.\",\"power\":\"1.4\",\"jutsu_type\":\"taijutsu\"},{\"battle_text\":\"[opponent] delivers a kick filled with chakra to [player]&#039;s side.\",\"power\":\"1.4\",\"jutsu_type\":\"taijutsu\"}]'),
(3, 1, 45, 'Prodigy Student', 280, 10, 200.00, 25.00, 25.00, 75.00, 10.00, 50.00, 90.00, 10.00, 10.00, '[{\"battle_text\":\"[opponent] sweeps at [player]&#039;s legs, causing them to fall\",\"power\":\"1.9\",\"jutsu_type\":\"taijutsu\"},{\"battle_text\":\"[opponent] creates multiple clones that trick [player] before [gender] is struck from behind.\",\"power\":\"1.9\",\"jutsu_type\":\"taijutsu\"}]'),
(4, 2, 40, 'Academy Graduate', 280, 10, 100.00, 0.00, 100.00, 70.00, 70.00, 100.00, 100.00, 10.00, 10.00, '[{\"battle_text\":\"[opponent] uses a replacement technique and strikes [player] from behind.\",\"power\":\"2\",\"jutsu_type\":\"taijutsu\"},{\"battle_text\":\"[opponent] throws a smoke bomb at [player] before throwing a multitude of chakra soaked shuriken.\",\"power\":\"2\",\"jutsu_type\":\"ninjutsu\"}]'),
(5, 2, 50, 'Advanced Genin', 925, 16, 325.00, 325.00, 10.00, 125.00, 10.00, 0.00, 300.00, 100.00, 10.00, '[{\"battle_text\":\"[opponent] stomps their foot on the ground and launches a wave of earth spikes.\",\"power\":\"2.5\",\"jutsu_type\":\"ninjutsu\"},{\"battle_text\":\"[opponent] uses their bloodline to trap [player] in a gruesome genjutsu.\",\"power\":\"2.5\",\"jutsu_type\":\"genjutsu\"}]'),
(6, 2, 60, 'Weapon Fanatic', 1175, 18, 450.00, 10.00, 450.00, 150.00, 150.00, 0.00, 350.00, 10.00, 10.00, '[{\"battle_text\":\"[opponent] rushes forward and slices at [player] with two chakra infused Ninjato.\",\"power\":\"2.7\",\"jutsu_type\":\"taijutsu\"},{\"battle_text\":\"[opponent] sends [player] flying with a strike from their hammer.\",\"power\":\"2.7\",\"jutsu_type\":\"taijutsu\"}]'),
(13, 2, 40, 'Crafty Kunoichi', 675, 14, 250.00, 10.00, 250.00, 100.00, 100.00, 10.00, 200.00, 10.00, 10.00, '[{\"battle_text\":\"[opponent] uses a series of wires to suspend [player]&#039;s body in the air. Electricity slowly slides down the metal wiring till it comes in contact with [player]&#039;s body, sending a surge of negative energy though their system.\",\"power\":\"2.3\",\"jutsu_type\":\"ninjutsu\"},{\"battle_text\":\"[opponent] delivers a powerful blow to the back of [player]&#039;s knee causing them to kneel down on one leg before Crafty Kunoichi does a backflip and smashes her foot under [player]&#039;s chin, sending them backwards.\",\"power\":\"2.3\",\"jutsu_type\":\"taijutsu\"}]'),
(7, 3, 70, 'Furious Tiger', 2350, 23, 10.00, 850.00, 850.00, 10.00, 400.00, 10.00, 600.00, 300.00, 10.00, '[{\"battle_text\":\"[opponent] sinks its teeth into [player]&#039;s side, the sound of bones breaking is painfully audible\",\"power\":\"3.3\",\"jutsu_type\":\"taijutsu\"},{\"battle_text\":\"[opponent] eyes glimmer as they move at incredible speed, leaving a blue streak in their haste. The blue streak passes though [player] causing them to hunch over in sever pain, bleeding where the streak had passed.\",\"power\":\"3.3\",\"jutsu_type\":\"genjutsu\"}]'),
(8, 3, 100, 'Genin Trio', 7150, 35, 2550.00, 500.00, 2550.00, 1300.00, 1300.00, 10.00, 1200.00, 10.00, 10.00, '[{\"battle_text\":\"[opponent] attack as one, delivering a well timed kunai to [player]&#039;s limbs before closing in proximity, two slam their elbow into [player]&#039;s gut and back, the third comes up from under the ground with a singular punch to the chin.\",\"power\":\"3.7\",\"jutsu_type\":\"taijutsu\"},{\"battle_text\":\"[opponent] each cast a element based jutsu, one wind, another fire and the last lightning. the resulting jutsu combine and effectively cause great damage to the environment and [player].\",\"power\":\"3.8\",\"jutsu_type\":\"ninjutsu\"}]'),
(9, 3, 125, 'Novice Chuunin', 12250, 40, 4500.00, 100.00, 4500.00, 2100.00, 2100.00, 0.00, 2000.00, 10.00, 10.00, '[{\"battle_text\":\"[opponent] fills calthrops with chakra and throws them across the ground in front of [player], Anbu Novice lets lose a massive amount of shuriken which come in contact with the electrical chakra in the calthrops, destroying [player]&#039;s nerves with ease.\",\"power\":\"4.2\",\"jutsu_type\":\"ninjutsu\"},{\"battle_text\":\"[opponent] disables [player]&#039;s mobility by slashing with their Ninjato at the crucial areas. When [player] retaliates a Substitution jutsu is used by Anbu Novice before a piece of cold steel is pressed against [player]&#039;s neck, now the victim of an assassination jutsu.\",\"power\":\"4.3\",\"jutsu_type\":\"taijutsu\"}]'),
(10, 100, 10, 'Talented Genin', 1425, 20, 700.00, 150.00, 700.00, 400.00, 400.00, 10.00, 300.00, 10.00, 10.00, '[{\"battle_text\":\"[opponent] pulling a wire that runs across the ground triggering a spring trap that fires dozens of kunai at [player].\",\"power\":\"3\",\"jutsu_type\":\"taijutsu\"},{\"battle_text\":\"[opponent] performs a series of hand signs before spitting out a dark violet colored acid that eats [player]&#039;s tissue at an alarming rate.\",\"power\":\"3\",\"jutsu_type\":\"ninjutsu\"}]'),
(11, 100, 10, 'Elite Contender', 2000, 25, 1400.00, 200.00, 1400.00, 600.00, 600.00, 200.00, 500.00, 100.00, 10.00, '[{\"battle_text\":\"[opponent] uses their extreme talent over taijutsu to deliver several precise blows, a spinning kick to the head and finally strike to [player]&#039;s left rib with a metal stave.\",\"power\":\"2.8\",\"jutsu_type\":\"taijutsu\"},{\"battle_text\":\"[opponent] bites their hand causing it to bleed and begins to perform handseals, using their bloodline to mix the blood with a firey jutsu they spew out, Which results in several spears of green flames to consume [player]\",\"power\":\"2.7\",\"jutsu_type\":\"ninjutsu\"}]'),
(12, 3, 85, 'Jounin&#039;s Shadow Clone', 5150, 30, 1600.00, 500.00, 1600.00, 1000.00, 1000.00, 10.00, 800.00, 500.00, 10.00, '[{\"battle_text\":\"[opponent] performs and insane amount of hand signs before firing out a barrage of fire bullets that couple with a giant snake made purely out of water, when the two come in contact right by [player], a searing haze of steam is created, boiling [player] alive.\",\"power\":\"3.5\",\"jutsu_type\":\"ninjutsu\"},{\"battle_text\":\"[opponent] pulls out two long jagged edged butcher knife style blades, using the momentum of the blades Jounin&#039;s Shadow Clone spins violently like a tornado. The resulting flurry tears [player] to shreds.\",\"power\":\"3.5\",\"jutsu_type\":\"taijutsu\"}]'),
(14, 4, 100, 'Chuunin Expert', 10000, 40, 2500.00, 1500.00, 2500.00, 1500.00, 1500.00, 0.00, 0.00, 1000.00, 500.00, '[{\"battle_text\":\"[opponent] strings together a combo of swift weapon attacks, ferociously attacking [player]\",\"power\":\"4.0\",\"jutsu_type\":\"ninjutsu\"},{\"battle_text\":\"[opponent] unleashes a fireball barrage at [player].\",\"power\":\"4.0\",\"jutsu_type\":\"taijutsu\"}]'),
(15, 4, 115, 'Village Outlaw', 12000, 45, 10000.00, 5000.00, 10000.00, 5000.00, 5000.00, 0.00, 0.00, 1000.00, 500.00, '[{\"battle_text\":\"[opponent] launches a barrage of shuriken at [player]       while player is distracted fending of shuriken Village Outlaw       sends a kunai flying [player] instinctively deflects kunai behind      him but fails to notice the explosive tag attached to the kunai. The tag explodes in a fiery blast.\",\"power\":\"4.0\",\"jutsu_type\":\"taijutsu\"},{\"battle_text\":\"[opponent] launches a barrage of fireballs that rain down from the sky at you.\",\"power\":\"4.0\",\"jutsu_type\":\"ninjutsu\"}]'),
(16, 4, 140, 'Rogue Samurai', 25300, 55, 20000.00, 10000.00, 20000.00, 10000.00, 10000.00, 0.00, 0.00, 5000.00, 750.00, '[{\"battle_text\":\"[opponent] rushes at [player] and feigns a slash with his sword while puling out a second katana with his other hand. Rogue Samurai swings the second katana in a vicious surprise strike at [player]&#039;s lowered defenses.\",\"power\":\"4.2\",\"jutsu_type\":\"taijutsu\"}]'),
(17, 4, 175, 'Enemy ANBU', 35800, 65, 25000.00, 15000.00, 25000.00, 13000.00, 13000.00, 0.00, 0.00, 10000.00, 1000.00, '[{\"battle_text\":\"[opponent] traps [player] in a genjutsu that obscurs [gender2] vision. Suddenly out of nowhere lightning bolts come shooting through the air at [player] and pierce through [gender2].\",\"power\":\"4.6\",\"jutsu_type\":\"ninjutsu\"}]'),
(18, 4, 200, 'ANBU Captain', 40000, 70, 20000.00, 15000.00, 30000.00, 30000.00, 15000.00, 0.00, 0.00, 5000.00, 1500.00, '[{\"battle_text\":\"[opponent] performs a series of hand signs in a blur, and rain begins to drench the battlefield, so heavily that it becomes difficult to see. As [player] looks at [opponent], suddenly [gender] feels a blade in their back and sees the shadow clone of [gender2] opponent fading away.\",\"power\":\"4.8\",\"jutsu_type\":\"taijutsu\"},{\"battle_text\":\"[opponent] forms several hand signs faster than the eye can follow. As the final sign is weaved several puddles appear around the battlefield. [opponent] touches one with his foot and sends a blast of chakra into it, causing a jet of water to shoot out of another puddle and pierce [player] through the stomach.\",\"power\":\"4.8\",\"jutsu_type\":\"ninjutsu\"}]'),
(19, 4, 10, 'Tetsu Yoki', 10000, 15, 3500.00, 4000.00, 2000.00, 2500.00, 8000.00, 0.00, 0.00, 4500.00, 2000.00, '[{\"battle_text\":\"Throws razor sharp leaf at [player]&#039;s head\",\"power\":\"2.0\",\"jutsu_type\":\"taijutsu\"},{\"battle_text\":\"[opponent] preform several hand signs and [player] begins to slip into a deep sleep\",\"power\":\"2.2\",\"jutsu_type\":\"genjutsu\"}]'),
(20, 4, 7800, 'Zeus', 81920, 99, 48000.00, 28000.00, 36000.00, 35000.00, 20000.00, 0.00, 0.00, 7500.00, 3000.00, '[{\"battle_text\":\"[opponent] smites [player].\",\"power\":\"13\",\"jutsu_type\":\"ninjutsu\"},{\"battle_text\":\"Something else about lightning.\",\"power\":\"11\",\"jutsu_type\":\"ninjutsu\"}]');


-- Add clans
INSERT INTO `clans` (`clan_id`, `village`, `name`, `bloodline_only`, `boost`, `boost_amount`, `points`, `leader`, `elder_1`, `elder_2`, `challenge_1`, `logo`, `motto`, `info`) VALUES
(1, 'Leaf', 'Kobayashi', 1, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(2, 'Sand', 'Tsukino', 1, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(3, 'Leaf', 'Himura', 1, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(4, 'Leaf', 'Sugi', 0, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(8, 'Stone', 'Kiku', 0, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(5, 'Cloud', 'Yokoyama', 0, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(6, 'Stone', 'Haniwa', 1, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(7, 'Cloud', 'Aozora', 1, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(9, 'Mist', 'Hashi', 0, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(10, 'Sand', 'Iijima', 0, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(11, 'Mist', 'Mizumaki', 1, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(12, 'Leaf', 'Tsuruya', 1, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(13, 'Stone', 'Yoshitomi', 1, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(14, 'Sand', 'Kurosawa', 1, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(15, 'Cloud', 'Tomioka', 1, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(16, 'Leaf', 'Joshuya', 1, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(17, 'Mist', 'Koizumi', 0, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(18, 'Sand', 'Kasuse', 0, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(19, 'Stone', 'Momotami', 0, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(20, 'Cloud', 'Uesugi', 0, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(21, 'Leaf', 'Nakano', 0, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(22, 'Mist', 'Kurushimi', 1, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(23, 'Leaf', 'Shukumei', 1, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(24, 'Cloud', 'Kibou', 1, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(25, 'Stone', 'Hosoku', 1, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(26, 'Sand', 'Zetsubou', 1, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(27, 'Mist', 'Haninozuka', 1, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(28, 'Mist', 'Maigo', 1, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(29, 'Leaf', 'Baransu', 1, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(30, 'Stone', 'Hagane', 1, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(31, 'Mist', 'Koori', 1, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(32, 'Sand', 'Chikara', 1, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(33, 'Cloud', 'Fumetsu', 1, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(34, 'Sand', 'Kokomotsu', 1, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(35, 'Cloud', 'Misaki', 1, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', ''),
(36, 'Mist', 'Mitzuku', 0, '', 0.00, 0, 0, 0, 0, '0', './images/default_avatar.png', '', '');

-- Add system storage
CREATE TABLE `system_storage` (
    `id` INT NOT NULL  AUTO_INCREMENT PRIMARY KEY,
    `global_message` TEXT
);
INSERT INTO `system_storage` (`id`, `global_message`) VALUES (NULL, NULL);


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
