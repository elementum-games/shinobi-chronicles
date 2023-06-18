<?php
function chatLog() {
    require_once 'classes/ReportManager.php';

    global $system;
    global $player;
    global $self_link;
    $reportManager = new ReportManager($system, $player, true);

    $report_id = false;
    if(isset($_GET['report_id'])) {
        $report_id = (int) $_GET['report_id'];
    }
    $report = $reportManager->getReport($report_id);

    //Validate chat log use
    if(!$report && !$player->staff_manager->isUserAdmin()) {
        $system->message("Invalid report!");
        $system->printMessage();
        return false;
    }
    if($report_id == false && !$player->staff_manager->isUserAdmin()) {
        $system->message("You can not view this page without a report!");
        $system->printMessage();
        return false;
    }
    if($report && $report['report_type'] != ReportManager::REPORT_TYPE_CHAT) {
        $system->message("This is not a chat report!");
        $system->printMessage();
        return false;
    }
    if($report && $report['status'] != ReportManager::VERDICT_UNHANDLED && !$player->staff_manager->isHeadModerator()) {
        $system->message("Report handled!!");
        $system->printMessage();
        return false;
    }

    if(isset($_GET['post_id'])) {
        $post_id = $_GET['post_id'];
    }
    else if($report){
        $post_id = $report['content_id'];
    }
    else {
        $post_id = 0;
    }

    $limit = 10;
    $max_chat_id = 0;
    $max_result = $system->db->query("SELECT `post_id` FROM `chat` ORDER BY `post_id` DESC LIMIT 1");
    if($system->db->last_num_rows) {
        $max_chat_id = $system->db->fetch($max_result)['post_id'];
    }

    $min_diff = 0;
    $min_id = $post_id - ($limit);
    if($min_id < 1) {
        $min_diff = abs(0 - $min_id);
    }
    $max_id = $post_id + $limit;
    if($min_diff > 0) {
        $max_id += $min_diff;
    }
    if($max_id > $max_chat_id) {
        $difference = $max_id - $max_chat_id;
        $max_id = $max_chat_id;
        $min_id -= $difference;
    }

    $posts = [];
    if($report || $post_id) {
        $result = $system->db->query(
            "SELECT * FROM `chat` WHERE `post_id` BETWEEN '$min_id' AND '$max_id' ORDER BY `post_id` DESC"
        );
        if (!$system->db->last_num_rows) {
            $system->message("Posts not found!");
            $system->printMessage();
            return false;
        }
        $posts = $system->db->fetch_all($result);
    }

    include 'templates/staff/mod/chat_log.php';
}