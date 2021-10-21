START TRANSACTION;

--
-- Table structure for table `support_request`
--

CREATE TABLE `support_request` (
  `support_id` INT PRIMARY KEY AUTO_INCREMENT,
  `support_type` VARCHAR(75),
  `guest_ticket` INT(1) NULL,
  `user_id` INT,
  `user_name` VARCHAR(40),
  `email` VARCHAR(100),
  `ip_address` VARCHAR(64),
  `subject` VARCHAR(70),
  `message` TEXT,
  `time` INT,
  `updated` INT,
  `assigned_to` INT,
  `admin_name` VARCHAR(75),
  `admin_respond_by` INT,
  `open` INT(1),
  `admin_response` INT(1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `support_request_responses` (
  `response_id` INT PRIMARY KEY AUTO_INCREMENT,
  `support_id` INT,
  `guest_ticket` INT(1) NULL,
  `user_id` INT,
  `user_name` VARCHAR(40),
  `email` VARCHAR(100),
  `ip_address` VARCHAR(64),
  `message` TEXT,
  `time` INT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `support_request`
  ADD `admin_response` INT(1);

TRUNCATE `support_request`;

INSERT INTO `support_request_responses` ( `support_id`, `time`, `user_id`, `user_name`, `message`, `ip_address`, ) VALUES ( '1', '1634152077', '1', 'Hitori', '::1', 'This are response to all the stuffs.' )

COMMIT;