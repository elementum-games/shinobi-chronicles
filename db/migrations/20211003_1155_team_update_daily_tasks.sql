START TRANSACTION;

--
-- Table structure for table `daily_tasks`
--

CREATE TABLE `daily_tasks` (
  `user_id` int(11) NOT NULL,
  `tasks` text CHARACTER SET latin1 NOT NULL,
  `last_reset` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for table `daily_tasks`
--
ALTER TABLE `daily_tasks`
  ADD PRIMARY KEY (`user_id`);

/* ADDING BOOST_TIME TO TEAM */

ALTER TABLE `teams` 
  ADD `boost_time` INT(11) NOT NULL DEFAULT 0,
  MODIFY `boost_amount` INT(11) NOT NULL DEFAULT 0;

UPDATE `system_storage` SET `database_version`='2021003_1155' LIMIT 1;
  
COMMIT;