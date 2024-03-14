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

function processRegionRegenInterval(System $system, $debug = true): void
{
    // get regions
    $region_result = $system->db->query("SELECT * FROM `regions`");
    $region_result = $system->db->fetch_all($region_result);
    $queries = [];
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

        /* step 1: roll for rebellion */
        foreach ($region_location_result as &$region_location) {
            // if village, and stability is negative, and not original village, roll for rebellion
            if ($region_location['type'] == 'village' && $region_location['stability'] < 0 && $region_location['occupying_village_id'] != WarManager::REGION_ORIGINAL_VILLAGE[$region['region_id']]) {
                // rebellin chance is proportional to stability, spread evenly over each hour
                $rebellion_chance = abs($region_location['stability']) / (60 / WarManager::REGEN_INTERVAL_MINUTES);
                if (mt_rand(0, 100) < $rebellion_chance) {
                    $region_location['rebellion_active'] = 1;
                }
            }
            unset($region_location);
        }

        /* step 2: update health */
        $castle = null;
        $village_regen_share = 0;
        foreach ($region_location_result as &$region_location) {
            switch ($region_location['type']) {
                case 'castle':
                    // increase health, cap at max
                    $regen = (WarManager::BASE_CASTLE_REGEN_PER_MINUTE * WarManager::REGEN_INTERVAL_MINUTES) * max((1 + ($region_location['stability'] / 100)), 0);
                    $region_location['health'] = min($region_location['health'] + $regen, WarManager::BASE_CASTLE_HEALTH);
                    // get castle reference
                    $castle = &$region_location;
                    break;
                case 'village':
                    if ($region_location['stability'] >= 0 && $region_location['rebellion_active']) {
                        $region_location['rebellion_active'] = 0;
                    }
                    if ($region_location['rebellion_active']) {
                        $damage = (WarManager::BASE_REBELLION_DAMAGE_PER_MINUTE * WarManager::REGEN_INTERVAL_MINUTES) * max((1 + (-1 * $region_location['stability'] / 100)), 0);
                        $region_location['health'] = max($region_location['health'] - $damage, 0);
                        // if health reaches 0, change control
                        if ($region_location['health'] == 0) {
                            $region_location['occupying_village_id'] = WarManager::REGION_ORIGINAL_VILLAGE[$region['region_id']];
                            $region_location['rebellion_active'] = 0;
                            $region_location['health'] = (WarManager::INITIAL_LOCATION_CAPTURE_HEALTH_PERCENT / 100) * WarManager::BASE_TOWN_HEALTH;
                            $region_location['defense'] = WarManager::INITIAL_LOCATION_CAPTURE_DEFENSE;
                            $region_location['stability'] = WarManager::INITIAL_LOCATION_CAPTURE_STABILITY;
                        }
                    } else {
                        // increase health, cap at max
                        $regen = (WarManager::BASE_TOWN_REGEN_PER_MINUTE * WarManager::REGEN_INTERVAL_MINUTES) * max((1 + ($region_location['stability'] / 100)), 0);
                        $region_location['health'] = min($region_location['health'] + $regen, WarManager::BASE_TOWN_HEALTH);
                        // give a bonus to castle regen if same owner
                        if (isset($castle)) {
                            if ($region_location['occupying_village_id'] == $castle['occupying_village_id']) {
                                $village_regen_share += floor((WarManager::TOWN_REGEN_SHARE_PERCENT / 100) * $regen);
                            }
                        }
                    }
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

        /* update region_locations */
        foreach ($region_location_result as $region_location) {
            $queries[] = "UPDATE `region_locations` SET
                `health` = {$region_location['health']},
                `defense` = {$region_location['defense']},
                `stability` = {$region_location['stability']},
                `rebellion_active` = {$region_location['rebellion_active']},
                `occupying_village_id` = {$region_location['occupying_village_id']}
                WHERE `region_location_id` = {$region_location['region_location_id']}";
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
