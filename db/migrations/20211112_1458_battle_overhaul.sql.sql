START TRANSACTION;

update users set `battle_id`=0, `exam_stage`=0, `mission_id`=0 where 1;

drop table if exists battles;

create table battles
(
    battle_id int auto_increment primary key,
    battle_type smallint not null,
    start_time int default 0 null,
    turn_time int null,
    turn_count smallint not null default 0,
    winner varchar(32) null,
    player1 varchar(64) not null,
    player2 varchar(64) not null,
    fighter_health text not null,
    fighter_actions text not null,
    field text not null,
    active_effects text not null,
    active_genjutsu text not null,
    jutsu_cooldowns text not null,
    fighter_jutsu_used text not null
)
engine=InnoDB charset=latin1;

create table battle_logs (
    id int auto_increment primary key,
    battle_id int not null,
    turn_number smallint not null,
    content text not null
);

-- most common use case - in a battle, want to see the latest turn (or X turns)
create index `battle_logs_latest_turn` on `battle_logs` (`battle_id` DESC, `turn_number` DESC);

-- this is mostly so we can safely do "insert ... on duplicate key update"
create unique index `battle_logs_turn` on battle_logs(`battle_id`, `turn_number`);


UPDATE `system_storage` SET `database_version`='20211112_1458' LIMIT 1;

COMMIT;
