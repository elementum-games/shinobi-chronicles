START TRANSACTION;

create table currency_logs (
    `id` INT NOT NULL AUTO_INCREMENT primary key,
    `character_id` INT NOT NULL,
    `currency_type` VARCHAR(128) NOT NULL,
    `previous_balance` INT NOT NULL,
    `new_balance` INT NOT NULL,
    `transaction_amount` INT NOT NULL,
    `transaction_description` text
);

UPDATE `system_storage` SET `database_version`='20230112_currency_logs' LIMIT 1;

COMMIT;