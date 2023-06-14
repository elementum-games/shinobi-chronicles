<?php

header('Content-Type: application/json');

# Begin standard auth
require "../classes/_autoload.php";

$system = new System();
$system->startTransaction();
$system->is_api_request = true;

try {
    $player = Auth::getUserFromSession($system);
    $player->loadData();
} catch(Exception $e) {
    echo json_encode(['logout' => true]);
    $system->rollbackTransaction();
    error_log($e->getMessage());
    exit;
    // API::exitWithError($e->getMessage());
}
# End standard auth


$status = true;

// Check if the user is in battle
if ($player->battle_id) {
    echo json_encode(['inBattle' => true]);
    $system->commitTransaction();
    exit;
}

// check if the mission exists
if (!$player->special_mission) {
    API::exitWithError("Not on a special mission!", system: $system);
}

$special_mission = new SpecialMission($system, $player, $player->special_mission);

$time_gap_ms = SpecialMission::EVENT_DURATION_MS;
$target_update = $special_mission->returnLastUpdateMs() + $time_gap_ms;
if (floor(microtime(true) * 1000) >= $target_update) {
    $special_mission->nextEvent();
}

$system->commitTransaction();
echo json_encode([
    'mission' => $special_mission,
    'systemMessage' => $system->message,
]);