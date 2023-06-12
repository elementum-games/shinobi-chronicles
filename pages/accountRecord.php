<?php


function accountRecord() {
    global $system;
    global $player;

    $warnings = $player->getOfficialWarnings();

    $bans = [];
    $ban_result = $system->query("SELECT * FROM `user_record` WHERE `user_id`='{$player->user_id}' AND `record_type` IN ('"
        . StaffManager::RECORD_BAN_ISSUED . "', '" . StaffManager::RECORD_BAN_REMOVED . "') ORDER BY `time` DESC");
    if($system->db_last_num_rows) {
        while($ban = $system->db_fetch($ban_result)) {
            $bans[] = $ban;
        }
    }

    $warning_to_view = null;
    if(isset($_GET['warning_id'])) {
        $warning_to_view = $player->getOfficialWarning((int)$_GET['warning_id']);
        if($warning_to_view == null) {
            $system->message("Invalid warning!");
            $system->printMessage();
        }
    }

    require 'templates/user_record.php';
}