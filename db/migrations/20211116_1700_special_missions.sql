START TRANSACTION;


--
-- Table structure for table `special_missions`
--

CREATE TABLE `special_missions` (
  `mission_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `difficulty` varchar(255) DEFAULT NULL,
  `start_time` int(11) NOT NULL,
  `end_time` int(11) NOT NULL DEFAULT 0,
  `progress` int(11) NOT NULL DEFAULT 0,
  `target` varchar(255) DEFAULT NULL,
  `log` text NOT NULL,
  `reward` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `special_missions`
--
ALTER TABLE `special_missions`
  ADD PRIMARY KEY (`mission_id`);

--
-- AUTO_INCREMENT for table `special_missions`
--
ALTER TABLE `special_missions`
  MODIFY `mission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=227;

ALTER TABLE `users` 
  ADD COLUMN `special_mission` INT(11) DEFAULT 0;

COMMIT;
