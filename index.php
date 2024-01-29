<?php
// Begin session & page load time
session_start();
$PAGE_LOAD_START = microtime(as_float: true);

// Load system
require_once("classes/_autoload.php");
$system = System::initialize();

// Display errors on dev
if($system->isDevEnvironment()) {
    ini_set(option: 'display_errors', value: 'On');
}
// Display errors on production for developers
if(isset($_SESSION['user_id']) && in_array($_SESSION['user_id'], System::$developers)) {
    ini_set(option: 'display_errors', value: 'On');
}

// Check for logout
if(isset($_GET['logout']) && $_GET['logout'] == 1) {
    Auth::processLogout(system: $system);
}

// Logged out
if(!isset($_SESSION['user_id'])) {
    $route = Router::$routes['home'];
    $system->layout->renderBeforeContentHTML(
        system: $system,
        player: null, // No need to check here, no session set
        page_title: $route->title,
        render_header: false, render_sidebar: false,
        render_topbar: false, render_content: false
    );

    require (__DIR__ . '/pages/' . $route->file_name);
    ($route->function_name)();

    // Calc page load time
    $PAGE_LOAD_TIME = microtime(as_float: true) - $PAGE_LOAD_START;
    $system->layout->renderAftercontentHTML(
        system: $system,
        player: null, // No need to check, no session started
        page_load_time: $PAGE_LOAD_TIME,
        render_content: false
    );
    $system->db->commitTransaction();
    exit;
}
// Logged in
else {
    $system->db->startTransaction();
    $player = User::loadFromId(
        system: $system,
        user_id: $_SESSION['user_id']
    );

    // Check for logout
    if($player->last_login < time() - (System::LOGOUT_LIMIT * 60)) {
        Auth::processLogout(system: $system);
        exit;
    }

    // Load player data & close session as user is loaded
    $player->loadData();
    session_write_close();

    // Set layout
    $layout = $system->setLayoutByName($player->layout);

    // Master system closure
    if(!$system->SC_OPEN && !StaffManager::hasServerMaintAccess(staff_level: $player->staff_level)) {
        $system->layout->renderBeforeContentHTML(
            system: $system,
            player: $player ?? null,
            page_title: 'Home', render_header: $player ? true : false,
            render_sidebar: false, render_topbar: false, render_content: false
        );

        $route = Router::$routes['home'];
        require (__DIR__ . '/pages/' . $route->file_name);
        ($route->function_name)();

        // Calc page load time
        $PAGE_LOAD_TIME = microtime(as_float: true) - $PAGE_LOAD_START;
        $system->layout->renderAftercontentHTML(
            system: $system,
            player: null, // No need to check, no session started
            page_load_time: $PAGE_LOAD_TIME,
            render_content: false
        );
        $system->db->commitTransaction();
        exit;
    }

    // Check for game ban
    if($player->checkBan(StaffManager::BAN_TYPE_GAME)) {
        $ban_type = StaffManager::BAN_TYPE_GAME;
        $expire_int = $player->ban_data[$ban_type];
        $ban_expire = ($expire_int == StaffManager::PERM_BAN_VALUE ? $expire_int : $system->time_remaining($player->ban_data[StaffManager::BAN_TYPE_GAME] - time()));

        //Display header
        $layout->renderBeforeContentHTML($system, $player, "Profile");

        //Ban info
        require 'templates/ban_info.php';

        // Load user record
        $route = Router::$routes[34];
        require 'pages/' . $route->file_name;
        ($route->function_name)();

        // Footer
        $layout->renderAfterContentHTML($system, $player);
        exit;
    }

    $result = $system->db->query(
        "SELECT `id` FROM `banned_ips` WHERE `ip_address`='" . $system->db->clean($_SERVER['REMOTE_ADDR']) . "' LIMIT 1"
    );
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
    }

    // Clear global message
    if(!$player->global_message_viewed && isset($_GET['clear_message'])) {
        $player->global_message_viewed = 1;
    }

    // Log actions
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

    // Load requested page
    $RENDER_CONTENT = true;
    if(isset($_GET['home'])) {
        $route = Router::$routes['home'];
        $RENDER_CONTENT = false;
        $system->layout->renderBeforeContentHTML(
            system: $system,
            player: null, // No need to check here, no session set
            page_title: 'Home', render_header: $player ? true : false,
            render_sidebar: false, render_topbar: false, render_content: $RENDER_CONTENT
        );

        require (__DIR__ . '/pages/' . $route->file_name);
        ($route->function_name)();
    }
    else {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : Router::PAGE_IDS['profile'];
        $route = Router::$routes[$id] ?? null;

        try {
            // Load page title
            if($system->layout->usesV2Interface()) {
                $location_name = $player->current_location->location_id
                    ? ' ' . ' <div id="contentHeaderLocation">' . " | " . $player->current_location->name . '</div>'
                    : null;
                $location_coords = "<div id='contentHeaderCoords'>" . " | " . $player->region->name . " (" . $player->location->x . "." . $player->location->y . ")" . '</div>';
                $content_header_divider = '<div class="contentHeaderDivider"><svg width="100%" height="2"><line x1="0%" y1="1" x2="100%" y2="1" stroke="#77694e" stroke-width="1"></line></svg></div>';
            }
            else {
                $location_name = $player->current_location->location_id
                    ? ' ' . ' <div id="contentHeaderLocation">' . $player->current_location->name . '</div>'
                    : null;
                $location_coords = null;
                $content_header_divider = null;
            }
            $page_title = $route->title . $location_name . $location_coords . $content_header_divider;

            // Force battle view if waiting too long
            if($player->battle_id && empty($route->battle_type)) {
                $battle_result = $system->db->query(
                    "SELECT winner, turn_time, battle_type FROM battles WHERE `battle_id`='{$player->battle_id}' LIMIT 1"
                );
                if($system->db->last_num_rows) {
                    $battle_data = $system->db->fetch($battle_result);
                    $time_since_turn = time() - $battle_data['turn_time'];

                    if($battle_data['winner'] && $time_since_turn >= 60) {
                        foreach(Router::$routes as $page_id => $page) {
                            $type = $page->battle_type ?? null;
                            if($type == $battle_data['battle_type']) {
                                $id = $page_id;
                                $route = Router::$routes[$id];
                            }
                        }
                    }
                }
            }

            // Check for valid route & permissions
            try {
                $system->router->assertRouteIsValid(route: $route, player: $player);
            } catch(RuntimeException $e) {
                $system->message($e->getMessage());
                $system->layout->renderBeforeContentHTML(system: $system, player: $player ?? null, page_title: '');
                $system->printMessage(force_display: true);
                exit;
            }

            // Set self link
            $self_link = $system->router->base_url . '?id=' . $id;

            // Render page
            $system->layout->renderBeforeContentHTML(
                system: $system, player: $player ?? null, page_title: $page_title
            );

            // Legacy event notification
            if(!$system->layout->usesV2Interface() && !is_null($system->event)) {
                require_once ('templates/temp_event_header.php');
            }

            require (__DIR__ . '/pages/' . $route->file_name);
            try {
                ($route->function_name)();
            } catch(DatabaseDeadlockException $e) {
                // Wait random time between 100-500ms, then retry deadlocked transaction
                $system->db->rollbackTransaction();
                usleep(mt_rand(100000, 500000));

                $system->db->startTransaction();
                $player->loadData();
                ($route->function_name)();
            }
        } catch (Exception $e) {
            if($e instanceof DatabaseDeadlockException) {
                error_log("DEADLOCK - retry did not solve");
                $system->db->rollbackTransaction();
                $system->message("Database deadlock, please reload your page and tell Lsm to fix!");
                $system->printMessage(true);
            }
            elseif(strlen($e->getMessage()) > 1) {
                $system->db->rollbackTransaction();
                $system->message($e->getMessage());
                $system->printMessage(true);
            }
        }
    }

    // Render after content
    $PAGE_LOAD_TIME = microtime(as_float: true) - $PAGE_LOAD_START;
    $system->layout->renderAfterContentHTML(
        system: $system, player: $player ?? null,
        page_load_time: $PAGE_LOAD_TIME,
        render_content: $RENDER_CONTENT
    );

    $player->updateData();
    $system->db->commitTransaction();
}