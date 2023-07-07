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

require_once __DIR__ . './classes/System.php';
require_once __DIR__ . './classes/Village.php';
require_once __DIR__ . './classes/User.php';
require_once __DIR__ . './classes/Mission.php';
require_once __DIR__ . './classes/travel/TravelManager.php';

$system = new System();
$system->db->connect();

if(!$system->db->con) {
    throw new RuntimeException("Error connecting to DB!");
}

$player = false;
if($_SESSION['user_id']) {
    $player = User::loadFromId($system, $_SESSION['user_id']);
    $player->loadData();

    if($player->staff_manager->isHeadAdmin()) {
        if(isset($_GET['run_script']) && $_GET['run_script'] == 'true') {
            weeklyCron($system);
            echo "Script ran.";
        }
        else {
            echo "You can run the reputation cron script Adhoc. This is not reversible. <a href='{$system->router->base_url}/reputation_cron.php?run_script=true'>Run</a>.";
        }
    }
}

function weeklyCron($system) {
    $decay = Reputation::DECAY;
    $system->db->query("UPDATE `users` SET `weekly_rep` = 0, `village_rep`=ceil(`village_rep`*{$decay})");
}