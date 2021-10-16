<?php
session_start();

require_once("classes.php");

$system = new System();
$guest_support = true;
$layout = 'layout/' . System::DEFAULT_LAYOUT . '.php';
$self_link = $system->link . 'support.php';
$staff_level = 0;
$user_id = 0;

if(isset($_SESSION['user_id'])) {
    $guest_support = false;
    $player = new User($_SESSION['user_id']);
    $player->loadData();
    $layout = $system->fetchLayoutByName($player->layout);
    $staff_level = $player->staff_level;
    $user_id = $player->user_id;
}

$supportSystem = new SupportManager($system, $user_id);
$request_types = $supportSystem->getSupportTypes($staff_level);

require($layout);

echo $heading;
echo $top_menu;
echo $header;
echo str_replace("[HEADER_TITLE]", "Support", $body_start);

if(!$guest_support) {
    //Form submitted
    if(isset($_POST['add_support'])) {
        try {
            $request_type = $system->clean($_POST['support_type']);
            $subject = $system->clean($_POST['subject']);
            $subjectLength = strlen($subject);
            $message = $system->clean($_POST['message']);
            $messageLength = strlen($message);

            // Validate support
            if (!in_array($request_type, $request_types)) {
                throw new Exception("Invalid support type!");
            }
            if ($subjectLength < SupportManager::$validationConstraints['subject']['min']) {
                throw new Exception("Subject must be at least " .
                    SupportManager::$validationConstraints['subject']['min'] . " characters!");
            }
            if ($subjectLength > SupportManager::$validationConstraints['subject']['max']) {
                throw new Exception("Subject must not exceed " .
                    SupportManager::$validationConstraints['subject']['max'] . " characters!");
            }
            if ($messageLength < SupportManager::$validationConstraints['message']['min']) {
                throw new Exception("Content must be at least " .
                    SupportManager::$validationConstraints['message']['min'] . " characters!");
            }
            if ($messageLength > SupportManager::$validationConstraints['message']['max']) {
                throw new Exception("Content must not exceed " .
                    SupportManager::$validationConstraints['message']['max'] . " characters!");
            }

            if($supportSystem->createSupport($player->current_ip, '', $request_type, $subject, $message,
                    $user_id, $player->user_name)) {
                $system->message("Support Submitted!");
            }
        }catch (Exception $e) {
            $system->message($e->getMessage());
        }
    }

    if($system->message && !$system->message_displayed) {
        $system->printMessage();
    }
    if(!isset($_GET['support_id'])) {
        // New Ticket form
        require('templates/supportTicketForm.php');

        // Load user tickets
        $supports = $supportSystem->fetchUserSupports($user_id);
        if (!empty($supports)) {
            require('templates/userTickets.php');
        }
    } else {
        $support_id = $system->clean($_GET['support_id']);
        $support = $supportSystem->fetchSupportByID($support_id, $user_id);

        if(!$support) {
            $system->message("Support not found!");
            $system->printMessage();
        } else {
            if(isset($_POST['add_response']) || isset($_POST['close_ticket'])) {
                try {
                    $responseType = (isset($_POST['add_response'])) ? 'response' : 'close';
                    $message = $system->clean($_POST['message']);
                    $messageLength = strlen($message);

                    // Validate
                    if ($messageLength < SupportManager::$validationConstraints['message']['min']) {
                        throw new Exception("Content must be at least " .
                            SupportManager::$validationConstraints['message']['min'] . " characters!");
                    }
                    if ($messageLength > SupportManager::$validationConstraints['message']['max']) {
                        throw new Exception("Content must not exceed " .
                            SupportManager::$validationConstraints['message']['max'] . " characters!");
                    }

                    if ($supportSystem->addSupportResponses($support_id, $user_id, $player->user_name, $message, $player->current_ip,)) {
                        if($responseType == 'response') {
                            $system->message("Response added!");
                        } else {
                            if($supportSystem->closeSupport($support_id)) {
                                $system->message("Support closed.");
                            }
                        }
                    } else {
                        throw new Exception("Error adding response!");
                    }
                } catch (Exception $e) {
                    $system->message($e->getMessage());
                }
            }

            $responses = $supportSystem->fetchSupportResponses($support_id);
            $self_link .= "?support_id=" . $support_id;
            if($system->message && !$system->message_displayed) {
                $system->printMessage();
            }
            require('templates/displayTicket.php');
        }
    }

    // Load side menu
    $pages = require_once('pages.php');

    if ($player->clan) {
        $pages[20]['menu'] = System::MENU_VILLAGE;
    }
    if ($player->rank >= 3) {
        $pages[24]['menu'] = System::MENU_USER;
    }

    echo $side_menu_start;
    foreach ($pages as $id => $page) {
        if (!isset($page['menu']) || $page['menu'] != System::MENU_USER) {
            continue;
        }

        echo "<li><a id='sideMenuOption-" . str_replace(' ', '', $page['title']) . "' href='{$system->link}?id=$id'>" . $page['title'] . "</a></li>";
    }

    echo $action_menu_header;
    if ($player->in_village) {
        foreach ($pages as $id => $page) {
            if (!isset($page['menu']) || $page['menu'] != 'activity') {
                continue;
            }
            // Page ok if an in-village page or player rank is below chuunin
            if ($page['village_ok'] != System::NOT_IN_VILLAGE || $player->rank < 3) {
                echo "<li><a id='sideMenuOption-" . str_replace(' ', '', $page['title']) . "' href='{$system->link}?id=$id'>" . $page['title'] . "</a></li>";
            }
        }
    } else {
        foreach ($pages as $id => $page) {
            if (!isset($page['menu']) || $page['menu'] != 'activity') {
                continue;
            }
            if ($page['village_ok'] != System::ONLY_IN_VILLAGE) {
                echo "<li><a id='sideMenuOption-" . str_replace(' ', '', $page['title']) . "' href='{$system->link}?id=$id'>" . $page['title'] . "</a></li>";
            }
        }
    }

// In village or not
    if ($player->in_village) {
        echo $village_menu_start;
        foreach ($pages as $id => $page) {
            if (!isset($page['menu']) || $page['menu'] != System::MENU_VILLAGE) {
                continue;
            }

            echo "<li><a id='sideMenuOption-" . str_replace(' ', '', $page['title']) . "' href='{$system->link}?id=$id'>" . $page['title'] . "</a></li>";
        }
    }

    if ($player->isModerator() || $player->hasAdminPanel()) {
        echo $staff_menu_header;
        if ($player->isModerator()) {
            echo "<li><a id='sideMenuOption-ModPanel' href='{$system->link}?id=16'>Mod Panel</a></li>
            <li><a id='sideMenuOption-ModPanel' href='{$system->link}?id=30'>Support Panel</a></li>";
        }
        if ($player->hasAdminPanel()) {
            echo "<li><a id='sideMenuOption-AdminPanel' href='{$system->link}?id=17'>Admin Panel</a></li>";
        }
    }

// Logout timer
    $logout_limit = System::LOGOUT_LIMIT;
    $time_remaining = ($logout_limit * 60) - (time() - $player->last_login);
    $logout_time = System::timeRemaining($time_remaining, 'short', false, true) . " remaining";

    $logout_display = $player->isUserAdmin() ? "Disabled" : $logout_time;
    echo str_replace("<!--LOGOUT_TIMER-->", $logout_display, $side_menu_end);

    if ($logout_display != "Disabled") {
        echo "<script type='text/javascript'>countdownTimer($time_remaining, 'logoutTimer');</script>";
    }
} else {

    echo $login_menu;
}

echo $footer;