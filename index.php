<?php
// Begin session
session_start();
$PAGE_LOAD_START = microtime(as_float: true);

// Load system
require_once("classes/_autoload.php");
$system = new System();
$LoginManager = LoginManager::loadLoginManager($system);

// Display errors on dev
if($system->isDevEnvironment()) {
    ini_set('display_errors', 'On');
}
// Display errors on production
else {
    if(isset($_SESSION['user_id']) && !in_array($_SESSION['user_id'], System::DEVELOPER_IDS)) {
        ini_set('display_errors', 'Off');
    }
}

// Logout check - Logout logic must preceed session checks
if(isset($_GET['logout']) && $_GET['logout'] == 1) {
    LoginManager::logout(system: $system);
}

// Process login, registration, activation, password reset
if(!isset($_SESSION['user_id'])) {
    $system->db->startTransaction();
    // File containing login, register, activation and password reset logic
    /*** NOTE: This must come before the display logic to avoid redundant calls ***/
    require('login.php');
}

/*** NOTE: This redudant session check is INTENTIONAL to avoid multiple checks / extra variables ***/
// Logged in display
if(isset($_SESSION['user_id'])) {
    $system->db->startTransaction();
    
    // Load player
    $player = User::loadFromId(system: $system, user_id: $_SESSION['user_id']);
    $player->loadData();

    // Check logout timer
    if($player->last_login < time() - (System::LOGOUT_LIMIT * 60)) {
        LoginManager::logout(system: $system);
    }

    // Load home page dependencies
    $NewsManager = new NewsManager(system: $system, player: $player);
    $layout = $system->setLayoutByName($player->layout);

    // SC Offline - This will boot players from server
    if(!$system->SC_OPEN && !$player->staff_manager->isUserAdmin()) {
        LoginManager::logout($system);
    }
    
    // Game ban check
    if($player->checkBan(StaffManager::BAN_TYPE_GAME)) {
        $ban_type = StaffManager::BAN_TYPE_GAME;
        $expire_int = $player->ban_data[$ban_type];
        $ban_expire = ($expire_int == StaffManager::PERM_BAN_VALUE ? $expire_int : $system->time_remaining($player->ban_data[StaffManager::BAN_TYPE_GAME] - time()));

        //Display header
        $layout->renderBeforeContentHTML(vsystem: $system, user: $player,  page_title: "Profile");

        //Ban info
        require 'templates/ban_info.php';

        // Footer
        $layout->renderAfterContentHTML(system: $system, user: $player);
        exit;
    }

    // Location (IP) ban
    $result = $system->db->query("SELECT `id` FROM `banned_ips` WHERE `ip_address`='" . $system->db->clean($_SERVER['REMOTE_ADDR']) . "' LIMIT 1");
    if($system->db->last_num_rows > 0) {
        $ban_type = StaffManager::BAN_TYPE_IP;
        $expire_int = -1;
        $ban_expire = ($expire_int == StaffManager::PERM_BAN_VALUE ? $expire_int : $system->time_remaining($player->ban_data[StaffManager::BAN_TYPE_GAME] - time()));

        $layout->renderBeforeContentHTML($system, $player, "Profile");

        //Ban info
        require 'templates/ban_info.php';

        // Footer
        $layout->renderAfterContentHTML($system, $player);
        exit;

        // Action log
        if($player->log_actions) {
            $log_contents = '';
            if($_GET['id'] && isset($routes[$_GET['id']])) {
                $log_contents .= 'Page: ' . $routes[$_GET['id']]['title'] . ' - Time: ' . round(microtime(true), 1) . '[br]';
            }
            foreach($_GET as $key => $value) {
                $val = $value;
                if($key == 'id') {
                    continue;
                }
                if(strlen($val) > 32) {
                    $val = substr($val, 0, 32) . '...';
                }
                $log_contents .= $key . ': ' . $val . '[br]';
            }
            foreach($_POST as $key => $value) {
                $val = $value;
                if(strpos($key, 'password') !== false) {
                    $val = '*******';
                }
                if(strlen($val) > 32) {
                    $val = substr($val, 0, 32) . '...';
                }
                $log_contents .= $key . ': ' . $val . '[br]';
            }
            $system->log('player_action', $player->user_name, $log_contents);
        }

        // Pre-content display
        $page_loaded = false;

        // Attempt requested page
        if(isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $route = Router::$routes[$id] ?? null;
    
            try {
                if ($layout->usesV2Interface()) {
                    $location_name = $player->current_location->location_id
                        ? ' ' . ' <div id="contentHeaderLocation">' . " | " . $player->current_location->name . '</div>'
                        : null;
                    $location_coords = "<div id='contentHeaderCoords'>" . " | " . $player->region->name . " (" . $player->location->x . "." . $player->location->y . ")" . '</div>';
                    $content_header_divider = '<div class="contentHeaderDivider"><svg width="100%" height="2"><line x1="0%" y1="1" x2="100%" y2="1" stroke="#77694e" stroke-width="1"></line></svg></div>';
                } else {
                    $location_name = $player->current_location->location_id
                        ? ' ' . ' <div id="contentHeaderLocation">' . $player->current_location->name . '</div>'
                        : null;
                    $location_coords = null;
                    $content_header_divider = null;
                }
    
                $layout->renderBeforeContentHTML(
                    system: $system,
                    player: $player,
                    page_title: $route->title . $location_name . $location_coords . $content_header_divider,
                );
    
                $system->router->assertRouteIsValid($route, $player);
    
                // Force view battle page if waiting too long
                if($player->battle_id && empty($route->battle_type)) {
                    $battle_result = $system->db->query(
                        "SELECT winner, turn_time, battle_type FROM battles WHERE `battle_id`='{$player->battle_id}' LIMIT 1"
                    );
                    if($system->db->last_num_rows) {
                        $battle_data = $system->db->fetch($battle_result);
                        $time_since_turn = time() - $battle_data['turn_time'];
    
                        if($battle_data['winner'] && $time_since_turn >= 60) {
                            foreach($routes as $page_id => $page) {
                                $type = $page->battle_type ?? null;
                                if($type == $battle_data['battle_type']) {
                                    $id = $page_id;
                                }
                            }
                        }
                    }
                }
    
                $self_link = $system->router->base_url . '?id=' . $id;
    
                // EVENT
                if($system->event != null) {
                    if (!$layout->usesV2Interface()) {
                        require 'templates/temp_event_header.php';
                    }
                }
    
                require('pages/' . $route->file_name);
    
                try {
                    ($route->function_name)();
                } catch (DatabaseDeadlockException $e) {
                    // Wait 1ms, then retry deadlocked transaction
                    $system->db->rollbackTransaction();
                    usleep(1000);
    
                    $system->db->startTransaction();
                    $player->loadData();
                    ($route->function_name)();
                }
    
                $page_loaded = true;
            } catch (Exception $e) {
                if($e instanceof DatabaseDeadlockException) {
                    error_log("DEADLOCK - retry did not solve");
                    $system->db->rollbackTransaction();
                    $system->message("Database deadlock, please reload your page and tell Lsm to fix!");
                    $system->printMessage(true);
                }
                else if(strlen($e->getMessage()) > 1) {
                    $system->db->rollbackTransaction();
                    $system->message($e->getMessage());
                    $system->printMessage(true);
                }
            }
        }
        else if (isset($_GET['home'])) {
            $LoginManager->initial_home_view = "default";
            if (isset($_GET['view'])) {
                switch ($_GET['view']) {
                    case "news":
                        $LoginManager->initial_home_view = "news";
                        break;
                    case "contact":
                        $LoginManager->initial_home_view = "contact";
                        break;
                    case "rules":
                        $LoginManager->initial_home_view = "rules";
                        break;
                    case "terms":
                        $LoginManager->initial_home_view = "terms";
                        break;
                }
            }
            $layout->renderBeforeContentHTML(
                system: $system,
                user: $player ?? null,
                page_title: "Home",
                render_content: false,
                render_header: true,
                render_sidebar: false,
                render_topbar: false
            );
    
            try {
                require('./templates/home.php');
            } catch (RuntimeException $e) {
                $system->db->rollbackTransaction();
                $system->message($e->getMessage());
                if (!$system->layout->usesV2Interface()) {
                    $system->printMessage(true);
                }
            }
    
            $layout->renderAfterContentHTML(
                system: $system, 
                user: $player ?? null, 
                render_content: false,
                render_footer: false, 
                render_hotbar: false
            );
        }
        else {
            $layout->renderBeforeContentHTML(
                system: $system,
                player: $player,
                page_title: "Profile"
            );
    
            $system->printMessage();
            if (!$player->global_message_viewed) {
                $global_message = $system->fetchGlobalMessage();
                $layout->renderGlobalMessage($system, $global_message);
            }
    
            try {
                require("pages/profile.php");
                userProfile();
            } catch (RuntimeException $e) {
                $system->db->rollbackTransaction();
                $system->message($e->getMessage());
                $system->printMessage(true);
            }
        }
    
        $player->updateData();

        // Render footer
        $page_load_time = round(microtime(true) - $PAGE_LOAD_START, 3);
        $layout->renderAfterContentHTML($system, $player ?? null, $page_load_time);

        $system->db->commitTransaction();
    }

    // Clear global message
    if(!$player->global_message_viewed && isset($_GET['clear_message'])) {
        $player->global_message_viewed = 1;
    }

    // Route list
    $routes = Router::$routes;
}
// Logged out display
else {
    // Load home page dependencies
    $NewsManager = new NewsManager(system: $system);
    $LoginManager->initial_home_view = "login";

    // Begin display
    $layout = $system->setLayoutByName(System::DEFAULT_LAYOUT);
    $layout->renderBeforeContentHTML(
        system: $system,
        player: null,
        page_title: 'Home',
        render_header: false,
        render_sidebar: false,
        render_topbar: false,
        render_content: false
    );

    // Legacy dependencies
    if(!$system->layout->usesV2Interface()) {
        $system->printMessage(force_display: true);
        // SC CLOSED
        if(!$system->SC_OPEN) {
            require('./templates/legacy_layout_stuff/SC_CLOSED.php');
        }

        // Render home page
        require('./templates/home.php');

        // Render page ned
        $page_load_time = round(microtime(as_float: true) - $PAGE_LOAD_START, 3);
        $layout->renderAfterContentHTML(
            system: $system,
            player: null,
            page_load_time: $page_load_time,
            render_content: false,
            render_footer: false,
            render_hotbar: false
        );
        $system->db->commitTransaction();
        exit;
    }
}
