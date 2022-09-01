<?php

require_once __DIR__ . "/classes/System.php";
$system = new System();

function clean_clan_position_holders()
{
    global $system;

    $max_idle_time = time(); //- System::MAX_CLAN_HOLDER_IDLE_TIME;

    $result = $system->query("SELECT clans.clan_id, clans.leader, clans.elder_1, clans.elder_2, users.user_name, users.user_id, users.last_login, users.clan_office FROM clans
        JOIN users ON clans.leader = users.user_id OR clans.elder_1 = users.user_id OR clans.elder_2 = users.user_id
        WHERE clans.leader > 0 OR clans.elder_1 > 0 OR clans.elder_2 > 0;"
    );

    $expired_holders = array();

    if($system->db_last_num_rows > 0) {
        while($row = $system->db_fetch($result)) {
            if ($row["last_login"] <= $max_idle_time)
            {
                $expired_holders[$row['user_id']] = $row;
            }
            else continue;
        }
    }

    $positions = array (
        1 => 'leader',
        2 => 'elder_1',
        3 => 'elder_2',
    );

    try {
        if (count($expired_holders) > 0)
        {
            foreach ($expired_holders as $expired_user)
            {
                $position = $positions[$expired_user['clan_office']];
                $system->query("UPDATE clans SET {$position} = 0 WHERE clan_id = {$expired_user['clan_id']} LIMIT 1");
                $system->query("UPDATE users SET clan_office=0 WHERE user_id = {$expired_user['user_id']} LIMIT 1");

            }
            $system->log('clean_clan_holders', "Inactive Clan Holders Removed: " . count($expired_holders), $expired_holders);
            return "Removed " . count($expired_holders) . " inactive users.";
        }
        else return false;
    }
    catch (Exception $e)
    {
        $system->log('clean_clan_holders', 'Failure Occurred', $e);
    }

    return true;
}

clean_clan_position_holders();
