<?php
session_start();

/**
 * Region Regen Cron Job
 **
 *  This should be processed every WarManager::REGEN_INTERVAL_MINUTES.
 *
 *  Region Cron does the following:
 *      Applies region_location regen, bonus for castles based on local village health
 */

require_once __DIR__ . '/../classes/System.php';
require_once __DIR__ . '/../classes/Village.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/inbox/Inbox.php';
require_once __DIR__ . '/../classes/war/WarManager.php';

$system = new System();
$system->db->connect();

if (!$system->db->con) {
    throw new RuntimeException("Error connecting to DB!");
}

$player = false;
if (isset($_SESSION['user_id'])) {
    $player = User::loadFromId($system, $_SESSION['user_id']);
    $player->loadData();
    $production_key = "RuN ON ProDUCtIon";

    if ($player->staff_manager->isHeadAdmin()) {
        if (isset($_GET['run_script']) && $_GET['run_script'] == 'true') {
            if ($system->environment === System::ENVIRONMENT_PROD) {
                $debug = false;
                if (isset($_GET['debug'])) {
                    $debug = $system->db->clean($_GET['debug']);
                }
                if (!isset($_POST['confirm']) || $_POST['confirm'] !== $production_key) {
                    echo "WARNING PRODUCTION ENVIRONMENT! Confirm you would like to run this adhoc on <b>PRODUCTION ENVIRONMENT</b>!!!<br />
                To confirm, type the following (case sensitive): $production_key<br />
                <form action='{$system->router->base_url}/cron_jobs/region_regen_cron.php?run_script=true' method='post'>
                    <input type='text' name='confirm' /><input type='submit' value='Run Script' />
                </form>";
                } else {
                    processRegionRegenInterval($system, $debug);
                    $player->staff_manager->staffLog(
                        StaffManager::STAFF_LOG_ADMIN,
                        "{$player->user_name}({$player->user_id}) manually ran region regen cron."
                    );
                }
            } else {
                $debug = false;
                if (isset($_GET['debug'])) {
                    $debug = $system->db->clean($_GET['debug']);
                }
                processRegionRegenInterval($system, $debug);
                $player->staff_manager->staffLog(
                    StaffManager::STAFF_LOG_ADMIN,
                    "{$player->user_name}({$player->user_id}) manually ran region regen cron."
                );
            }
        } else {
            echo "You can run the region regen cron script Adhoc. This is not reversible.<br><a href='{$system->router->base_url}/cron_jobs/region_regen_cron.php?run_script=true'>Run</a><br><a href='{$system->router->base_url}/cron_jobs/region_regen_cron.php?run_script=true&debug=true'>Debug</a>";
        }
    }
} else {
    // Check for verify to run cron
    $run_ok = false;
    if (php_sapi_name() == 'cli') {
        $run_ok = true;
    } else if ($_SERVER['REMOTE_ADDR'] == $_SERVER['SERVER_ADDR']) {
        $run_ok = true;
    }

    if ($run_ok) {
        processRegionRegenInterval($system, debug: false);
        $system->log('cron', 'Region Regen', "Regions have been processed.");
    } else {
        $system->log('cron', 'Invalid access', "Attempted access by " . $_SERVER['REMOTE_ADDR']);
    }
}

function processRegionRegenInterval(System $system, $debug = true): void
{
    // get regions
    $region_result = $system->db->query("SELECT * FROM `regions`");
    $region_result = $system->db->fetch_all($region_result);
    $queries = [];
    // get villages
    $village_resource_production = [];
    $village_result = $system->db->query("SELECT * FROM `villages`");
    $village_result = $system->db->fetch_all($village_result);
    $villages = [];
    foreach ($village_result as $village) {
        $villages[$village['village_id']] = new Village($system, village_row: $village);
    }
    foreach ($region_result as $region) {
        // get region locations for region
        $region_location_result = $system->db->query("SELECT * FROM `region_locations` WHERE `region_id` = {$region['region_id']}");
        $region_location_result = $system->db->fetch_all($region_location_result);

        /* step 1: update health */
        $castle = null;
        $village_regen_share = 0;
        foreach ($region_location_result as &$region_location) {
            switch ($region_location['type']) {
                case 'castle':
                    // increase health, cap at max
                    $region_location['health'] = min($region_location['health'] + WarManager::BASE_CASTLE_REGEN_PER_MINUTE * WarManager::REGEN_INTERVAL_MINUTES, WarManager::BASE_CASTLE_HEALTH);
                    // get castle reference
                    $castle = &$region_location;
                    break;
                case 'village';
                    // give a bonus to castle regen if not occupied
                    if (empty($region_location['occupying_village_id'])) {
                        $village_regen_share += floor((WarManager::VILLAGE_REGEN_SHARE_PERCENT / 100) * WarManager::BASE_VILLAGE_REGEN_PER_MINUTE * WarManager::REGEN_INTERVAL_MINUTES);
                    }
                    // increase health, cap at max
                    $region_location['health'] = min($region_location['health'] + WarManager::BASE_VILLAGE_REGEN_PER_MINUTE * WarManager::REGEN_INTERVAL_MINUTES, WarManager::BASE_VILLAGE_HEALTH);
                    break;
                default;
                    break;
            }
            unset($region_location);
        }
        // if castle exists, add bonus regen from villages
        if (!empty($castle)) {
            $castle['health'] = min($castle['health'] + $village_regen_share, WarManager::BASE_CASTLE_HEALTH);
            unset($castle);
        }

        /* step 2: update region_locations */
        foreach ($region_location_result as $region_location) {
            $queries[] = "UPDATE `region_locations`
                SET `resource_count` = {$region_location['resource_count']},
                `health` = {$region_location['health']}
                WHERE `id` = {$region_location['id']}";
        }
    }

    if ($debug) {
        echo "Debug running...<br>";
        foreach ($queries as $query) {
            echo $query . "<br />";
        }
        echo "Debug complete";
    } else {
        echo "Script running...<br>";
        foreach ($queries as $query) {
            $system->db->query("LOCK TABLES `region_locations` WRITE;");
            $system->db->query($query);
            $system->db->query("UNLOCK TABLES;");
        }
        echo "Script complete";
    }
}