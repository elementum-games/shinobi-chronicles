<?php
session_start();

/**
 * Region Cron Job
 **
 *  This should be processed every hour.
 *
 *  Region Cron does the following:
 *      Collects home region resources
 *      Increases resource count of each region by production rate (25)
 *      Increases/Decreases region_location defense value by 1 toward baseline
 *      Applies region_location regen, bonus for castles based on local village health
 *      Creates resource logs
 */

require_once __DIR__ . '/../classes/System.php';
require_once __DIR__ . '/../classes/Village.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/inbox/Inbox.php';
require_once __DIR__ . '/../classes/war/WarManager.php';
require_once __DIR__ . '/../classes/travel/Patrol.php';

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
                <form action='{$system->router->base_url}/cron_jobs/region_cron.php?run_script=true' method='post'>
                    <input type='text' name='confirm' /><input type='submit' value='Run Script' />
                </form>";
                } else {
                    hourlyRegion($system, $debug);
                    $player->staff_manager->staffLog(
                        StaffManager::STAFF_LOG_ADMIN,
                        "{$player->user_name}({$player->user_id}) manually ran region cron."
                    );
                }
            } else {
                $debug = false;
                if (isset($_GET['debug'])) {
                    $debug = $system->db->clean($_GET['debug']);
                }
                hourlyRegion($system, $debug);
                $player->staff_manager->staffLog(
                    StaffManager::STAFF_LOG_ADMIN,
                    "{$player->user_name}({$player->user_id}) manually ran region cron."
                );
            }
        } else {
            echo "You can run the region cron script Adhoc. This is not reversible.<br><a href='{$system->router->base_url}/cron_jobs/region_cron.php?run_script=true'>Run</a><br><a href='{$system->router->base_url}/cron_jobs/region_cron.php?run_script=true&debug=true'>Debug</a>";
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
        hourlyRegion($system, debug: false);
        $system->log('cron', 'Hourly Region', "Regions have been processed.");
    } else {
        $system->log('cron', 'Invalid access', "Attempted access by " . $_SERVER['REMOTE_ADDR']);
    }
}

