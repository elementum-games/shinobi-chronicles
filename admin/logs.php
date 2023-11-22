<?php

function viewLogsPage(System $system, User $player) {
    $self_link = $system->router->getUrl('admin', ['page' => 'logs']);
    $default_log_type = 'staff_logs';
    $log_type = $default_log_type;

    // Filters
    $character_id = null;
    $currency_type = null;
    $currency_transaction_description_prefix = null;

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
    if(isset($_GET['currency_transaction_description_prefix'])) {
        $currency_transaction_description_prefix = $system->db->clean($_GET['currency_transaction_description_prefix']);
    }

    $allowed_log_types = ['staff_logs', 'currency_logs', 'cron_job_logs'];

    //Pagination - log types
    if(isset($_GET['log_type'])) {
        $log_type = $system->db->clean($_GET['log_type']);
        if(!in_array($log_type, $allowed_log_types)) {
            $log_type = $default_log_type;
        }
        $self_link .= "&log_type=$log_type";
    }

    $offset = 0;
    $limit = 25;

    // Get max post ID
    switch($log_type) {
        case 'currency_logs':
            if($character_id != null) {
                $max = $player->staff_manager->countCurrencyLogs(
                    character_id: $character_id,
                    offset: $offset,
                    limit: $limit,
                    currency_type: $currency_type,
                    transaction_description_prefix: $currency_transaction_description_prefix
                ) - $limit;
            }
            else {
                $max = 0;
            }
            break;
        case 'cron_job_logs':
            $query = "SELECT COUNT(*) as `count` FROM `logs` WHERE `log_type`='cron'";
            $result = $system->db->query($query);
            if($system->db->last_num_rows) {
                $max = $system->db->fetch($result)['count'] - $limit;
            }
            $max = 0;
            break;
        default:
            // staff logs
            $max = $player->staff_manager->getStaffLogs(
                table: $log_type,
                log_type: 'all',
                offset: $offset,
                limit: $limit,
                maxCount: true
            ) - $limit;
            break;
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

    // get logs
    switch($log_type) {
        case 'currency_logs':
            if($character_id != null) {
                $logs = $player->staff_manager->getCurrencyLogs(
                    character_id: $character_id,
                    offset: $offset,
                    limit: $limit,
                    currency_type: $currency_type,
                    transaction_description_prefix: $currency_transaction_description_prefix
                );
            }
            else {
                $logs = [];
            }
            break;
        case 'cron_job_logs':
            $logs = [];

            $query = "SELECT * FROM `logs` WHERE `log_type`='cron' ORDER BY `log_id` DESC LIMIT $limit OFFSET $offset";
            $result = $system->db->query($query);
            if($system->db->last_num_rows) {
                $logs = $system->db->fetch_all($result);
            }

            break;
        default:
            $logs = $player->staff_manager->getStaffLogs(table: $log_type, log_type: 'all', offset: $offset, limit: $limit);
            break;
    }


    if($system->message) {
        $system->printMessage();
    }

    require 'templates/admin/logs.php';
}