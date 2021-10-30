START TRANSACTION;

--
-- Table structure for table `support_request`
--

CREATE TABLE `support_request` (
  `support_id` INT PRIMARY KEY AUTO_INCREMENT,
  `support_type` VARCHAR(75),
  `support_key` VARCHAR(75),
  `user_id` INT,
  `user_name` VARCHAR(40),
  `email` VARCHAR(100),
  `ip_address` VARCHAR(64),
  `subject` VARCHAR(70),
  `message` TEXT,
  `time` INT,
  `updated` INT,
  `open` INT(1) DEFAULT 1,
  `admin_response` INT(1) DEFAULT 0,
  `premium` INT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `support_request_responses`
--

CREATE TABLE `support_request_responses` (
  `response_id` INT PRIMARY KEY AUTO_INCREMENT,
  `support_id` INT,
  `user_id` INT,
  `user_name` VARCHAR(40),
  `email` VARCHAR(100),
  `ip_address` VARCHAR(64),
  `message` TEXT,
  `time` INT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- ADD SUPPORT LEVELS TO USERS
--
ALTER TABLE `users` ADD `support_level` INT(1) DEFAULT 0 AFTER `staff_level`;

--
-- ADD HITORI & LSM AS SUPPORT ADMINS
--
ALTER TABLE `user` SET `support_level`='5' WHERE `user_id` IN(1, 254) LIMIT 2;

COMMIT;