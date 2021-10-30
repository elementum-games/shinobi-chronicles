<?php

function supportPanel() {
    global $system;
    global $player;
    global $self_link;

    require_once('classes/SupportManager.php');
    $supportManager = new SupportManager($system, $player->user_id, $player->support_level, true);
    $offset = 0;
    $limit = 100;
    $next = null;
    $previous = null;

    // Auto close supports for user inactivity
    $supportManager->autocloseSupports();

    $processTypes = 'IN_' . $supportManager->processTypes(true);
    $maxOffset = $supportManager->supportSearch(['open' => '1', 'support_type' => $processTypes], false,
            ['updated' => 'DESC', 'premium'=>'DESC'], false, true) - $limit;

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

    if(isset($_GET['support_search']) && $_GET['support_search'] == 'true') {
        $self_link .= "&support_search=true";
        $searchData['support_type'] = $processTypes;

        if(isset($_GET['user_name'])) {
            $self_link .= "&user_name=" . $_GET['user_name'];
            $searchData['user_name'] = $system->clean($_GET['user_name']);
        }
        if(isset($_GET['support_type'])) {
            $self_link .= "&support_type=" . $_GET['support_type'];
            $searchData['support_type'] = $system->clean($_GET['support_type']);
        }
        if(isset($_GET['support_key'])) {
            $self_link .= "&support_key=" . $_GET['support_key'];
            $searchData['support_key'] = $system->clean($_GET['support_key']);
        }
        if(isset($_GET['ip_address'])) {
            $self_link .= "&ip_address=" . $_GET['ip_address'];
            $searchData['ip_address'] = $system->clean($_GET['ip_address']);
        }

        $supports = $supportManager->supportSearch($searchData, false, ['updated' => 'DESC', 'open'=>'DESC', 'premium'=>'DESC']);
        $maxOffset = $supportManager->supportSearch($searchData, false, ['updated' => 'DESC', 'open'=>'DESC', 'premium'=>'DESC'], false, true) - $limit;
    }

    // Pagination
    if($maxOffset < 0) {
        $maxOffset = 0;
    }
    if(isset($_GET['offset'])) {
        $offset = (int) $_GET['offset'];
    }
    // Previous button
    $previous -= $limit;
    if($previous < 0) {
        $previous = 0;
    }
    // Next button
    $next = $offset + $limit;
    if($next > $maxOffset) {
        $next = $maxOffset;
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
                // No valid permission
                if(!$supportManager->canProcess($supportData['support_type'])) {
                    throw new Exception("You do not have permission to process this support type!");
                }

                $supportManager->addSupportResponses($support_id, $player->user_id, $player->user_name,
                    $message, $player->current_ip, $player->staff_level);
            }

            if (isset($_POST['close_ticket'])) {
                $supportData = $supportManager->fetchSupportByID($support_id);
                $message = '';
                if (isset($_POST['message'])) {
                    $message = $system->clean($message);
                }

                // Not found
                if(!$supportData) {
                    throw new Exception("Support not found!");
                }
                // No valid permission
                if(!$supportManager->canProcess($supportData['support_type'])) {
                    throw new Exception("You do not have permission to process this support type!");
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
    }
    //Only fetch all supports if a specific isn't selected
    else if (!isset($_GET['support_search'])){
        $supports = $supportManager->fetchAllSupports($category, $player->user_id, $limit, $offset);
    }

    if (!$system->message_displayed) {
        $system->printMessage();
    }
    require('templates/staff/supportRequests.php');
}