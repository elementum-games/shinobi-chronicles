START TRANSACTION;

ALTER TABLE `users` ADD `chat_color` VARCHAR(100) NOT NULL AFTER `forbidden_seal`;
ALTER TABLE `chat` CHANGE `user_color` `user_color` VARCHAR(50) NOT NULL;

COMMIT;
