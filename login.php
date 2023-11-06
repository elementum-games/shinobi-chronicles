<?php
global $system;
global $LoginManager;
global $LOGGED_IN;

// Login/Register
if(!isset($_SESSION['user_id'])) {
    if(!empty($_POST['login'])) {
        try {
            $user_name = $system->db->clean($_POST['user_name']);
            $password = $system->db->clean($_POST['password']);

            // Basic validation
            if (empty($user_name) || empty($password)) {
                throw new RuntimeException("Username and password is required!");
            }

            // Query user data
            $result = User::findByNameForLogin(system: $system, user_name: $user_name);
            // User not found
            if(is_null($result)) {
                throw new RuntimeException("$user_name was not found!");
            }

            // SC Offline
            if(!$system->SC_OPEN && $result['staff_level'] < StaffManager::STAFF_CONTENT_ADMIN) {
                throw new RuntimeException("SC is currently offline for maintenance!");
            }

            // User not verified
            if(!$result['user_verified']) {
                $LoginManager->login_user_not_active = true;
                throw new RuntimeException("Account has not been verified!");
            }

            // Failed logins
            if ($result['failed_logins'] >= User::PARTIAL_LOCK && $_SERVER['REMOTE_ADDR'] != $result['current_ip'] && $_SERVER['REMOTE_ADDR'] != $result['last_ip']) {
                $system->log(
                    'malicious_lockout',
                    $result['user_id'],
                    "IP address {$_SERVER['REMOTE_ADDR']} failed login on account {$result['user_name']} not matching previous IPs {$result['current_ip']} or {$result['last_ip']}."
                );
                throw new RuntimeException("Account has been locked out!");
            }
            else if ($result['failed_logins'] >= User::FULL_LOCK) {
                throw new RuntimeException("Account has been locked out!");
            }

            // Check password
            // Due to importance of login, it is inclusive instead of exclusive (if statement must be true for user to be logged in)
            if($system->verify_password($password, $result['password'])) {
                $_SESSION['user_id'] = $result['user_id'];
                $LOGGED_IN = true;

                $player = User::loadFromId($system, $_SESSION['user_id']);
                $player->loadData();
                $player->last_login = time();
                $player->failed_logins = 0;
                $player->log(User::LOG_LOGIN, $_SERVER['REMOTE_ADDR']);
                $player->updateDAta();
            }
            // Incorrect password, increment failed logins
            else {
                $system->db->query(
                    "UPDATE `users` SET `failed_logins` = `failed_logins` + 1 WHERE `user_id`='{$result['user_id']}' LIMIT 1"
                );
                throw new RuntimeException("Invalid password!");
            }
        } catch (RuntimeException $e) {
            $system->db->rollbackTransaction();
            $system->message($e->getMessage());
            error_log($e->getMessage());
            $LoginManager->login_error_text = $e->getMessage();
        }
    }
    else if (!empty($_POST['reset_password'])) {
        try {
            $con = $system->db->connect();
            $user_name = $system->db->clean($_POST['username']);
            $email = $system->db->clean($_POST['email']);
            $query = "SELECT `user_id` FROM users WHERE user_name='$user_name' AND email='$email' LIMIT 1";
            $result = $system->db->query($query);
            if ($system->db->last_num_rows == 0) {
                throw new RuntimeException("Invalid username or email address! Please submit a support request or try again.");
            }
            else {
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
            $LoginManager->reset_error_text = $e->getMessage();
            $LoginManager->initial_home_view = "reset_password";
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
            $LoginManager->login_error_text = $e->getMessage();
        }
    }
    else if (!empty($_POST['register'])) {
        try {
            if (!$system->register_open) {
                throw new RuntimeException("Sorry, not currently functional. Check back later.");
            }
            $user_name = $system->db->clean(trim($_POST['user_name']));
            $password = trim($_POST['password']);
            $confirm_password = trim($_POST['confirm_password']);
            $email = $system->db->clean(trim($_POST['email']));
            $gender = trim($_POST['gender']);
            $village = trim($_POST['village']);

            // Username
            if(strlen($user_name) < $LoginManager->min_username_length) {
                throw new RuntimeException("Please enter a username longer than 3 characters!");
            }
            if(strlen($user_name) > $LoginManager->max_username_length) {
                throw new RuntimeException("Please enter a username shorter than " . ($LoginManager->max_username_length + 1) . " characters!");
            }

            if(!preg_match('/^[a-zA-Z0-9_-]+$/', $user_name)) {
                throw new RuntimeException("Only alphanumeric characters, dashes, and underscores are allowed in usernames!");
            }

            // Banned words
            if($system->explicitLanguageCheck($user_name)) {
                throw new RuntimeException("Inappropriate language is not allowed in usernames!");
            }

            // Password
            if(strlen($password) < $LoginManager->min_password_length) {
                throw new RuntimeException("Please enter a password longer than " . ($LoginManager->min_password_length - 1) . " characters!");
            }

            if(!preg_match('/[0-9]/', $password)) {
                throw new RuntimeException("Password must include at least one number!");
            }
            if(!preg_match('/[A-Z]/', $password)) {
                throw new RuntimeException("Password must include at least one capital letter!");
            }
            if(!preg_match('/[a-z]/', $password)) {
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
            $result = $system->db->query("SELECT `villages`.`name`, `x`, `y`, `map_id` FROM `villages`
                INNER JOIN `maps_locations` on `villages`.`map_location_id` = `maps_locations`.`location_id`
            ");
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

            $village_coords = new TravelCoords($villages[$village]['x'], $villages[$village]['y'], $villages[$village]['map_id']);

            // TEMP FIX - AUTOMATICALLY VERIFIES - DO NOT FORGET TO CHANGE LATER
            User::create(
                $system,
                $user_name,
                $password,
                $email,
                $gender,
                $village,
                $village_coords->toString(),
                $verification_code
            );

            if(System::SEND_EMAIL_DURING_REGISTER) {
                $subject = 'Shinobi-Chronicles account verification';
                $message = "Welcome to Shinobi-Chronicles RPG. Please visit the link below to verify your account: \r\n
		            {$system->router->base_url}register.php?act=verify&username={$user_name}&verify_key=$verification_code";
                $headers = "From: Shinobi-Chronicles<" . System::SC_ADMIN_EMAIL . ">" . "\r\n";
                $headers .= "Reply-To: " . System::SC_NO_REPLY_EMAIL . "\r\n";
                if (mail($email, $subject, $message, $headers)) {
                    ;
                    $system->message("Account created! Please check the email that you registered with for the verification  link (Be sure to check your spam folder as well)!");
                    $LoginManager->login_message_text = "Account created! Please check the email that you registered with for the verification  link (Be sure to check your spam folder as well)!";
                    //$login_message_text = "Account created! Log in to continue.";
                }
                else {
                    $system->message("There was a problem sending the email to the address provided: $email. If you are unable to log in please submit a ticket or contact a staff member on discord for manual activation.");
                    $LoginManager->login_error_text = "An error occurred during account creation. Contact an administrator in Discord, or submit a support ticket, to have your account activated.";
                }
            }
            else {
                $system->message("Account created! Log in to continue.");
                $LoginManager->login_message_text = "Account created! Log in to continue.";
            }
        } catch (Exception $e) {
            $system->db->rollbackTransaction();
            $system->message($e->getMessage());
            error_log($e->getMessage());
            $LoginManager->register_error_text = $e->getMessage();
            $LoginManager->initial_home_view = "register";
            $LoginManager->register_prefill = [
                'username' => $_POST['user_name'] ?? "",
                'email' => $_POST['email'] ?? "",
                'gender' => $_POST['gender'] ?? "",
                'village' => $_POST['village'] ?? ""
            ];
        }
    }
}