function hourlyRegion(System $system, $debug = true): void
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
    // apply base production
    foreach ($village_result as $village) {
        $villages[$village['village_id']] = new Village($system, village_row: $village);
        $production = WarManager::VILLAGE_BASE_RESOURCE_PRODUCTION + $villages[$village['village_id']]->policy->home_production_boost;
        $materials_production = $villages[$village['village_id']]->policy->materials_production;
        $food_production = $villages[$village['village_id']]->policy->food_production;
        $wealth_production = $villages[$village['village_id']]->policy->wealth_production;
        $villages[$village['village_id']]->addResource(1, $materials_production);
        $villages[$village['village_id']]->addResource(2, $food_production);
        $villages[$village['village_id']]->addResource(3, $wealth_production);
        $queries[] = "INSERT INTO `resource_logs`
                        (`village_id`, `resource_id`, `type`, `quantity`, `time`)
                        VALUES ({$village['village_id']}, 1, " . VillageManager::RESOURCE_LOG_PRODUCTION . ", {$materials_production}, " . time() . ")";
        $queries[] = "INSERT INTO `resource_logs`
                        (`village_id`, `resource_id`, `type`, `quantity`, `time`)
                        VALUES ({$village['village_id']}, 1, " . VillageManager::RESOURCE_LOG_COLLECTION . ", {$materials_production}, " . time() . ")";
        $queries[] = "INSERT INTO `resource_logs`
                        (`village_id`, `resource_id`, `type`, `quantity`, `time`)
                        VALUES ({$village['village_id']}, 2, " . VillageManager::RESOURCE_LOG_PRODUCTION . ", {$food_production}, " . time() . ")";
        $queries[] = "INSERT INTO `resource_logs`
                        (`village_id`, `resource_id`, `type`, `quantity`, `time`)
                        VALUES ({$village['village_id']}, 2, " . VillageManager::RESOURCE_LOG_COLLECTION . ", {$food_production}, " . time() . ")";
        $queries[] = "INSERT INTO `resource_logs`
                        (`village_id`, `resource_id`, `type`, `quantity`, `time`)
                        VALUES ({$village['village_id']}, 3, " . VillageManager::RESOURCE_LOG_PRODUCTION . ", {$wealth_production}, " . time() . ")";
        $queries[] = "INSERT INTO `resource_logs`
                        (`village_id`, `resource_id`, `type`, `quantity`, `time`)
                        VALUES ({$village['village_id']}, 3, " . VillageManager::RESOURCE_LOG_COLLECTION . ", {$wealth_production}, " . time() . ")";
    }
    foreach ($region_result as $region) {
        // get region locations for region
        $region_location_result = $system->db->query("SELECT * FROM `region_locations` WHERE `region_id` = {$region['region_id']}");
        $region_location_result = $system->db->fetch_all($region_location_result);

        /* update stability */
        $castle_stability = 0;
        $castle_occupying_village_id = 0;
        foreach ($region_location_result as $region_location) {
            if ($region_location['type'] === 'castle') {
                $castle_occupying_village_id = $region_location['occupying_village_id'];
                $stability_baseline = WarManager::BASE_CASTLE_STABILITY;
                if ($region_location['stability'] < $stability_baseline) {
                    $region_location['stability'] += WarManager::BASE_STABILITY_SHIFT_PER_HOUR;
                    $region_location['stability'] = min($region_location['stability'], $stability_baseline);
                } else if ($region_location['stability'] > $stability_baseline) {
                    $region_location['stability'] -= WarManager::BASE_STABILITY_SHIFT_PER_HOUR;
                    $region_location['stability'] = max($region_location['stability'], $stability_baseline);
                }
                $castle_stability = $region_location['stability'];
            }
        }
        foreach ($region_location_result as $region_location) {
            if ($region_location['type'] === 'village') {
                $stability_baseline = WarManager::BASE_TOWN_STABILITY;
                // if castle owned
                if ($castle_occupying_village_id == $region_location['occupying_village_id']) {
                    $stability_baseline += $castle_stability;
                }
                // if non-native region
                if ($region_location['occupying_village_id'] != WarManager::REGION_ORIGINAL_VILLAGE[$region['region_id']]) {
                    $stability_baseline -= WarManager::OCCUPIED_TOWN_STABILITY_PENALTY;
                }
                if ($region_location['stability'] < $stability_baseline) {
                    $region_location['stability'] += WarManager::BASE_STABILITY_SHIFT_PER_HOUR;
                    $region_location['stability'] = min($region_location['stability'], $stability_baseline);
                } else if ($region_location['stability'] > $stability_baseline) {
                    $region_location['stability'] -= WarManager::BASE_STABILITY_SHIFT_PER_HOUR;
                    $region_location['stability'] = max($region_location['stability'], $stability_baseline);
                }
            }
        }

        /* update resource count */
        foreach ($region_location_result as &$region_location) {
            switch ($region_location['type']) {
                case 'castle':
                    $production = WarManager::BASE_CASTLE_RESOURCE_PRODUCTION * max((1 + ($region_location['stability'] / 100)), 0);
                    break;
                case 'village';
                    $production = WarManager::BASE_TOWN_RESOURCE_PRODUCTION * max((1 + ($region_location['stability'] / 100)), 0);
                    break;
                default;
                    break;
            }
            // if one of the home regions, collect resources bypassing caravans
            if ($region['region_id'] <= 5) {
                $villages[$region['village']]->addResource($region_location['resource_id'], $region_location['resource_count']);
                $queries[] = "INSERT INTO `resource_logs`
                    (`village_id`, `resource_id`, `type`, `quantity`, `time`)
                    VALUES ({$region_location['occupying_village_id']}, {$region_location['resource_id']}, " . VillageManager::RESOURCE_LOG_COLLECTION . ", {$region_location['resource_count']}, " . time() . ")";
                $region_location['resource_count'] = 0;
            }
            $region_location['resource_count'] += $production;
            !empty($village_resource_production[$region['village']][$region_location['resource_id']]) ? $village_resource_production[$region['village']][$region_location['resource_id']] += $production : $village_resource_production[$region['village']][$region_location['resource_id']] = $production;
            unset($region_location);
        }

        /* update defense */
        foreach ($region_location_result as &$region_location) {
            switch ($region_location['type']) {
                case 'castle':
                    if ($region_location['defense'] > $region_location['stability']) {
                        $region_location['defense'] -= WarManager::BASE_DEFENSE_SHIFT_PER_HOUR;
                        if ($region_location['defense'] < $region_location['stability']) {
                            $region_location['defense'] = $region_location['stability'];
                        }
                    }
                    else if ($region_location['defense'] < $region_location['stability']) {
                        $region_location['defense'] += WarManager::BASE_DEFENSE_SHIFT_PER_HOUR;
                        if ($region_location['defense'] > $region_location['stability']) {
                            $region_location['defense'] = $region_location['stability'];
                        }
                    }
                    break;
                case 'village';
                    if ($region_location['defense'] > $region_location['stability']) {
                        $region_location['defense'] -= WarManager::BASE_DEFENSE_SHIFT_PER_HOUR;
                        if ($region_location['defense'] < $region_location['stability']) {
                            $region_location['defense'] = $region_location['stability'];
                        }
                    } else if ($region_location['defense'] < $region_location['stability']) {
                        $region_location['defense'] += WarManager::BASE_DEFENSE_SHIFT_PER_HOUR;
                        if ($region_location['defense'] > $region_location['stability']) {
                            $region_location['defense'] = $region_location['stability'];
                        }
                    }
                    break;
                default;
                    break;
            }
            unset($region_location);
        }

        /* update region_locations */
        foreach ($region_location_result as $region_location) {
            $queries[] = "UPDATE `region_locations`
                SET `resource_count` = {$region_location['resource_count']},
                `health` = {$region_location['health']},
                `defense` = {$region_location['defense']},
                `stability` = {$region_location['stability']}
                WHERE `id` = {$region_location['id']}";
        }
    }

    /* update villages */
    foreach ($villages as $village) {
        /* log resource production */
        if (!empty($village_resource_production[$village->village_id])) {
            /* log production */
            foreach ($village_resource_production[$village->village_id] as $key => $value) {
                $queries[] = "INSERT INTO `resource_logs`
                    (`village_id`, `resource_id`, `type`, `quantity`, `time`)
                    VALUES ({$village->village_id}, {$key}, " . VillageManager::RESOURCE_LOG_PRODUCTION . ", " . $value . ", " . time() . ")";
            }
        }
        /* generate village update queries */
        $queries[] = $village->updateResources(false);
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
            $system->db->query("LOCK TABLES `region_locations` WRITE, `villages` WRITE, `resource_logs` WRITE;");
            $system->db->query($query);
            $system->db->query("UNLOCK TABLES;");
        }
        echo "Script complete";
    }
}