<?php
session_start();

require_once("classes.php");
$system = new System();
$guest_support = true;
$layout = 'layout/' . System::DEFAULT_LAYOUT . '.php';
$self_link = $system->link . 'support.php';

if(isset($_SESSION['user_id'])) {
    $guest_support = false;
    $player = new User($_SESSION['user_id']);
    $player->loadData();
    $layout = $system->fetchLayoutByName($player->layout);
}

require($layout);

echo $heading;
echo $top_menu;
echo $header;
echo str_replace("[HEADER_TITLE]", "Support", $body_start);

if(!$guest_support) {

    // New Ticket form
    require('templates/supportTicketForm.php');

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
            echo "<li><a id='sideMenuOption-ModPanel' href='{$system->link}?id=16'>Mod Panel</a></li>";
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