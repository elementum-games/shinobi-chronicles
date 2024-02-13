<?php

require_once __DIR__ . '/../classes/notification/NotificationManager.php';

/**
 * @throws DatabaseDeadlockException
 */
function specialMissions() {
    global $system;
    global $player;

    $special_mission = null;

    // Start new Special Mission
    if (isset($_GET['start']) && !$player->special_mission_id) {
        try {
            $difficulty = $system->db->clean($_GET['start']);
            $special_mission = SpecialMission::startMission($system, $player, $difficulty);
            $player->special_mission_id = $special_mission->mission_id;

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
        } catch (RuntimeException $e) {
            $system->message($e->getMessage());
        }
    }

    // cancel the mission
    if (isset($_GET['cancelmission']) && $player->special_mission_id) {
        SpecialMission::cancelMission($system, $player, $player->special_mission_id);
    }

    if($player->special_mission_id && $special_mission == null) {
        $special_mission = new SpecialMission($system, $player, $player->special_mission_id);
    }

    $system->printMessage();
    require 'templates/specialMissions.php';
}