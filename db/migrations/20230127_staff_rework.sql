CREATE TABLE `staff_logs` (
    `log_id` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `time` int(11) NOT NULL,
    `type` varchar(75) DEFAULT NULL,
    `content` TEXT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `user_record` (
    `record_id` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `staff_id` int(11) NOT NULL,
    `staff_name` varchar(75) NOT NULL,
    `user_id` int(11) NOT NULL,
    `record_type` varchar(100) NOT NULL,
    `time` int(11) NOT NULL,
    `data` text NOT NULL,
    `deleted` INT(2) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `official_warnings` (
    `warning_id` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `staff_id` int(11) NOT NULL,
    `staff_name` varchar(75) NOT NULL,
    `user_id` int(11) NOT NULL,
    `time` int(11) NOT NULL,
    `data` text NOT NULL,
    `viewed` INT(2) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `users`
    ADD `ban_data` TEXT DEFAULT NULL AFTER `last_active`;