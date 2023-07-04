<?php

require_once __DIR__ . '/../classes/notification/NotificationManager.php';

function specialMissions() {
    global $system;
    global $player;
	global $self_link;
	$self_id = 15;

    // Start new Special Mission
    if (isset($_GET['start']) && !$player->special_mission) {
        try {
            $difficulty = $system->db->clean($_GET['start']);
            $special_mission = SpecialMission::startMission($system, $player, $difficulty);
            $player->special_mission = $special_mission->mission_id;

            $player->log(User::LOG_SPECIAL_MISSION, "{$difficulty} $difficulty");

            // Create notification
            $new_notification = new NotificationDto(
                type: "specialmission",
                message: "Special Mission in progress",
                user_id: $player->user_id,
                created: time(),
                alert: false,
            );
            NotificationManager::createNotification($new_notification, $system, NotificationManager::UPDATE_REPLACE);
        } catch (Exception $e) {
            $system->message($e->getMessage());
        }
    }

    // cancel the mission
    if (isset($_GET['cancelmission']) && $player->special_mission) {
        $special_mission = SpecialMission::cancelMission($system, $player, $player->special_mission);
    }

    $system->printMessage();
    require 'templates/specialMissions.php';
}