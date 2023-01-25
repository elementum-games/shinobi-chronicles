-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 26, 2023 at 12:28 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- Table structure for table `maps`
--

CREATE TABLE `maps` (
  `map_id` int(11) NOT NULL,
  `map_name` varchar(255) NOT NULL,
  `container_height` int(11) NOT NULL,
  `container_width` int(11) NOT NULL,
  `map_height` int(11) NOT NULL,
  `map_width` int(11) NOT NULL,
  `tile_height` int(11) NOT NULL,
  `tile_width` int(11) NOT NULL,
  `background` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maps`
--

INSERT INTO `maps` (`map_id`, `map_name`, `container_height`, `container_width`, `map_height`, `map_width`, `tile_height`, `tile_width`, `background`) VALUES
(1, 'Earth', 363, 330, 12, 18, 33, 33, '/images/travel_map.png'),
(2, 'Stone Village', 165, 165, 5, 5, 33, 33, '');

-- --------------------------------------------------------

--
-- Table structure for table `maps_locations`
--

CREATE TABLE `maps_locations` (
  `location_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `map_id` int(11) NOT NULL,
  `x` int(11) NOT NULL,
  `y` int(11) NOT NULL,
  `background_image` varchar(255) DEFAULT NULL,
  `background_color` varchar(11) DEFAULT NULL,
  `pvp_allowed` int(11) NOT NULL DEFAULT 1,
  `ai_allowed` int(11) NOT NULL DEFAULT 1,
  `regen` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maps_locations`
--

INSERT INTO `maps_locations` (`location_id`, `name`, `map_id`, `x`, `y`, `background_image`, `background_color`, `pvp_allowed`, `ai_allowed`, `regen`) VALUES
(1, 'Underground Colosseum', 1, 11, 9, '/images/map/locations/undergroundcolosseum.png', 'FF00007E', 0, 0, 0),
(2, 'Font of Vitality', 1, 10, 1, '/images/map/locations/fontofvitality.png', '38FF007E', 1, 1, 200),
(3, 'Stone', 1, 5, 3, '/images/village_icons/stone.png', '0000009f', 0, 0, 50),
(4, 'Cloud', 1, 17, 2, '/images/village_icons/cloud.png', '0000009f', 0, 0, 50),
(5, 'Leaf', 1, 9, 6, '/images/village_icons/leaf.png', '0000009f', 0, 0, 50),
(6, 'Sand', 1, 3, 8, '/images/village_icons/sand.png', '0000009f', 0, 0, 50),
(7, 'Mist', 1, 16, 10, '/images/village_icons/mist.png', '0000009f', 0, 0, 50);

-- --------------------------------------------------------

--
-- Table structure for table `maps_portals`
--

CREATE TABLE `maps_portals` (
  `portal_id` int(11) NOT NULL,
  `from_id` int(11) NOT NULL,
  `to_id` int(11) NOT NULL,
  `entrance_x` int(11) NOT NULL,
  `entrance_y` int(11) NOT NULL,
  `portal_text` varchar(255) NOT NULL,
  `entrance_name` varchar(255) NOT NULL,
  `exit_x` int(11) NOT NULL,
  `exit_y` int(11) NOT NULL,
  `active` int(11) NOT NULL DEFAULT 1,
  `whitelist` varchar(255) NOT NULL DEFAULT '[]'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maps_portals`
--

INSERT INTO `maps_portals` (`portal_id`, `from_id`, `to_id`, `entrance_x`, `entrance_y`, `portal_text`, `entrance_name`, `exit_x`, `exit_y`, `active`, `whitelist`) VALUES
(1, 1, 2, 5, 4, 'Enter Stone Village', 'Stone Village', 1, 1, 0, 'Mist,Leaf, Stone'),
(2, 2, 1, 1, 1, 'Exit Stone Village', 'Stone Village Outskirts', 5, 4, 0, 'Mist,Leaf, Stone');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `maps`
--
ALTER TABLE `maps`
  ADD PRIMARY KEY (`map_id`);

--
-- Indexes for table `maps_locations`
--
ALTER TABLE `maps_locations`
  ADD PRIMARY KEY (`location_id`);

--
-- Indexes for table `maps_portals`
--
ALTER TABLE `maps_portals`
  ADD PRIMARY KEY (`portal_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `maps`
--
ALTER TABLE `maps`
  MODIFY `map_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `maps_locations`
--
ALTER TABLE `maps_locations`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `maps_portals`
--
ALTER TABLE `maps_portals`
  MODIFY `portal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

UPDATE `users`
  SET `location` = concat(`location`, '.1');

UPDATE `villages`
  SET `location` = concat(`location`, '.1');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
