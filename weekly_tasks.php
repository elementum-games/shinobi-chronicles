<?php

// Don't allow users to trigger these tasks from the web
if(php_sapi_name() !== "cli") {
   echo "Invalid environment!";
   exit;
}

require_once __DIR__ . "/classes/System.php";
$system = new System();

function clean_clan_position_holders()
{
    global $system;

    $max_idle_time = time() - System::MAX_CLAN_HOLDER_IDLE_TIME;

    $result = $system->query("SELECT clans.clan_id, clans.leader, clans.elder_1, clans.elder_2, users.user_name, users.user_id, users.last_login, users.clan_office, users.staff_level FROM clans
        JOIN users ON clans.leader = users.user_id OR clans.elder_1 = users.user_id OR clans.elder_2 = users.user_id
        WHERE clans.leader > 0 OR clans.elder_1 > 0 OR clans.elder_2 > 0;"
    );

    $expired_holders = array();

    if($system->db_last_num_rows > 0) {
        while($row = $system->db_fetch($result)) {
            if ($row["last_login"] <= $max_idle_time && $row['staff_level'] < User::STAFF_MODERATOR)
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
            $log_content = json_encode($expired_holders);
            $log_title = "Inactive Clan Holders Removed: " . count($expired_holders);

            $system->query("INSERT INTO `logs` (`log_type`, `log_title`, `log_time`, `log_contents`)
			VALUES ('clean_clan_holders', '$log_title', " . time() . ", '$log_content')");
            //$system->log('clean_clan_holders', "Inactive Clan Holders Removed: " . count($expired_holders), $expired_holders);
            return "Removed " . count($expired_holders) . " inactive users.";
        }
        else return false;
    }
    catch (Exception $e)
    {
        $system->log('clean_clan_holders', 'Failure Occurred', $e);
        return $e;
    }

    return true;
}

clean_clan_position_holders();
