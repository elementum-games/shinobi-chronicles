<?php
session_start();
/****************************************************************************
 *                           Reputation Weekly Cron                         *
 * **************************************************************************
 *   This should be processed weekly on Fridays at 23:59 server time.    *
 *                                                                          *
 *    The intent of this script is to reset the weekly gained reputation,   *
 *  decay any current reputation (decrease positive/increase negative) reps *
 *  for all SC users. Once clan/kage requirements are in place, this script *
 *    will also remove players with insufficient reputation from office.    *
 ****************************************************************************/

require_once __DIR__ . '/../classes/System.php';
require_once __DIR__ . '/../classes/Village.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/inbox/Inbox.php';
require_once __DIR__ . '/../classes/UserReputation.php';

$system = System::initialize(load_layout: true);
$system->db->connect();

if(!$system->db->con) {
    throw new RuntimeException("Error connecting to DB!");
}

$player = false;
if(isset($_SESSION['user_id'])) {
    $player = User::loadFromId($system, $_SESSION['user_id']);
    $player->loadData();
    $production_key = "RuN ON ProDUCtIon";

    if($player->staff_manager->isHeadAdmin()) {
        if(isset($_GET['run_script']) && $_GET['run_script'] == 'true') {
            if($system->environment === System::ENVIRONMENT_PROD) {
                if (!isset($_POST['confirm']) || $_POST['confirm'] !== $production_key) {
                    echo "WARNING PRODUCTION ENVIRONMENT! Confirm you would like to run this adhoc on <b>PRODUCTION ENVIRONMENT</b>!!!<br />
                To confirm, type the following (case sensitive): $production_key<br />
                <form action='{$system->router->base_url}/cron_jobs/reputation_cron.php?run_script=true' method='post'>
                    <input type='text' name='confirm' /><input type='submit' value='Run Script' />
                </form>";
                }
                else {
                    weeklyCron($system);
                    $player->staff_manager->staffLog(StaffManager::STAFF_LOG_ADMIN,
                        "{$player->user_name}({$player->user_id}) manually ran reputation cron.");
                    echo "Script ran.";
                }
            }
            else {
                weeklyCron($system);
                $player->staff_manager->staffLog(StaffManager::STAFF_LOG_ADMIN,
                    "{$player->user_name}({$player->user_id}) manually ran reputation cron.");
                echo "Script ran.";
            }
        }
        else {
            echo "You can run the reputation cron script Adhoc. This is not reversible. <a href='{$system->router->base_url}/cron_jobs/reputation_cron.php?run_script=true'>Run</a>.";
        }
    }
}
else {
    // Check for verify to run cron
    $run_ok = false;
    if(php_sapi_name() == 'cli') {
        $run_ok = true;
    }
    else if($_SERVER['REMOTE_ADDR'] == $_SERVER['SERVER_ADDR']) {
        $run_ok = true;
    }

    if($run_ok) {
        weeklyCron($system);
        $system->log('cron', 'Weekly Reputation', "Weekly reputation decay has been processed.");
    }
    else {
        $system->log('cron', 'Invalid access', "Attempted access by " . $_SERVER['REMOTE_ADDR']);
    }
}

function weeklyCron(System $system, $debug = false): void {
    $queries = [];

    $queries[] = "UPDATE `users` SET `weekly_pve_rep`=0, `weekly_war_rep`=0, `weekly_pvp_rep`=0 WHERE 1;";

    foreach(UserReputation::$VillageRep as $RANK_INT => $RANK) {
        if($RANK_INT == 1) {
            // Disable for rank 1
            continue;
        }

        $next_rank_where = "";
        if($RANK_INT < sizeof(UserReputation::$VillageRep)) {
            $RANK2_INT = $RANK_INT+1;
            $RANK2 = UserReputation::$VillageRep[$RANK2_INT];
            $next_rank_where = " AND `village_rep` < " . $RANK2['min_rep'];
        }

        $queries [] = "UPDATE `users` SET 
            `village_rep`=`village_rep`- " . $RANK['base_decay'] . " 
            WHERE `village_rep` >= " . $RANK['min_rep'] . $next_rank_where;
    }

    foreach($queries as $query) {
        if($debug) {
            echo $query . "<br />";
        }
        else {
            $system->db->query("LOCK TABLES `users` WRITE;");
            $system->db->query($query);
            $system->db->query("UNLOCK TABLES;");
        }
    }

}