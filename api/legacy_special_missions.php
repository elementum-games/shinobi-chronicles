<?php

header('Content-Type: application/json');

# Begin standard auth
require "../classes/_autoload.php";

$system = API::init(row_lock: true);

try {
    $player = Auth::getUserFromSession($system);
    $player->loadData();
} catch(RuntimeException $e) {
    echo json_encode(['logout' => true]);
    $system->db->rollbackTransaction();
    error_log($e->getMessage());
    exit;
    // API::exitWithError($e->getMessage());
}
# End standard auth

$mission_id = $_GET['mission_id'];

$status = true;

$special_mission = new SpecialMission($system, $player, $mission_id);

$time_gap_ms = SpecialMission::EVENT_DURATION_MS;
$target_update = $special_mission->returnLastUpdateMs() + $time_gap_ms;
if (floor(microtime(true) * 1000) >= $target_update && $player->special_mission) {
    $special_mission->nextEvent();
}

$system->db->commitTransaction();
echo json_encode([
    'mission' => $special_mission,
    'systemMessage' => $system->message,
]);