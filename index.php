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

if($system->isDevEnvironment()) {
    ini_set('display_errors', 'On');
}

// Data for Home
$login_error_text = "";
$login_message_text = "";
$register_error_text = "";
$reset_error_text = "";
$initial_home_view = "none";
$register_pre_fill = [];
$home_links = [];
$home_links['news_api'] = $system->router->api_links['news'];
$home_links['logout'] = $system->router->base_url . "?logout=1";
$home_links['profile'] = $system->router->getUrl('profile');
$home_links['github'] = $system->router->links['github'];
$home_links['discord'] = $system->router->links['discord'];
$home_links['support'] = $system->router->base_url . "support.php";

$min_user_name_length = User::MIN_NAME_LENGTH;
$max_user_name_length = 18;
$min_password_length = User::MIN_PASSWORD_LENGTH;

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
    if (!empty($_POST['login'])) {
        try {
            /*			$image = new Securimage();
            if(!$image->check($_POST['login_code']) && $system->environment == 'prod') {
            throw new RuntimeException("Invalid login code!");
            }*/

            // Basic input check - user_name/password
            $user_name = $system->db->clean($_POST['user_name']);
            if (empty($user_name)) {
                throw new RuntimeException("Please enter a username!");
            }
            $password = $system->db->clean($_POST['password']);
            if (empty($password)) {
                throw new RuntimeException("Please enter a password!");
            }

            // Get result
            $result = $system->db->query(
                "SELECT `user_id`, `user_name`, `password`, `failed_logins`, `current_ip`, `last_ip`, `user_verified`
                    FROM `users` WHERE `user_name`='$user_name' LIMIT 1"
            );
            if ($system->db->last_num_rows == 0) {
                throw new RuntimeException("User does not exist!");
            }
            $result = $system->db->fetch($result);
            if (!$result['user_verified']) {
                throw new RuntimeException("Your account has not been verified. Please check your email for the activation code or use the following link to resend your activation email: {$system->router->base_url}?act=resend_verification&username=$user_name");
            }

            // Check failed logins
            if ($result['failed_logins'] >= User::PARTIAL_LOCK && $_SERVER['REMOTE_ADDR'] != $result['current_ip'] && $_SERVER['REMOTE_ADDR'] != $result['last_ip']) {
                $system->log(
                    'malicious_lockout',
                    $result['user_id'],
                    "IP address {$_SERVER['REMOTE_ADDR']} failed login on account {$result['user_name']} not matching previous IPs {$result['current_ip']} or {$result['last_ip']}."
                );

                throw new RuntimeException("Account has been locked out1!");
            } else if ($result['failed_logins'] >= User::FULL_LOCK) {
                throw new RuntimeException("Account has been locked out2!");
            }

            // Check password (NOTE: Due to importance of login, it is inclusive instead of exclusive (if statement must be true for user to be logged in) )
            if ($system->verify_password($password, $result['password'])) {
                $_SESSION['user_id'] = $result['user_id'];
                $LOGGED_IN = true;
                if ($result['failed_logins'] > 0) {
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
                throw new RuntimeException("Invalid password!");
            }
        } catch (Exception $e) {
            $system->db->rollbackTransaction();
            $system->message($e->getMessage());
            error_log($e->getMessage());
            $login_error_text = $e->getMessage();
        }
    } else if (!empty($_POST['reset'])) {
        try {
            $con = $system->db->connect();
            $user_name = $system->db->clean($_POST['username']);
            $email = $system->db->clean($_POST['email']);
            $query = "SELECT `user_id` FROM users WHERE user_name='$user_name' AND email='$email' LIMIT 1";
            $result = $system->db->query($query);
            if ($system->db->last_num_rows == 0) {
                throw new RuntimeException("Invalid username or email address! Please submit a support request or try again.");
            } else {
                $result = $system->db->fetch($result);
                $userid = $result['user_id'];

                $hash = sha1(mt_rand(1, 1000000));
                $new_password = substr($hash, 0, 16);
                $hashed_password = $system->hash_password($new_password);
                $system->db->query("UPDATE users SET password='{$hashed_password}' WHERE user_id=$userid");

                $subject = "Shinobi Chronicles - Password Reset";
                $headers = "From: Shinobi Chronicles<" . System::SC_ADMIN_EMAIL . ">" . "\r\n";
                $message = "A password reset was requested for your account $user_name. Your temporary password is:
$new_password
You can login at {$system->router->base_url} with
your temporary password. We strongly suggest you change it to something easier to remember;
It can be changed in the settings page, found on your profile.

If this is your account but you did not request a password reset, please submit a support request: <a href='{$system->router->base_url}support.php'>here</a>.

This message was sent because someone signed up at {$system->router->base_url} with this email
address and requested a password reset. If this is not your account, please disregard this email or submit a
 <a href='{$system->router->base_url}support.php'>support.php</a> to have your address removed from our records.";
                mail($email, $subject, $message, $headers);
                $system->message("Password sent!");
                $login_message_text = "Password sent!";
            }

        } catch (Exception $e) {
            $system->db->rollbackTransaction();
            $system->message($e->getMessage());
            error_log($e->getMessage());
            $reset_error_text = $e->getMessage();
        }
    }
    else if (!empty($_GET['act'])) {
        try {
            if (!$system->register_open) {
                throw new RuntimeException("Sorry, not currently functional. Check back later.");
            }
            if ($_GET['act'] == 'verify') {
                $key = $system->db->clean($_GET['verify_key']);
                $user_name = $system->db->clean($_GET['username']);

                $result = $system->db->query(
                    "UPDATE `users` SET `user_verified`=1 WHERE `user_name`='$user_name' AND `verify_key`='$key' LIMIT 1"
                );
                if ($system->db->last_affected_rows > 0) {
                    $system->message("Account activated! You may log in and start playing. <a href='{$system->router->base_url}'>Continue</a>");
                } else {
                    $accountData = $system->db->query(
                        "SELECT `user_verified` FROM `users` WHERE `user_name`='$user_name' AND `verify_key`='$key' LIMIT 1"
                    );
                    if (!$system->db->last_num_rows) {
                        $system->message("User not found!. Please contact an administrator. Staff can be found on
                        <a href='{$system->router->links['discord']}' target='_blank'>Discord.</a>");
                    } else {
                        $accountData = $system->db->fetch($accountData);
                        if ($accountData['user_verified']) {
                            $system->message("Your account is already activated and you may login!");
                        } else {
                            $system->message("Account activation error! Please contact an administrator. Staff can be found on
                        <a href='{$system->router->links['discord']}' target='_blank'>Discord.</a>");
                        }
                    }
                }
            } else if ($_GET['act'] == 'resend_verification') {
                $user_name = $system->db->clean($_GET['username']);
                $result = $system->db->query(
                    "SELECT `email`, `verify_key`, `user_verified` FROM `users` WHERE `user_name`='$user_name' LIMIT 1"
                );
                if ($system->db->last_num_rows == 0) {
                    $system->message("Invalid user!");
                } else {
                    $result = $system->db->fetch($result);

                    $subject = "Shinobi-Chronicles account verification";
                    $message = "Welcome to Shinobi-Chronicles RPG. Please visit the link below to verify your account: \r\n" .
                        "{$system->router->base_url}register.php?act=verify&username={$user_name}&verify_key={$result['verify_key']}";
                    $headers = "From: Shinobi-Chronicles<" . System::SC_ADMIN_EMAIL . ">" . "\r\n";
                    $headers .= "Reply-To: " . System::SC_NO_REPLY_EMAIL . "\r\n";
                    if (mail($result['email'], $subject, $message, $headers)) {
                        ;
                        $system->message("Email sent! Please check your email (including spam folder)");
                    } else {
                        $system->message(
                            "There was a problem sending the email to the address provided: {$result['verify_key']}
				Please contact a staff member on the forums for manual activation."
                        );
                    }
                }
            }
            $login_message_text = $system->message;
        } catch (Exception $e) {
            $system->db->rollbackTransaction();
            $system->message($e->getMessage());
            error_log($e->getMessage());
            $login_error_text = $e->getMessage();
        }
    }
    else if (!empty($_POST['register'])) {
        try {
            if (!$system->register_open) {
                throw new RuntimeException("Sorry, not currently functional. Check back later.");
            }
            if(isset($_POST['user_name'])) {
                $user_name = $system->db->clean(trim($_POST['user_name']));
            }
            if(isset($_POST['password'])) {
                $password = trim($_POST['password']);
            }
            if(isset($_POST['confirm_password'])) {
                $confirm_password = trim($_POST['confirm_password']);
            }
            if(isset($_POST['email'])) {
                $email = $system->db->clean(trim($_POST['email']));
            }
            if(isset($_POST['gender'])) {
                $gender = trim($_POST['gender']);
            }
            if(isset($_POST['village'])) {
                $village = trim($_POST['village']);
            }

            // Username
            if(strlen($user_name) < $min_user_name_length) {
                throw new RuntimeException("Please enter a username longer than 3 characters!");
            }
            if(strlen($user_name) > $max_user_name_length) {
                throw new RuntimeException("Please enter a username shorter than " . ($max_user_name_length + 1) . " characters!");
            }

            if(!preg_match('/^[a-zA-Z0-9_-]+$/', $user_name)) {
                throw new RuntimeException("Only alphanumeric characters, dashes, and underscores are allowed in usernames!");
            }

            // Banned words
            if($system->explicitLanguageCheck($user_name)) {
                throw new RuntimeException("Inappropriate language is not allowed in usernames!");
            }

            // Password
            if(strlen($password) < $min_password_length) {
                throw new RuntimeException("Please enter a password longer than " . ($min_password_length) . " characters!");
            }

            if(preg_match('/[0-9]/', $password) == false) {
                throw new RuntimeException("Password must include at least one number!");
            }
            if(preg_match('/[A-Z]/', $password) == false) {
                throw new RuntimeException("Password must include at least one capital letter!");
            }
            if(preg_match('/[a-z]/', $password) == false) {
                throw new RuntimeException("Password must include at least one lowercase letter!");
            }
            $common_passwords = [
                'Password1',
            ];
            foreach($common_passwords as $pword) {
                if($pword == $password) {
                    throw new RuntimeException("This password is too common, please choose a more unique password!");
                }
            }

            if($password != $confirm_password) {
                throw new RuntimeException("The passwords do not match!");
            }

            // Email
            if(strlen($email) < 5) {
                throw new RuntimeException("Please enter a valid email address!");
            }

            /** @noinspection RegExpRedundantEscape */
            $email_pattern = '/^[\w\-\.\+]+@[\w\-\.]+\.[a-zA-Z]{2,4}$/';
            if(!preg_match($email_pattern, $email)) {
                throw new RuntimeException("Please enter a valid email address!");
            }

            // Check for hotmail

            $email_arr = explode('@', $email);
            $email_arr[1] = strtolower($email_arr[1]);

            if(array_search($email_arr[1], System::UNSERVICEABLE_EMAIL_DOMAINS) !== false) {
                throw new RuntimeException(implode(' / ', System::UNSERVICEABLE_EMAIL_DOMAINS) . " emails are currently not supported!");
            }

            // Check for username/email existing
            $result = $system->db->query(
                "SELECT `user_id`, `user_name`, `email` FROM `users`
                    WHERE `email`='$email' OR `user_name`='$user_name' LIMIT 1"
            );
            if(mysqli_num_rows($result) > 0) {
                $result = mysqli_fetch_assoc($result);
                if(strtolower($result['user_name']) == strtolower($user_name)) {
                    throw new RuntimeException("Username already in use!");
                }
                else if(strtolower($result['email']) == strtolower($email)) {
                    throw new RuntimeException("Email address already in use!");
                }
            }

            // Gender
            if(!in_array($gender, User::$genders, true)) {
                throw new RuntimeException("Invalid gender!");
            }

            // Village
            // Load villages
            $result = $system->db->query("SELECT `name`, `location` FROM `villages`");
            $villages = [];
            while ($row = mysqli_fetch_array($result)) {
                $villages[$row['name']] = $row;
            }
            if(!isset($villages[$village])) {
                throw new RuntimeException("Invalid village!");
            }

            // Encrypt password
            $password = $system->hash_password($password);

            $verification_code = sha1(mt_rand(1, 1337000));

            User::create(
                $system,
                $user_name,
                $password,
                $email,
                $gender,
                $village,
                $villages[$village]['location'],
                $verification_code
            );

            $subject = 'Shinobi-Chronicles account verification';
            $message = "Welcome to Shinobi-Chronicles RPG. Please visit the link below to verify your account: \r\n
		    {$system->router->base_url}register.php?act=verify&username={$user_name}&verify_key=$verification_code";
            $headers = "From: Shinobi-Chronicles<" . System::SC_ADMIN_EMAIL . ">" . "\r\n";
            $headers .= "Reply-To: " . System::SC_NO_REPLY_EMAIL . "\r\n";
            if(mail($email, $subject, $message, $headers)) {
                ;
                $system->message("Account created! Please check the email that you registered with for the verification  link (Be sure to check your spam folder as well)!");
                $login_message_text = "Account created! Please check the email that you registered with for the verification  link (Be sure to check your spam folder as well)!";
            }
            else {
                $system->message("There was a problem sending the email to the address provided: $email. If you are unable to log in please submit a ticket or contact a staff member on discord for manual activation.");
                $login_message_text = "There was a problem sending the email to the address provided: $email. If you are unable to log in please submit a ticket or contact a staff member on discord for manual activation.";
            }
        } catch (Exception $e) {
            $system->db->rollbackTransaction();
            $system->message($e->getMessage());
            error_log($e->getMessage());
            $register_error_text = $e->getMessage();

            $register_pre_fill['user_name'] = isset($_POST['user_name']) ? $_POST['user_name'] : "";
            $register_pre_fill['email'] = isset($_POST['email']) ? $_POST['email'] : "";
            $register_pre_fill['gender'] = isset($_POST['gender']) ? $_POST['gender'] : "";
            $register_pre_fill['village'] = isset($_POST['village']) ? $_POST['village'] : "";
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
    $layout = $system->setLayoutByName($player->layout);

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
            if ($layout->usesV2Interface()) {
                $location_name = $player->current_location->location_id
                    ? ' ' . ' <div id="contentHeaderLocation">' . " | " . $player->current_location->name . '</div>'
                    : null;
                $location_coords = "<div id='contentHeaderCoords'>" . " (" . $player->location->x . "." . $player->location->y . ")" . '</div>';
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
        $home_view = "default";
        if (isset($_GET['view'])) {
            switch ($_GET['view']) {
                case "news":
                    $home_view = "news";
                    break;
                case "contact":
                    $home_view = "contact";
                    break;
                case "rules":
                    $home_view = "rules";
                    break;
                case "terms":
                    $home_view = "terms";
                    break;
            }
        }
        $layout->renderBeforeContentHTML(
            $system,
            $player ?? null,
            "Home",
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

        $layout->renderAfterContentHTML($system, $player ?? null, render_content: false, render_footer: false, render_hotbar: false);
        $page_load_time = round(microtime(true) - $PAGE_LOAD_START, 3);
        $system->db->commitTransaction();
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
}
// Login
else {

    $layout = $system->setLayoutByName(System::DEFAULT_LAYOUT);
    $layout->renderBeforeContentHTML($system, $player ?? null, "Home", render_content: false, render_header: false, render_sidebar: false, render_topbar: false);

    // Display error messages
    if (!$system->layout->usesV2Interface()) {
        $system->printMessage(true);
    }
    if(!$system->SC_OPEN) {
        echo "<table class='table'><tr><th>Game Maintenance</th></tr>
        <tr><td style='text-align:center;'>
        Shinobi-Chronicles is currently closed for maintenace. Please check back in a few minutes!
        </td></tr></table>";
    }

    $captcha = '';

    $initial_home_view = "login";
    if ($reset_error_text != "") {
        $initial_home_view = "reset";
    }
    if ($register_error_text != "") {
        $initial_home_view = "register";
    }
    require('./templates/home.php');
    $layout->renderAfterContentHTML($system, $player ?? null, render_content: false, render_footer: false, render_hotbar: false);

    $page_load_time = round(microtime(true) - $PAGE_LOAD_START, 3);
    $system->db->commitTransaction();
    exit;


}

// Render footer
$page_load_time = round(microtime(true) - $PAGE_LOAD_START, 3);
$layout->renderAfterContentHTML($system, $player ?? null, $page_load_time);

$system->db->commitTransaction();