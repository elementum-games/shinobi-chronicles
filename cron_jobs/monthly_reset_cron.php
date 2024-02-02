<?php
session_start();

require_once __DIR__ . '/../classes/System.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/inbox/Inbox.php';

/**
 * Monthly Reset Cron Job
 *
 * This should be processed every month.
 *
 * Monthly Reset does the following:
 *      Resets monthly PvP for users to 0
 *      Resets monthly Points for teams to 0
 *      Resets monthly Points for villages to 0
 */

$system = System::initialize(load_layout: false);
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
                <form action='{$system->router->base_url}/cron_jobs/monthly_reset.php?run_script=true' method='post'>
                    <input type='text' name='confirm' /><input type='submit' value='Run Script' />
                </form>";
                }
                else {
                    monthlyCron($system);
                    $player->staff_manager->staffLog(StaffManager::STAFF_LOG_ADMIN,
                        "{$player->user_name}({$player->user_id}) manually ran monthly reset cron.");
                    echo "Script ran.";
                }
            }
            else {
                monthlyCron($system);
                $player->staff_manager->staffLog(StaffManager::STAFF_LOG_ADMIN,
                    "{$player->user_name}({$player->user_id}) manually ran monthly reset cron.");
                echo "Script ran.";
            }
        }
        else {
            echo "You can run the reputation cron script Adhoc. This is not reversible. <a href='{$system->router->base_url}/cron_jobs/monthly_reset_cron.php?run_script=true'>Run</a>.";
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
        monthlyCron($system);
        $system->log('cron', 'Monthly Reset', "Monthly reset cron has been processed.");
    }
    else {
        $system->log('cron', 'Invalid access', "Attempted access by " . $_SERVER['REMOTE_ADDR']);
    }
}

function monthlyCron(System $system, $debug = false): void
{
    $queries = [];
    $queries[] = "UPDATE `users` SET `prev_monthly_pvp` = `monthly_pvp`";
    $queries[] = "UPDATE `teams` SET `prev_monthly_points` = `monthly_points`";
    $queries[] = "UPDATE `villages` SET `prev_monthly_points` = `monthly_points`";
    $queries[] = "UPDATE `users` SET `monthly_pvp` = 0";
    $queries[] = "UPDATE `teams` SET `monthly_points` = 0";
    $queries[] = "UPDATE `villages` SET `monthly_points` = 0";

    foreach($queries as $query) {
        if($debug) {
            echo $query . "<br />";
        }
        else {
            $system->db->query("LOCK TABLES `users` WRITE, `teams` WRITE, `villages` WRITE;");
            $system->db->query($query);
            $system->db->query("UNLOCK TABLES;");
        }
    }

}