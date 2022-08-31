START TRANSACTION;

DROP PROCEDURE IF EXISTS SP_kick_inactive_clan_position_holders;

create procedure SP_kick_inactive_clan_position_holders(IN max_idle_time INT)
BEGIN
    DECLARE cm_clan_id INT;
    DECLARE cm_clan_leader INT;
    DECLARE cm_clan_elder_1 INT;
    DECLARE cm_clan_elder_2 INT;
    DECLARE cm_user_name VARCHAR(40);
    DECLARE cm_user_id INT;
    DECLARE cm_last_login INT;
    DECLARE cm_clan_office INT;

    DECLARE done INT DEFAULT FALSE;

    DECLARE cursor_clan_member CURSOR FOR select clans.clan_id, clans.leader, clans.elder_1, clans.elder_2, users.user_name, users.user_id, users.last_login, users.clan_office FROM clans
    JOIN users ON clans.leader = users.user_id OR clans.elder_1 = users.user_id OR clans.elder_2 = users.user_id
    WHERE clans.leader > 0 OR clans.elder_1 > 0 OR clans.elder_2 > 0;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = True;

    OPEN cursor_clan_member;
    process_clan_member: LOOP
        FETCH cursor_clan_member INTO cm_clan_id, cm_clan_leader, cm_clan_elder_1, cm_clan_elder_2, cm_user_name, cm_user_id, cm_last_login, cm_clan_office;
        IF done THEN
            LEAVE process_clan_member;
        end if;

        IF UNIX_TIMESTAMP(max_idle_time) > cm_last_login THEN
            set @cm_clan_office = ELT(cm_clan_office, 'leader','elder_1','elder_2');
            set @cm_clan_id = cm_clan_id;
            set @cm_user_id = cm_user_id;

            set @clan_update_query = CONCAT('UPDATE clans SET ',@cm_clan_office,' = 0 WHERE clan_id = ? LIMIT 1');
            PREPARE clan_update FROM @clan_update_query;
            EXECUTE clan_update USING @cm_clan_id;

            PREPARE user_update FROM 'UPDATE users SET clan_office=0 WHERE user_id = ? LIMIT 1';
            EXECUTE user_update USING @cm_user_id;

            DEALLOCATE PREPARE clan_update;
            DEALLOCATE PREPARE user_update;

        end if;
    end loop;
    CLOSE cursor_clan_member;
end;

COMMIT;