START TRANSACTION;

-- Alter `users`
ALTER TABLE `users` add column `isMarried` bit(1) NOT NULL DEFAULT 0;
ALTER TABLE `users` add column `marriage_id` int(11) NOT NULL DEFAULT 0;
ALTER TABLE `users` add column `spouse_username` text NOT NULL DEFAULT '';
ALTER TABLE `users` add column `proposalInbox` text NOT NULL;


--
-- Table structure for table `marriage`
--
CREATE TABLE `marriage` (
  `marriage_id` int(11) NOT NULL AUTO_INCREMENT,
  `user1_id` int(11) NOT NULL DEFAULT -1,
  `user2_id` int(11) NOT NULL DEFAULT -1,
  `boost_amount` float NOT NULL DEFAULT 0.15,
  `proposal_date` date NOT NULL DEFAULT current_timestamp(),
   PRIMARY KEY (`marriage_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

UPDATE `system_storage` SET `database_version`='20211001_0747' LIMIT 1;

COMMIT;
