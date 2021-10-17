<?php

header('Content-Type: application/json');
session_start();


$status = true;
// Make sure the user is logged in.
if(!isset($_SESSION['user_id'])) {
	echo "<!--LOGOUT-->";
	$status = false;
}

require_once("classes.php");
$system = new System();
$player = new User($_SESSION['user_id']);

// Load data without calling regen/training updates
$player->loadData(0); 

// Check if the user is in battle
if ($player->battle_id) {
    echo json_encode('battle');
    $status = false;
}

// check if the mission exists
if (!$player->special_mission) {
    // echo 'No Mission Set!';
    $status = false;
}

if ($status) { 

    $special_mission = new SpecialMission($system, $player, $player->special_mission);

    /* ******** LSM ADJUSTMENT ****** */
    // if the last step was more than X seconds ago 
    // The longer this is set then the easier it is for users to get sniped
    // and the longer the mission takes to complete
    $time_gap = 3;
    $target_update = $special_mission->returnLastUpdate() + $time_gap;
    if (time() >= $target_update) {
        $special_mission->nextEvent();
        echo json_encode($special_mission);
    }
}