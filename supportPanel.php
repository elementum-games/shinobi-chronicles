<?php

function supportPanel() {
    global $system;
    global $player;
    global $self_link;

    require_once('classes/SupportManager.php');
    $supportManager = new SupportManager($system, $player->user_id, true);
    $offset = 0;
    $limit = 100;
    $next = 100;
    $previous = null;

    // Auto close supports for user inactivity
    $supportManager->autocloseSupports();

    $maxOffset = $supportManager->supportSearch(['open' => '1'], false, ['updated' => 'DESC'], false, true);
    if ($next > $maxOffset) {
        $next = $maxOffset - $limit;
    }

    // Display variables
    $self_link .= '&view=support_requests';
    $supports = [];
    $support_id = 0;
    if (isset($_GET['support_id'])) {
        $support_id = (int)$_GET['support_id'];
    }
    $category = 'awaiting_admin';
    if (isset($_GET['category'])) {
        $category = $system->clean($_GET['category']);
        $self_link .= "&category={$category}";
    }

    // Pagination
    if (isset($_GET['offset'])) {
        $offset = $_GET['offset'];
        if ($offset > $maxOffset) {
            $offset -= $limit;
        }

        $previous = $offset - 100;
        if ($previous < 0) {
            $previous = 0;
        }
        $next = $offset + $limit;
        if ($next > $maxOffset) {
            $next = $maxOffset - $limit;
        }
    }

    if (isset($_POST)) {
        try {
            if (isset($_POST['add_response'])) {
                $supportData = $supportManager->fetchSupportByID($support_id);
                $message = $system->clean($_POST['message']);

                // Support not found
                if (!$supportData) {
                    throw new Exception("Support not found!");
                }
                // Support closed
                if (!$supportData['open']) {
                    throw new Exception("Support is closed!");
                }
                // Message validation
                if ($message == '') {
                    throw new Exception("You must enter a reply!");
                }
                if (strlen($message) < SupportManager::$validationConstraints['message']['min']) {
                    throw new Exception("Response must be at least "
                        . SupportManager::$validationConstraints['message']['min'] . " characters long.");
                }
                if (strlen($message) > SupportManager::$validationConstraints['message']['max']) {
                    throw new Exception("Response cannot exceed "
                        . SupportManager::$validationConstraints['message']['max'] . " characters long.");
                }

                $supportManager->addSupportResponses($support_id, $player->user_id, $player->user_name,
                    $message, $player->current_ip, $player->staff_level);
            }

            if (isset($_POST['close_ticket'])) {
                $message = '';
                if (isset($_POST['message'])) {
                    $message = $system->clean($message);
                }

                if ($message != '') {
                    $supportManager->addSupportResponses($support_id, $player->user_id, $player->user_name,
                        $message, $player->current_ip, $player->staff_level);
                }

                if ($supportManager->closeSupport($support_id, $player->staff_level, false, $player->user_name)) {
                    $system->message("Support closed!");
                } else {
                    throw new Exception("Error closing support!d");
                }
            }
        } catch (Exception $e) {
            $system->message($e->getMessage());
        }
    }

    //Fetch support data and responses
    if ($support_id != 0) {
        $supportData = $supportManager->fetchSupportByID($support_id);
        $supportResponses = $supportManager->fetchSupportResponses($support_id);
    } //Only fetch all supports if a specific isn't selected
    else {
        $supports = $supportManager->fetchAllSupports($category, $player->user_id, $limit, $offset);
    }

    if (!$system->message_displayed) {
        $system->printMessage();
    }
    require('templates/staff/supportRequests.php');
}