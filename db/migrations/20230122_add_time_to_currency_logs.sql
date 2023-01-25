START TRANSACTION;

alter table currency_logs
    add transaction_time int default 1577916956 not null;

UPDATE `system_storage` SET `database_version`='20230122_add_time_to_currency_logs' LIMIT 1;

COMMIT;