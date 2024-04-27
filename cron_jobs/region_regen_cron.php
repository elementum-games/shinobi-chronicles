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
require_once __DIR__ . '/../classes/travel/RegionLocation.php';

$system = System::initialize(load_layout: false);
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

function processRegionRegenInterval(System $system, $debug = true): void {
    $queries = [];

    // get regions
    $region_result = $system->db->query("SELECT * FROM `regions`");
    $region_result = $system->db->fetch_all($region_result);

    // get villages
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

        /** @var RegionLocation[] $region_locations */
        $region_locations = [];
        $castle = null;
        foreach ($region_location_result as $row) {
            $region_location = RegionLocation::fromDb($row, $villages[$row['occupying_village_id']]);
            $region_locations[] = $region_location;

            /* step 1: roll for rebellion */
            $region_location->rollForRebellion();

            /* step 2: update health */
            $region_location->processRegen();
            if($region_location->type == 'castle') {
                $castle = &$region_location;
            }
        }

        // Check for village regen bonus
        $village_regen_to_castle = 0;
        foreach ($region_locations as $region_location) {
            // give a bonus to castle regen if same owner
            if (isset($castle)) {
                if ($region_location->occupying_village_id == $castle->occupying_village_id) {
                    $village_regen_to_castle += floor(
                        $region_location->getRegenAmount() * (WarManager::TOWN_REGEN_SHARE_PERCENT / 100)
                    );
                }
            }
        }

        // if castle exists, add bonus regen from villages
        if (!empty($castle)) {
            $castle->health = min($castle->health + $village_regen_to_castle, $castle->max_health);
        }

        /* update region_locations */
        foreach ($region_locations as $region_location) {
            $queries[] = "UPDATE `region_locations` SET
                `health` = {$region_location->health},
                `defense` = {$region_location->defense},
                `stability` = {$region_location->stability},
                `rebellion_active` = {$region_location->rebellion_active},
                `occupying_village_id` = {$region_location->occupying_village_id}
                WHERE `region_location_id` = {$region_location->region_location_id}";
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
            try {
                $system->db->query("LOCK TABLES `region_locations` WRITE;");
                $system->db->query($query);
                $system->db->query("UNLOCK TABLES;");
            } catch (Exception $e) {
                $system->db->query("UNLOCK TABLES;");
                echo $e->getMessage();
            }

        }
        echo "Script complete";
    }
}
