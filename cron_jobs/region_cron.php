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
require_once __DIR__ . '/../classes/war/WarManager.php';
require_once __DIR__ . '/../classes/travel/Patrol.php';

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
        $production = WarManager::VILLAGE_BASE_RESOURCE_PRODUCTION + $villages[$region['village']]->policy->home_production_boost;
        $villages[$village['village_id']]->addResource(1, $production);
        $villages[$village['village_id']]->addResource(2, $production);
        $villages[$village['village_id']]->addResource(3, $production);
        $queries[] = "INSERT INTO `resource_logs`
                        (`village_id`, `resource_id`, `type`, `quantity`, `time`)
                        VALUES ({$village['village_id']}, 1, " . VillageManager::RESOURCE_LOG_PRODUCTION . ", {$production}, " . time() . ")";
        $queries[] = "INSERT INTO `resource_logs`
                        (`village_id`, `resource_id`, `type`, `quantity`, `time`)
                        VALUES ({$village['village_id']}, 1, " . VillageManager::RESOURCE_LOG_COLLECTION . ", {$production}, " . time() . ")";
        $queries[] = "INSERT INTO `resource_logs`
                        (`village_id`, `resource_id`, `type`, `quantity`, `time`)
                        VALUES ({$village['village_id']}, 2, " . VillageManager::RESOURCE_LOG_PRODUCTION . ", {$production}, " . time() . ")";
        $queries[] = "INSERT INTO `resource_logs`
                        (`village_id`, `resource_id`, `type`, `quantity`, `time`)
                        VALUES ({$village['village_id']}, 2, " . VillageManager::RESOURCE_LOG_COLLECTION . ", {$production}, " . time() . ")";
        $queries[] = "INSERT INTO `resource_logs`
                        (`village_id`, `resource_id`, `type`, `quantity`, `time`)
                        VALUES ({$village['village_id']}, 3, " . VillageManager::RESOURCE_LOG_PRODUCTION . ", {$production}, " . time() . ")";
        $queries[] = "INSERT INTO `resource_logs`
                        (`village_id`, `resource_id`, `type`, `quantity`, `time`)
                        VALUES ({$village['village_id']}, 3, " . VillageManager::RESOURCE_LOG_COLLECTION . ", {$production}, " . time() . ")";
    }
    foreach ($region_result as $region) {
        // get region locations for region
        $region_location_result = $system->db->query("SELECT * FROM `region_locations` WHERE `region_id` = {$region['region_id']}");
        $region_location_result = $system->db->fetch_all($region_location_result);

        /* step 1: update resource count */
        foreach ($region_location_result as &$region_location) {
            // if one of the home regions, collect resources bypassing caravans
            switch ($region_location['type']) {
                case 'castle':
                    $production = WarManager::BASE_CASTLE_RESOURCE_PRODUCTION;
                    break;
                case 'village';
                    $production = WarManager::BASE_TOWN_RESOURCE_PRODUCTION;
                    break;
                default;
                    break;
            }
            if ($region['region_id'] <= 5) {
                if (empty($region['occupying_village_id'])) {
                    $villages[$region['village']]->addResource($region_location['resource_id'], $region_location['resource_count']);
                    $queries[] = "INSERT INTO `resource_logs`
                        (`village_id`, `resource_id`, `type`, `quantity`, `time`)
                        VALUES ({$region['village']}, {$region_location['resource_id']}, " . VillageManager::RESOURCE_LOG_COLLECTION . ", {$region_location['resource_count']}, " . time() . ")";
                    $region_location['resource_count'] = 0;
                }
            }
            $region_location['resource_count'] += $production;
            !empty($village_resource_production[$region['village']][$region_location['resource_id']]) ? $village_resource_production[$region['village']][$region_location['resource_id']] += $production : $village_resource_production[$region['village']][$region_location['resource_id']] = $production;
            unset($region_location);
        }

        /* step 2: update defense */
        foreach ($region_location_result as &$region_location) {
            switch ($region_location['type']) {
                case 'castle':
                    if ($region_location['defense'] > WarManager::BASE_CASTLE_DEFENSE) {
                        $region_location['defense']--;
                    }
                    else if ($region_location['defense'] < WarManager::BASE_CASTLE_DEFENSE) {
                        $region_location['defense']++;
                    }
                    break;
                case 'village';
                    if ($region_location['defense'] > WarManager::BASE_VILLAGE_DEFENSE) {
                        $region_location['defense']--;
                    } else if ($region_location['defense'] < WarManager::BASE_VILLAGE_DEFENSE) {
                        $region_location['defense']++;
                    }
                    break;
                default;
                    break;
            }
            unset($region_location);
        }

        /* step 3: update region_locations */
        foreach ($region_location_result as $region_location) {
            $queries[] = "UPDATE `region_locations`
                SET `resource_count` = {$region_location['resource_count']},
                `health` = {$region_location['health']},
                `defense` = {$region_location['defense']}
                WHERE `id` = {$region_location['id']}";
        }
    }

    /* update villages */
    foreach ($villages as $village) {
        /* apply policy upkeep and log expense */
        $materials_upkeep = $village->policy->materials_upkeep;
        $food_upkeep = $village->policy->food_upkeep;
        $wealth_upkeep = $village->policy->wealth_upkeep;
        if ($materials_upkeep > 0) {
            $change = $village->subtractResource(WarManager::RESOURCE_MATERIALS, $materials_upkeep);
            if ($change > 0) {
                $queries[] = "INSERT INTO `resource_logs`
                (`village_id`, `resource_id`, `type`, `quantity`, `time`)
                VALUES ({$village->village_id}, " . WarManager::RESOURCE_MATERIALS . ", " . VillageManager::RESOURCE_LOG_EXPENSE . ", " . $change . ", " . time() . ")";
            }
        }
        if ($food_upkeep > 0) {
            $change = $village->subtractResource(WarManager::RESOURCE_FOOD, $food_upkeep);
            if ($change > 0) {
            $queries[] = "INSERT INTO `resource_logs`
                (`village_id`, `resource_id`, `type`, `quantity`, `time`)
                VALUES ({$village->village_id}, " . WarManager::RESOURCE_FOOD . ", " . VillageManager::RESOURCE_LOG_EXPENSE . ", " . $change . ", " . time() . ")";
            }
        }
        if ($wealth_upkeep > 0) {
            $change = $village->subtractResource(WarManager::RESOURCE_WEALTH, $wealth_upkeep);
            if ($change > 0) {
                $queries[] = "INSERT INTO `resource_logs`
                (`village_id`, `resource_id`, `type`, `quantity`, `time`)
                VALUES ({$village->village_id}, " . WarManager::RESOURCE_WEALTH . ", " . VillageManager::RESOURCE_LOG_EXPENSE . ", " . $change . ", " . time() . ")";
            }
        }
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