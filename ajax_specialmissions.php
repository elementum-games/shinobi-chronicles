<?php

header('Content-Type: application/json');
session_start();


$status = true;
// Make sure the user is logged in.
if(!isset($_SESSION['user_id'])) {
	echo json_encode(['logout' => true]);
	$status = false;
}

require_once("classes/_autoload.php");
$system = new System();
$player = new User($_SESSION['user_id']);

// Load data without calling regen/training updates
$player->loadData(0); 

// Check if the user is in battle
if ($player->battle_id) {
    echo json_encode(['inBattle' => true]);
    $status = false;
}

// check if the mission exists
if (!$player->special_mission) {
    // echo 'No Mission Set!';
    $status = false;
}

if ($status) {
    $special_mission = new SpecialMission($system, $player, $player->special_mission);

    $time_gap_ms = SpecialMission::EVENT_DURATION_MS;
    $target_update = $special_mission->returnLastUpdateMs() + $time_gap_ms;
    if (floor(microtime(true) * 1000) >= $target_update) {
        $special_mission->nextEvent();
    }

    echo json_encode([
        'mission' => $special_mission,
        'systemMessage' => $system->message,
    ]);
}