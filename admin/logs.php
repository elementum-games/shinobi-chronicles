<?php

function viewLogsPage(System $system, User $player) {
    $self_link = $system->router->getUrl('admin', ['page' => 'logs']);
    $default_log_type = 'staff_logs';
    $log_type = $default_log_type;

    // Filters
    $character_id = null;
    $currency_type = null;

    if(isset($_GET['character_id'])) {
        $character_id = (int)$_GET['character_id'];
    }
    if(isset($_GET['currency_type'])) {
        switch($_GET['currency_type']) {
            case 'money':
            case 'premium_credits':
                $currency_type = $_GET['currency_type'];
                break;
            default:
                break;
        }
    }

    //Pagination - log types
    if(isset($_GET['log_type'])) {
        $log_type = $system->db->clean($_GET['log_type']);
        if(!in_array($log_type, ['staff_logs', 'currency_logs'])) {
            $log_type = $default_log_type;
        }
        $self_link .= "&log_type=$log_type";
    }

    $offset = 0;
    $limit = 25;

    if($log_type === 'currency_logs') {
        if($character_id != null) {
            $max = $player->staff_manager->countCurrencyLogs(
                character_id: $character_id,
                offset: $offset,
                limit: $limit,
                currency_type: $currency_type
            ) - $limit;
        }
        else {
            $max = 0;
        }
    }
    else {
        $max = $player->staff_manager->getStaffLogs(
            table: $log_type,
            log_type: 'all',
            offset: $offset,
            limit: $limit,
            maxCount: true
        ) - $limit;
    }

    if(isset($_GET['offset'])) {
        $offset = (int) $_GET['offset'];
        if($offset < 0) {
            $offset = 0;
        }
        if($offset > $max) {
            $offset = $max;
        }
    }

    $next = $offset + $limit;
    $previous = $offset - $limit;
    if($next > $max) {
        $next = $max;
    }
    if($previous < 0) {
        $previous = 0;
    }

    if($log_type === 'currency_logs') {
        if($character_id != null) {
            $logs = $player->staff_manager->getCurrencyLogs(
                character_id: $character_id,
                offset: $offset,
                limit: $limit,
                currency_type: $currency_type
            );
        }
        else {
            $logs = [];
        }

    }
    else {
        $logs = $player->staff_manager->getStaffLogs(table: $log_type, log_type: 'all', offset: $offset, limit: $limit);
    }

    if($system->message) {
        $system->printMessage();
    }

    require 'templates/admin/logs.php';
}