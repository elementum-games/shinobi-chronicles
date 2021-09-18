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
	// ini_set('display_errors', 'Off');
}
ini_set('display_errors', 'On');

$PAGE_LOAD_START = microtime(true);
require_once("classes.php");
$system = new SystemFunctions();

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
	header("Location: {$system->link}");
	exit;
}
$LOGGED_IN = false;


// Ajax
$ajax = false;
if(isset($_GET['request_type']) && $_GET['request_type'] == 'ajax') {
	$ajax = true;
}
// Run login, load player data
$player_display = '';

$logout_limit = SystemFunctions::LOGOUT_LIMIT;
if(!isset($_SESSION['user_id'])) {
	// require("./securimage/securimage.php");
	if(!empty($_POST['login'])) {
		try {
/*			$image = new Securimage();
			if(!$image->check($_POST['login_code']) && $system->environment == 'prod') {
				throw new Exception("Invalid login code!");
			}*/

			// Basic input check - user_name/password
			$user_name = $system->clean($_POST['user_name']);
			if(empty($user_name)) {
				throw new Exception("Please enter username!");
			}
			$password = $system->clean($_POST['password']);
			if(empty($password)) {
				throw new Exception("Please enter password!");
			}
			// Get result
			$result = $system->query("SELECT `user_id`, `user_name`, `password`, `failed_logins`, `current_ip`, `last_ip`, `user_verified` 
				FROM `users` WHERE `user_name`='$user_name' LIMIT 1");
			if($system->db_num_rows == 0) {
				throw new Exception("User does not exist!");
			}
			$result = $system->db_fetch($result);
			if(!$result['user_verified']) {
				throw new Exception("Your account has not been verified. Please check your email for the activation code.
				<a class='link' href='{$system->link}register.php?act=resend_verification&username=$user_name'>Resend Verification</a>");
			}
			// Check failed logins
			if($result['failed_logins'] >= 3 && $_SERVER['REMOTE_ADDR'] != $result['current_ip'] && $_SERVER['REMOTE_ADDR'] != $result['last_ip']) {
				throw new Exception("Account has been locked out!");
				$system->query("INSERT INTO `logs` (`log_type`, `log_time`, `log_contents`)
					VALUES ('malicious_lockout', '" . time() . "', 'IP address " . $_SERVER['REMOTE_ADDR'] . " failed login on account " .
					$result['user_name'] . " not matching previous IPs " . $result['current_ip'] . " or " . $result['last_ip'] . ".'");
			}
			else if($result['failed_logins'] >= 5) {
				throw new Exception("Account has been locked out!");
			}

			// Check password (NOTE: Due to importance of login, it is inclusive instead of exclusive (if statement must be true for user to be logged in) )
			if($system->verify_password($password, $result['password'])) {
				$_SESSION['user_id'] = $result['user_id'];
				$LOGGED_IN = true;
				if($result['failed_logins'] > 0) {
					$system->query("UPDATE `users` SET `failed_logins`= 0 WHERE `user_id`='{$result['user_id']}' LIMIT 1");
				}
				$player = new User($_SESSION['user_id']);
				$player_display = $player->loadData();
				$player->last_login = time();
				$player->updateData();
			}
			// If wrong, increment failed logins
			else {
				$system->query("UPDATE `users` SET `failed_logins` = `failed_logins` + 1 WHERE `user_id`='{$result['user_id']}' LIMIT 1");
				throw new Exception("Invalid password! <a href='./password_reset.php'>Forgot password?</a>");
			}
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
	}
}
else {
	$LOGGED_IN = true;
	$player = new User($_SESSION['user_id']);
	//This is in minutes.
	$logout_display = false;
	if($player->staff_level == SystemFunctions::SC_ADMINISTRATOR) {
		$logout_limit = 1440;
		$logout_display = "Disabled";
	}
	else if($player->forbidden_seal) {
		$logout_limit *= 2;
	}
	// Check logout timer
	if($player->last_login < time() - ($logout_limit * 60)) {
		if($ajax) {	
			echo "<script type='text/javascript'>
			clearInterval(refreshID);
			clearInterval(notificationRefreshID);
			</script>
			<p style='text-align:center;'>Logout timer finished. <a href='{$system->link}'>Continue</a></p>";
			exit;
		}
		else {
			$_SESSION = array();
			if(ini_get("session.use_cookies")) {
				$params = session_get_cookie_params();
				setcookie(session_name(), '', time() - 42000,
					$params["path"], $params["domain"],
					$params["secure"], $params["httponly"]
				);
			}
			session_destroy();
			header("Location: {$system->link}");
			exit;
		}
	}
	if($ajax) {
		$player_display = $player->loadData(1);
	}
	else {
		$player_display = $player->loadData();
	}
}

// Start display
if(!$LOGGED_IN) {
	$layout = SystemFunctions::DEFAULT_LAYOUT;
}
else {
	$layout = $player->layout;
}
switch($layout) {
  case 'cextralite':
		require("layout/cextralite.php");
		break;
	case 'classic_blue':
		require("layout/classic_blue.php");
		break;
	case 'shadow_ribbon':
		require("layout/shadow_ribbon.php");
		break;
	case 'geisha':
		require("layout/geisha.php");
		break;
	default:
		require("layout/classic_blue.php");
		break;
}
if(!$ajax) {
	echo $heading;
	echo $top_menu;
	echo $header;
}
// Array to allow access to dev folders of coders
$allowed_coders = array(
	'lsmjudoka'
);
// Load page or news
if($LOGGED_IN) {
	// Master close
	if(!$system->SC_OPEN && $player->staff_level < SystemFunctions::SC_ADMINISTRATOR) {
		if(!$ajax) {
			echo str_replace("[HEADER_TITLE]", "Profile", $body_start);
		}
		echo "<table class='table'><tr><th>Game Maintenance</th></tr>
		<tr><td style='text-align:center;'>
		Shinobi-Chronicles is currently closed for maintenace. Please check back in a few minutes!
		</td></tr></table>";
		if(!$ajax) {
			echo $side_menu_start . $side_menu_end;
			echo str_replace('<!--[VERSION_NUMBER]-->', SystemFunctions::VERSION_NUMBER, $footer);
		}
		exit;
	}	
	if($player->ban_type == 'game') {
		$ban_time = $player->ban_expire - time();
		$ban_message = 'You are currently banned from the game. Time remaining: ';
		$ban_message .= $system->time_remaining($ban_time);
		if(!$ajax) {
			echo str_replace("[HEADER_TITLE]", "Profile", $body_start);
		}
		echo "<table class='table'><tr><th>Game Ban</th></tr>
		<tr><td style='text-align:center;'>
		$ban_message
		</td></tr></table>";
		if(!$ajax) {
			echo $side_menu_start . $side_menu_end;
			echo str_replace('<!--[VERSION_NUMBER]-->', SystemFunctions::VERSION_NUMBER, $footer);
		}
		exit;
	}
	$result = $system->query("SELECT `id` FROM `banned_ips` WHERE `ip_address`='" . $system->clean($_SERVER['REMOTE_ADDR']) . "' LIMIT 1");
	if($system->db_num_rows > 0) {
		if(!$ajax) {
			echo str_replace("[HEADER_TITLE]", "Profile", $body_start);
		}
		echo "<table class='table'><tr><th>Game Ban</th></tr>
		<tr><td style='text-align:center;'>
		You are currently banned from the game. Please contact a head moderator on the forums if you have any questions.
		</td></tr></table>";
		if(!$ajax) {
			echo $side_menu . $menu_end . $footer;
		}
		exit;
	}

	// Notifications
	if(!$ajax) {
		require("notifications.php");
		displayNotifications();
		echo "<script type='text/javascript'>
		var notificationRefreshID = setInterval('javascript:$(\'#notifications\').load(\'./ajax_notifications.php\');', 5000);
		</script>";
	}
	// Global message
	if(!$player->global_message_viewed && isset($_GET['clear_message'])) {
		$player->global_message_viewed = 1;
	}
	if(!$player->global_message_viewed && !$ajax) {
		$result = $system->query("SELECT `global_message` FROM `system_storage` LIMIT 1");
		$message = $system->db_fetch($result)['global_message'];
		$global_message = str_replace("\r\n", "<br />", $message);
	}
	else {
		$global_message = false;
	}

	// Load village list
	$result = $system->query("SELECT `name`, `location` FROM `villages`");
	$villages = array();
	$count = 0;
	while($row = $system->db_fetch($result)) {
		$villages[$row['location']] = $row;
		$villages[$row['location']]['count'] = $count++;
	}
	// Load rank data// Rank names
	$RANK_NAMES = array();
	$result = $system->query("SELECT `rank_id`, `name` FROM `ranks`");
	while($rank = $system->db_fetch($result)) {
		$RANK_NAMES[$rank['rank_id']] = $rank['name'];
	}
	// Page list 
	$pages = require 'pages.php';

	// Action log
	if($player->log_actions) {
		$log_contents = '';
		if($_GET['id'] && isset($pages[$_GET['id']])) {
			$log_contents .= 'Page: ' . $pages[$_GET['id']]['title'] . ' - Time: ' . round(microtime(true), 1) . '[br]';
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
	if($player_display) {
		echo $player_display;
	}
	$page_loaded = false;
	if(isset($_GET['id'])) {
		$id = (int)$_GET['id'];
		try {
			if(!isset($pages[$id])) {
				throw new Exception("");
			}
			// Unset battle ID if AI battle is not in session
			if($player->battle_id == -1 && !isset($_SESSION['ai_id'])) {
				$player->battle_id = 0;
			}
			// Check for battle if page is restricted
			if(isset($pages[$id]['battle_ok']) && $pages[$id]['battle_ok'] == false) {
				if(isset($_SESSION['ai_id']) || $player->battle_id) {
					throw new Exception("You cannot visit this page while in battle!");
				}
			}
			//Check for survival mission restricted
			if(isset($pages[$id]['survival_ok']) && $pages[$id]['survival_ok'] == false) {
				if(isset($_SESSION['ai_defeated']) && $player->mission_stage['action_type'] == 'combat') {
					throw new Exception("You cannot move while under attack!");
				}
			}
			// Check for PvP battle/AI type if page is restricted
			if(isset($pages[$id]['pvp_ok']) && $pages[$id]['pvp_ok'] == false) {
				// PvP
				if($player->battle_id > 0) {
					throw new Exception("You cannot visit this page while in a PvP battle!");
				}
				else if($player->battle_id == -1 && $_SESSION['battle_page'] != $id) {
					throw new Exception("You cannot visit this page while in combat!");
				}
			}
			// Check for spar/fight PvP type, stop page if trying to load spar/battle while in AI battle
			if(isset($pages[$id]['battle_type'])) {
				if($player->battle_id == -1) {
					throw new Exception("You cannot access this page while in an AI battle!");
				}
				$result = $system->query("SELECT `battle_type` FROM `battles` WHERE `battle_id`='$player->battle_id' LIMIT 1");
				if($system->db_num_rows > 0) {
					$battle_type = $system->db_fetch($result)['battle_type'];
					if($battle_type != $pages[$id]['battle_type']) {
						throw new Exception("You cannot visit this page while in combat!");
					}
				}
			}
			if(isset($pages[$id]['staff_level_required'])) {
				if($pages[$id]['staff_level_required'] == 0) {
					throw new Exception("A permissions error has occurred. Please contact an administrator.");
				}
				if($player->staff_level < $pages[$id]['staff_level_required']) {
					throw new Exception("");
				}
			}
			// Check for being in village is not okay/okay/required (0 / 1 / 2)
			if(isset($pages[$id]['village_ok'])) {
				// Player is alllowed in up to rank 3, then must go outside village
				if($player->rank > 2 && $pages[$id]['village_ok'] == SystemFunctions::NOT_IN_VILLAGE && isset($villages[$player->location])) {
					throw new Exception("You cannot access this page while in a village!");
				}
				if($pages[$id]['village_ok'] == SystemFunctions::ONLY_IN_VILLAGE && $player->location != $player->village_location) {
					throw new Exception("You must be in your village to access this page!");
				}
			}
			if(isset($pages[$id]['min_rank'])) {
				if($player->rank < $pages[$id]['min_rank']) {
					throw new Exception("You are not a high enough rank to access this page!");
				}
			}
			// Page is okay
			if(!$ajax || !isset($pages[$id]['ajax_ok']) ) {
				echo str_replace("[HEADER_TITLE]", $pages[$id]['title'], $body_start);
			}
			$self_link = $system->link . '?id=' . $id;
			$system->printMessage();
			if($global_message) {
				echo "<table class='table globalMessage'><tr><th>Global message</th></tr>
				<tr><td style='text-align:center;'>" . $system->html_parse($global_message) . "
				<br />
				<a class='link' href='{$self_link}&clear_message=1'>Dismiss</a>
				</td></tr></table>";
			}
			require($pages[$id]['file_name']);
			$pages[$id]['function_name']();
			$page_loaded = true;
		} catch (Exception $e) {
			if(strlen($e->getMessage()) > 1) {
				// Display page title if page is set
				if(isset($pages[$id])) {
					echo str_replace("[HEADER_TITLE]", $pages[$id]['title'], $body_start);
					$page_loaded = true;
				}
				$system->message($e->getMessage());
				$system->printMessage();
			}
		}
	}
	if(!$page_loaded) {
		echo str_replace("[HEADER_TITLE]", "News", $body_start);
		$system->printMessage();
		if($global_message) {
			echo "<table class='table globalMessage'><tr><th>Global message</th></tr>
			<tr><td style='text-align:center;'>" . $system->html_parse($global_message) . "
			<br />
			<a class='link' href='{$system->link}?clear_message=1'>Dismiss</a>
			</td></tr></table>";
		}
		require("news.php");
		news();
	}
	$player->updateData();
	// Display side menu and footer
	if(!$ajax) {
		if($player->clan) {
		    $pages[20]['menu'] = SystemFunctions::MENU_VILLAGE;
		}
		if($player->rank >= 3) {
		    $pages[24]['menu'] = SystemFunctions::MENU_USER;
		}


        echo $side_menu_start;
		foreach($pages as $id => $page) {
            if(!isset($page['menu']) || $page['menu'] != SystemFunctions::MENU_USER) {
                continue;
            }

            echo "<li><a href='{$system->link}?id=$id'>" . $page['title'] . "</a></li>";
        }

		echo $action_menu_header;
		if($player->in_village) {
			foreach($pages as $id => $page) {
				if(!isset($page['menu']) || $page['menu'] != 'activity') {
					continue;
				}
				// Page ok if an in-village page or player rank is below chuunin
				if($page['village_ok'] != SystemFunctions::NOT_IN_VILLAGE || $player->rank < 3) {
					echo "<li><a href='{$system->link}?id=$id'>" . $page['title'] . "</a></li>";
				}
			}
		}
		else {
			foreach($pages as $id => $page) {
				if(!isset($page['menu']) || $page['menu'] != 'activity') {
					continue;
				}
				if($page['village_ok'] != SystemFunctions::ONLY_IN_VILLAGE) {
					echo "<li><a href='{$system->link}?id=$id'>" . $page['title'] . "</a></li>";
				}
			}
		}

        // In village or not
        if($player->in_village) {
            echo $village_menu_start;
            foreach($pages as $id => $page) {
                if(!isset($page['menu']) || $page['menu'] != SystemFunctions::MENU_VILLAGE) {
                    continue;
                }

                echo "<li><a href='{$system->link}?id=$id'>" . $page['title'] . "</a></li>";
            }
        }

		if($player->staff_level >= SystemFunctions::SC_MODERATOR) {
			echo $staff_menu_header;
			echo "<li><a href='{$system->link}?id=16'>Mod Panel</a></li>";
		}
		if($player->staff_level >= SystemFunctions::SC_ADMINISTRATOR) {
			echo "<li><a href='{$system->link}?id=17'>Admin Panel</a></li>";
		}
		// Logout timer
		$time_remaining = ($logout_limit * 60) - (time() - $player->last_login);
		$logout_time = SystemFunctions::timeRemaining($time_remaining, 'short', false, true) . " remaining";
		$logout_display = ($logout_display) ? $logout_display : $logout_time;
		echo str_replace("<!--LOGOUT_TIMER-->", $logout_display, $side_menu_end);

		if($logout_display != "Disabled") {
			echo "<script type='text/javascript'>countdownTimer($time_remaining, 'logoutTimer');</script>";
		}
	}
}
else if($ajax) {
	echo "<script type='text/javascript'>
			clearInterval(refreshID);
			clearInterval(notificationRefreshID);
			</script>
	<p style='text-align:center;'>Logout timer finished. <a href='{$system->link}'>Continue</a></p>";
}
else {
	echo str_replace("[HEADER_TITLE]", "News", $body_start);
	// Display error messages
	$system->printMessage();
	if(!$system->SC_OPEN && $player->staff_level < SystemFunctions::SC_ADMINISTRATOR) {
		echo "<table class='table'><tr><th>Game Maintenance</th></tr>
		<tr><td style='text-align:center;'>
		Shinobi-Chronicles is currently closed for maintenace. Please check back in a few minutes!
		</td></tr></table>";
	}	
	require("news.php");
	newsPosts();

    $captcha = '';
	echo str_replace('<!--CAPTCHA-->', $captcha, $login_menu);
}
if(!$ajax) {
	$page_load_time = round(microtime(true) - $PAGE_LOAD_START, 3);
	echo str_replace(
		array('<!--[VERSION_NUMBER]-->', '<!--[PAGE_LOAD_TIME]-->'),
		array(SystemFunctions::VERSION_NUMBER, $page_load_time),
	$footer);
}

