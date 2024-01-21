<?php
global $system;

if(!empty($_POST['login'])) {
    try {
        $user_name = $system->db->clean($_POST['user_name']);
        $password = $system->db->clean($_POST['password']);

        // These are both required and can be an inclusive check
        if(empty($user_name) || empty($password)) {
            throw new RuntimeException("Username and password are required!");
        }

        // Find user
        $result = $system->db->query("SELECT 
            `user_id`, `staff_level`, `user_name`, `password`, 
            `failed_logins`, `current_ip`, `last_ip`, `user_verified`, 
            `last_login_attempt`
        FROM `users` WHERE `user_name`='$user_name' LIMIT 1");

        // User not found
        if(!$system->db->last_num_rows) {
            throw new RuntimeException("User does not exist!");
        }
        // Fetch user data
        $result = $system->db->fetch($result);

        // Server closed, block logins from users that aren't admins
        if(!$system->SC_OPEN && !in_array($result['staff_level'], [StaffManager::STAFF_CONTENT_ADMIN, StaffManager::STAFF_ADMINISTRATOR, StaffManager::STAFF_HEAD_ADMINISTRATOR])) {
            throw new RuntimeException("System maintenance is in-progress! Please chack back soon.");
        }

        // User not verified
        if(!$result['user_verified'] && $system->REQUIRE_USER_VERIFICATION) {
            throw new RuntimeException("Account not activated!");
        }

        // Check failed logins - New location
        if ($result['failed_logins'] >= User::PARTIAL_LOCK && $_SERVER['REMOTE_ADDR'] != $result['current_ip'] && $_SERVER['REMOTE_ADDR'] != $result['last_ip']) {
            if(time() - $result['last_login_attempt'] <= User::PARTIAL_LOCK_CD) {
                $system->log(
                    'malicious_lockout',
                    $result['user_id'],
                    "IP address {$_SERVER['REMOTE_ADDR']} failed login on account {$result['user_name']} not matching previous IPs {$result['current_ip']} or {$result['last_ip']}."
                );

                throw new RuntimeException("Account has been locked out, please try again in a few minutes!");
            }
        } else if ($result['failed_logins'] >= User::FULL_LOCK && time() - $result['last_login_attempt'] <= User::FULL_LOCK_CD) {
            throw new RuntimeException("Account has been locked out, please try again in a few minutes!");
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

            // Load user and set new login data
            $player = User::loadFromId($system, $_SESSION['user_id']);
            $player->loadData();
            $player->last_login = time();
            $player->log(User::LOG_LOGIN, $_SERVER['REMOTE_ADDR']);
            $player->updateData();

            // Redirect to default page
            header(header: "Location: {$system->router->base_url}");
        }
        // If wrong, increment failed logins
        else {
            //Use longest timer to determine failed attempt resets
            if(time() - $result['last_login_attempt'] >= User::PARTIAL_LOCK_CD) {
                $system->db->query(
                    "UPDATE `users` SET `failed_logins` = 1, `last_login_attempt` = "
                    . time() . " WHERE `user_id`='{$result['user_id']}' LIMIT 1"
                );
            }
            else {
                $system->db->query(
                    "UPDATE `users` SET `failed_logins` = `failed_logins` + 1, `last_login_attempt` = "
                    . time() . " WHERE `user_id`='{$result['user_id']}' LIMIT 1"
                );
            }
            throw new RuntimeException("Invalid password!");
        }
    }catch (RuntimeException $e) {
        $system->message($e->getMessage());
        // Force login error message on legacy layouts
        if(!$system->layout->usesV2Interface()) {
            $system->printMessage(force_display: true);
        }
        // Set login error message for new layouts
        $system->homeVars['errors']['login'] = $e->getMessage();
    }
}