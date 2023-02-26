CREATE TABLE `multi_accounts` (
    `multi_id` int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `status` varchar(75) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;