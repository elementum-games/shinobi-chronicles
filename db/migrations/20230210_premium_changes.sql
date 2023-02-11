START TRANSACTION;

ALTER TABLE premium_credit_exchange
    ADD `offer_type` INT(2)  DEFaULT 1 NOT NULL;

COMMIT;