<?php

use DDTrace\Trace;

require_once __DIR__ . "/User.php";
require_once __DIR__ . "/SystemV2.php";
require_once __DIR__ . "/exception/LoggedOutException.php";

class Auth {
    /**
     * @param System $system
     * @return User
     * @throws RuntimeException
     */
    #[Trace]
    public static function getUserFromSession(System $system): User {
        session_start();
        session_write_close();
        
        if(!isset($_SESSION['user_id'])) {
            throw new LoggedOutException("User is not logged in!");
        }

        $user = User::loadFromId($system, $_SESSION['user_id']);

        if(!$system->SC_OPEN && !StaffManager::hasServerMaintAccess(staff_level: $user->staff_level)) {
            throw new RuntimeException('Server is temporarily closed!');
        }

        return $user;
    }

    public static function processLogin(System $system, string $user_name, string $password): void {
        try {
            // These are dependent, inclusive check premitted
            if(empty($user_name) || empty($password)) {
                throw new RuntimeException("Username and password are required!");
            }

            // Select user
            $result = $system->db->query("SELECT
                `user_id`, `staff_level`, `user_name`, `password`, `failed_logins`,
                `current_ip`, `last_ip`, `user_verified`, `last_login_attempt`
            FROM `users` WHERE `user_name`='$user_name' LIMIT 1");

            // User not found
            if(!$system->db->last_num_rows) {
                throw new RuntimeException("User not found!");
            }
            
            // Fetch data
            $user_data = $system->db->fetch($result);

            // Block login to unauthorized users during server maint
            if(!$system->SC_OPEN && !StaffManager::hasServerMaintAccess(staff_level: $user_data['staff_level'])) {
                throw new RuntimeException("Server is closed! Try againsoon.");
            }

            // User not verified
            if(!$user_data['user_verified'] && $system->REQUIRE_USER_VERIFICATION) {
                throw new RuntimeException("Account not activated!");
            }

            // Check failed logins - New location
            if($user_data['failed_logins'] >= User::PARTIAL_LOCK && ($_SERVER['REMOTE_ADD'] != $user_data['current_ip'] || $_SERVER['REMOTE_ADD'] != $user_data['last_ip'])) {
                // Failed login during CD period - log attempt and block error
                if(time() - $user_data['last_login_attempt'] <= User::PARTIAL_LOCK_CD) {
                    $system->log(
                        'malicious_lockout',
                        $user_data['user_id'],
                        "IP addres {$_SERVER['REMOTE_ADD']} failed login on account {$user_data['user_name']} not matching previous IPs {$user_data['current_ip']} or {$user_data['last_ip']}."
                    );

                    throw new RuntimeException("Account has been locked, please try again in a few minutes!");
                }
            }
            // Additional failed login check
            else if($user_data['failed_logins'] >= User::FULL_LOCK && time() - $user_data['last_login_attempt'] <= User::FULL_LOCK_CD) {
                // Already had at least on malicious attempt logged, no need to do further system logs
                // Continue to update login attepmt time for new locations
                if(!in_array($_SERVER['REMOTE_ADD'], [$user_data['current_ip'], $user_data['last_ip']])) {
                    $system->db->query("UPDATE `users`
                        SET `last_login_attempt`='" . time() . "'
                    WHERE `user_id`='{$user_data['user_id']} LIMIT 1");
                }
                throw new RuntimeException("Account has been locked, please try again in a few minutes!");
            }
            // Continue processing login
            else {
                // Login failed
                if(!$system->verify_password($password, $user_data['password'])) {
                    $failed_logins = $user_data['failed_logins'];
                    
                    // Reset failed logins outside failed threshold
                    if($user_data['failed_logins'] < User::FULL_LOCK && time() - $user_data['last_login_attempt'] >= User::PARTIAL_LOCK_CD) {
                        $failed_logins = 0;
                    }
                    if($user_data['failed_logins'] >= User::FULL_LOCK && time() - $user_data['last_login_attempt'] >= User::FULL_LOCK_CD) {
                        $failed_logins = 0;
                    }

                    $system->db->query("UPDATE `users` SET
                        `failed_logins`='" . ($failed_logins + 1) . "',
                        `last_login_attempt`='" . time() . "'
                    WHERE `user_id`='{$user_data['user_id']}' LIMIT 1");

                    throw new RuntimeException("Invalid password!");
                }

                // Complete login process
                $_SESSION['user_id'] = $user_data['user_id'];
                $player = User::loadFromId(system: $system, user_id: $_SESSION['user_id']);
                $player->loadData();
                $player->last_login = time();
                $player->failed_logins = 0;
                $player->log(User::LOG_LOGIN, $_SERVER['REMOTE_ADDR']);
                $player->updateData();

                // Redirect to default page
                header(header: "Location {$system->router->base_url}");
            }
        } catch(RuntimeException $e) {
            $system->message($e->getMessage());

            // Force login error message on legacy layouts
            if(!$system->layout->usesV2Interface()) {
                $system->printMessage(force_display: true);
            }

            // Set login error message for new layouts
            $system->homeVars['errors']['login'] = $e->getMessage();
        }
    }

    public static function processLogout(System $system) {
        $_SESSION = array();
        if(ini_get(option: 'session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        session();
        header(header: "Location: {$system->router->base_url}");
    }
}