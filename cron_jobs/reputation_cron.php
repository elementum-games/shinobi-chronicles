<?php
session_start();
/****************************************************************************
 *                           Reputation Weekly Cron                         *
 * **************************************************************************
 *   This should be processed weekly on Wednesdays at 00:00 server time.    *
 *                                                                          *
 *    The intent of this script is to reset the weekly gained reputation,   *
 *  decay any current reputation (decrease positive/increase negative) reps *
 *  for all SC users. Once clan/kage requirements are in place, this script *
 *    will also remove players with insufficient reputation from office.    *
 ****************************************************************************/

require_once __DIR__ . '/../classes/System.php';
require_once __DIR__ . '/../classes/Village.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Mission.php';
require_once __DIR__ . '/../classes/Bloodline.php';
require_once __DIR__ . '/../classes/Reputation.php';
require_once __DIR__ . '/../classes/travel/TravelManager.php';

$system = new System();
$system->db->connect();

if(!$system->db->con) {
    throw new RuntimeException("Error connecting to DB!");
}

$player = false;
if($_SESSION['user_id']) {
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
                    echo "Script ran.";
                }
            }
            else {
                weeklyCron($system);
                echo "Script ran.";
            }
        }
        else {
            echo "You can run the reputation cron script Adhoc. This is not reversible. <a href='{$system->router->base_url}/cron_jobs/reputation_cron.php?run_script=true'>Run</a>.";
        }
    }
}

function weeklyCron($system) {
    foreach(Reputation::$VillageRep as $RANK_INT => $RANK) {
        if($RANK_INT == 1) {
            $queries[] = "UPDATE `users` SET `weekly_rep`=0, `village_rep`=0 WHERE `village_rep` >= " . $RANK['min_rep']
                . " AND `village_rep` < " . Reputation::$VillageRep[2]['min_rep'] . " AND `village_rep`<" . $RANK['base_decay'];
        }
        $next_rank_where = "";
        if($RANK_INT < sizeof(Reputation::$VillageRep)) {
            $RANK2_INT = $RANK_INT+1;
            $RANK2 = Reputation::$VillageRep[$RANK2_INT];
            $next_rank_where = " AND `village_rep` < " . $RANK2['min_rep'];
            if($RANK_INT == 1) {
                $next_rank_where .= " AND `village_rep` > 0";
            }
        }
        $queries[] = "UPDATE `users` SET `weekly_rep`=0, `village_rep`=`village_rep`- " . $RANK['base_decay'] . " WHERE `village_rep` >= "
            . $RANK['min_rep'] . $next_rank_where . " AND `weekly_rep` < " . $RANK['weekly_cap'];
        $queries[] = "UPDATE `users` SET `weekly_rep`=0, `village_rep`=`village_rep`- " . floor($RANK['base_decay'] * Reputation::DECAY_MODIFIER)
            . " WHERE `village_rep` >= " . $RANK['min_rep'] . $next_rank_where . " AND `weekly_rep` >= " . $RANK['weekly_cap'];
    }
    foreach($queries as $query) {
        if(false) {
            echo $query . "<br />";
        }
        else {
            $system->db->query($query);
        }
    }
}