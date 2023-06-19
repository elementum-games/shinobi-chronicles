<?php
/*
File: 		index.php
Coder:		Levi Meahan
Created:	02/21/2012
Revised:	11/25/2013 by Levi Meahan
Purpose:	Authenticate/deauthenticate users, and direct logged in users to pages. Central gateway for entire game.
Algorithm:	See master_plan.html
*/

//Start the session
session_start();

// Turn errors off unless Lsm
if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1 || $_SESSION['user_id'] != 190) {
	ini_set('display_errors', 'Off');
}

$PAGE_LOAD_START = microtime(true);

require_once("classes/_autoload.php");
$system = new System();

if($system->environment == System::ENVIRONMENT_DEV) {
    ini_set('display_errors', 'On');
}

// Check for logout
if(isset($_GET['logout']) && $_GET['logout'] == 1) {
	$_SESSION = array();
	if(ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
		);
	}
	session_destroy();
	header("Location: {$system->router->base_url}");
	exit;
}
$LOGGED_IN = false;

// Run login, load player data
$player_display = '';

$logout_limit = System::LOGOUT_LIMIT;
if(!isset($_SESSION['user_id'])) {
	// require("./securimage/securimage.php");
	if(!empty($_POST['login'])) {
		try {
/*			$image = new Securimage();
			if(!$image->check($_POST['login_code']) && $system->environment == 'prod') {
				throw new RuntimeException("Invalid login code!");
			}*/

			// Basic input check - user_name/password
			$user_name = $system->db->clean($_POST['user_name']);
			if(empty($user_name)) {
				throw new RuntimeException("Please enter username!");
			}
			$password = $system->db->clean($_POST['password']);
			if(empty($password)) {
				throw new RuntimeException("Please enter password!");
			}

			// Get result
			$result = $system->db->query(
                "SELECT `user_id`, `user_name`, `password`, `failed_logins`, `current_ip`, `last_ip`, `user_verified`
                    FROM `users` WHERE `user_name`='$user_name' LIMIT 1"
            );
			if($system->db->last_num_rows == 0) {
				throw new RuntimeException("User does not exist!");
			}
			$result = $system->db->fetch($result);
			if(!$result['user_verified']) {
				throw new RuntimeException("Your account has not been verified. Please check your email for the activation code.
				<a class='link' href='{$system->router->base_url}register.php?act=resend_verification&username=$user_name'>Resend Verification</a>");
			}

			// Check failed logins
			if($result['failed_logins'] >= User::PARTIAL_LOCK && $_SERVER['REMOTE_ADDR'] != $result['current_ip'] && $_SERVER['REMOTE_ADDR'] != $result['last_ip']) {
                $system->log(
                    'malicious_lockout',
                    $result['user_id'],
                    "IP address {$_SERVER['REMOTE_ADDR']} failed login on account {$result['user_name']} not matching previous IPs {$result['current_ip']} or {$result['last_ip']}."
                );

                throw new RuntimeException("Account has been locked out!");
            }
			else if($result['failed_logins'] >= User::FULL_LOCK) {
				throw new RuntimeException("Account has been locked out!");
			}

			// Check password (NOTE: Due to importance of login, it is inclusive instead of exclusive (if statement must be true for user to be logged in) )
			if($system->verify_password($password, $result['password'])) {
				$_SESSION['user_id'] = $result['user_id'];
				$LOGGED_IN = true;
				if($result['failed_logins'] > 0) {
					$system->db->query(
                        "UPDATE `users` SET `failed_logins`= 0 WHERE `user_id`='{$result['user_id']}' LIMIT 1"
                    );
				}

				$player = User::loadFromId($system, $_SESSION['user_id']);
				$player->loadData();
				$player->last_login = time();
				$player->log(User::LOG_LOGIN, $_SERVER['REMOTE_ADDR']);
				$player->updateData();
			}
			// If wrong, increment failed logins
			else {
				$system->db->query(
                    "UPDATE `users` SET `failed_logins` = `failed_logins` + 1 WHERE `user_id`='{$result['user_id']}' LIMIT 1"
                );
				throw new RuntimeException("Invalid password! <a href='./password_reset.php'>Forgot password?</a>");
			}
		} catch (Exception $e) {
            $system->db->rollbackTransaction();
			$system->message($e->getMessage());
			error_log($e->getMessage());
		}
	}
}
else {
	$LOGGED_IN = true;

    $system->db->startTransaction();
	$player = User::loadFromId($system, $_SESSION['user_id']);

    // Check logout timer
	if($player->last_login < time() - (System::LOGOUT_LIMIT * 60)) {
        $_SESSION = array();
        if(ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        header("Location: {$system->router->base_url}");
        exit;
	}

	$player->loadData();
}

// Load page or news
if($LOGGED_IN) {
    $layout = $system->fetchLayoutByName($player->layout);

    // Master close
    if(!$system->SC_OPEN && !$player->isUserAdmin()) {
        $layout->renderBeforeContentHTML($system, $player, "Profile");

        echo "<table class='table'><tr><th>Game Maintenance</th></tr>
        <tr><td style='text-align:center;'>
        Shinobi-Chronicles is currently closed for maintenace. Please check back in a few minutes!
        </td></tr></table>";

        $layout->renderAfterContentHTML($system, $player);
        exit;
    }

    // Check for ban
    if($player->checkBan(StaffManager::BAN_TYPE_GAME)) {
        $ban_type = StaffManager::BAN_TYPE_GAME;
        $expire_int = $player->ban_data[$ban_type];
        $ban_expire = ($expire_int == StaffManager::PERM_BAN_VALUE ? $expire_int : $system->time_remaining($player->ban_data[StaffManager::BAN_TYPE_GAME] - time()));

        //Display header
        $layout->renderBeforeContentHTML($system, $player, "Profile");

        //Ban info
        require 'templates/ban_info.php';

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
            $location_name = $player->current_location->location_id
                ? ' ' . ' <div id="contentHeaderLocation">' . $player->current_location->name . '</div>'
                : null;
            $layout->renderBeforeContentHTML(
                system: $system,
                player: $player,
                page_title: $route->title . $location_name
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
            if($system::$SC_EVENT_ACTIVE) {
                require 'templates/temp_event_header.php';
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
    else {
        $layout->renderBeforeContentHTML(
            system: $system,
            player: $player,
            page_title: "Profile"
        );

        $system->printMessage();
        if(!$player->global_message_viewed) {
            $global_message = $system->fetchGlobalMessage();
            $layout->renderGlobalMessage($system, $global_message);
        }

        try {
            require("pages/profile.php");
            userProfile();
        } catch(RuntimeException $e) {
            $system->db->rollbackTransaction();
            $system->message($e->getMessage());
            $system->printMessage(true);
        }
    }

    $player->updateData();
}
// Login
else {
    $layout = $system->fetchLayoutByName(System::DEFAULT_LAYOUT);
    $layout->renderBeforeContentHTML($system, null, "News");

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
}

// Render footer
$page_load_time = round(microtime(true) - $PAGE_LOAD_START, 3);
$layout->renderAfterContentHTML($system, $player ?? null, $page_load_time);

$system->db->commitTransaction();