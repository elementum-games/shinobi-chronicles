-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 15, 2023 at 03:39 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sc`
--

-- --------------------------------------------------------

--
-- Table structure for table `convos`
--

CREATE TABLE `convos` (
  `convo_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `active` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `convos_alerts`
--

CREATE TABLE `convos_alerts` (
  `alert_id` int(11) NOT NULL,
  `system_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `target_id` int(11) NOT NULL,
  `message` varchar(255) DEFAULT NULL,
  `time` int(11) NOT NULL,
  `unread` int(11) NOT NULL DEFAULT 1,
  `alert_deleted` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `convos_messages`
--

CREATE TABLE `convos_messages` (
  `message_id` int(11) NOT NULL,
  `convo_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `time` int(11) NOT NULL,
  `deleted` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `convos_users`
--

CREATE TABLE `convos_users` (
  `entry_id` int(11) NOT NULL,
  `convo_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `last_read` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `convos`
--
ALTER TABLE `convos`
  ADD PRIMARY KEY (`convo_id`);

--
-- Indexes for table `convos_alerts`
--
ALTER TABLE `convos_alerts`
  ADD PRIMARY KEY (`alert_id`);

--
-- Indexes for table `convos_messages`
--
ALTER TABLE `convos_messages`
  ADD PRIMARY KEY (`message_id`);

--
-- Indexes for table `convos_users`
--
ALTER TABLE `convos_users`
  ADD PRIMARY KEY (`entry_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `convos`
--
ALTER TABLE `convos`
  MODIFY `convo_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `convos_alerts`
--
ALTER TABLE `convos_alerts`
  MODIFY `alert_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `convos_messages`
--
ALTER TABLE `convos_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `convos_users`
--
ALTER TABLE `convos_users`
  MODIFY `entry_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
