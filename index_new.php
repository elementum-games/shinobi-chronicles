<?php
/**
 * @var System $system
 * @var User $player
 * @var Layout $layout
 */

    // WIP - fork of Index.php to test new_geisha
    require($layout->headerModule);
    echo $layout->heading;

    // Load page or news
    if($LOGGED_IN) {
        // Master close
        if(!$system->SC_OPEN && !$player->isUserAdmin()) {
            require($layout->sidebarModule);
            echo '<div id="content_wrapper">';
            require($layout->topbarModule);
            echo str_replace("[HEADER_TITLE]", "Profile", $layout->body_start);

            echo "<table class='table'><tr><th>Game Maintenance</th></tr>
		<tr><td style='text-align:center;'>
		Shinobi-Chronicles is currently closed for maintenace. Please check back in a few minutes!
		</td></tr></table>";
            echo '</div>';
            echo str_replace('<!--[VERSION_NUMBER]-->', System::VERSION_NUMBER, $layout->footer);
            exit;
        }

        // Check for ban
        if($player->checkBan(StaffManager::BAN_TYPE_GAME)) {
            $ban_type = StaffManager::BAN_TYPE_GAME;
            $expire_int = $player->ban_data[$ban_type];
            $ban_expire = ($expire_int == StaffManager::PERM_BAN_VALUE ? $expire_int : $system->time_remaining($player->ban_data[StaffManager::BAN_TYPE_GAME] - time()));

            //Display header
            require($layout->sidebarModule);
            echo '<div id="content_wrapper">';
            require($layout->topbarModule);
            echo str_replace("[HEADER_TITLE]", "Profile", $layout->body_start);

            //Ban info
            require 'templates/ban_info.php';
            echo '</div>';
            // Footer
            echo "</div>";
            echo str_replace('<!--[VERSION_NUMBER]-->', System::VERSION_NUMBER, $layout->footer);
            exit;
        }

        $result = $system->query("SELECT `id` FROM `banned_ips` WHERE `ip_address`='" . $system->clean($_SERVER['REMOTE_ADDR']) . "' LIMIT 1");
        if($system->db_last_num_rows > 0) {
            $ban_type = StaffManager::BAN_TYPE_IP;
            $expire_int = -1;
            $ban_expire = ($expire_int == StaffManager::PERM_BAN_VALUE ? $expire_int : $system->time_remaining($player->ban_data[StaffManager::BAN_TYPE_GAME] - time()));

            //Display header
            require($layout->sidebarModule);
            echo '<div id="content_wrapper">';
            require($layout->topbarModule);
            echo str_replace("[HEADER_TITLE]", "Profile", $layout->body_start);

            //Ban info
            require 'templates/ban_info.php';
            echo '</div>';
            // Footer
            echo "</div>";
            echo str_replace('<!--[VERSION_NUMBER]-->', System::VERSION_NUMBER, $layout->footer);
            exit;
        }

        // Global message
        if(!$player->global_message_viewed && isset($_GET['clear_message'])) {
            $player->global_message_viewed = 1;
        }

        // Load rank data// Rank names
        $RANK_NAMES = RankManager::fetchNames($system);

        // Route list
        $routes = Router::$routes;

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

        if(isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $route = Router::$routes[$id] ?? null;

            try {
                $system->router->assertRouteIsValid($route, $player);

                // Force view battle page if waiting too long
                if($player->battle_id && empty($route->battle_type)) {
                    $battle_result = $system->query(
                        "SELECT winner, turn_time, battle_type FROM battles WHERE `battle_id`='{$player->battle_id}' LIMIT 1"
                    );
                    if($system->db_last_num_rows) {
                        $battle_data = $system->db_fetch($battle_result);
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

                $location_name = $player->current_location->location_id
                    ? ' ' . ' <div id="contentHeaderLocation">' . $player->current_location->name . '</div>'
                    : null;
                require($layout->sidebarModule);
                echo '<div id="content_wrapper">';
                require($layout->topbarModule);
                echo str_replace("[HEADER_TITLE]", $route->title . $location_name, $layout->body_start);

                $self_link = $system->router->base_url . '?id=' . $id;

                $system->printMessage();
                if(!$player->global_message_viewed) {
                    $global_message = $system->fetchGlobalMessage();
                    $layout->renderGlobalMessage($system, $global_message);
                }

                // EVENT
                if($system::$SC_EVENT_ACTIVE) {
                    require 'templates/temp_event_header.php';
                }

                require('pages/' . $route->file_name);

                ($route->function_name)();
                echo '</div>';

                $page_loaded = true;
            }
            catch (Exception $e) {
                if(strlen($e->getMessage()) > 1) {
                    // Display page title if page is set
                    if($routes[$id] != null) {
                    require($layout->sidebarModule);
                    echo '<div id="content_wrapper">';
                    require($layout->topbarModule);
                    echo str_replace("[HEADER_TITLE]", $route->title, $layout->body_start);
                        $page_loaded = true;
                    }
                    $system->message($e->getMessage());
                    $system->printMessage();
                    echo '</div>';
                }
            }
        }

        if(!$page_loaded) {
            require($layout->sidebarModule);
            echo '<div id="content_wrapper">';
            require($layout->topbarModule);
            echo str_replace("[HEADER_TITLE]", "Profile", $layout->body_start);

            $system->printMessage();
            if(!$player->global_message_viewed) {
                $global_message = $system->fetchGlobalMessage();
                $layout->renderGlobalMessage($system, $global_message);
            }

            try {
                require("pages/profile.php");
                userProfile();
            }
            catch(Exception $e) {
                $system->message($e->getMessage());
                $system->printMessage(true);
            }
        }
        $player->updateData();

        echo "</div></div>";
    }
    // Login
    else {
        echo str_replace("[HEADER_TITLE]", "News", $layout->body_start);
        // Display error messages
        $system->printMessage();
        if(!$system->SC_OPEN) {
            echo "<table class='table'><tr><th>Game Maintenance</th></tr>
		<tr><td style='text-align:center;'>
		Shinobi-Chronicles is currently closed for maintenace. Please check back in a few minutes!
		</td></tr></table>";
        }

        require("pages/news.php");
        newsPosts();

        $captcha = '';
        echo str_replace('<!--CAPTCHA-->', $captcha, $layout->login_menu);
    }

    // End content
    echo "</div>";

    // Render hotbar
    if (isset($player)) {
        if ($system->environment == System::ENVIRONMENT_DEV) {
            require($layout->hotbarModule);
        }
    }

    // Render footer
    $page_load_time = round(microtime(true) - $PAGE_LOAD_START, 3);
    $layout->renderFooter($page_load_time);
