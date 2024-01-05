<?php

require_once __DIR__ . "/User.php";
require_once __DIR__ . "/System.php";
require_once __DIR__ . "/exception/LoggedOutException.php";

class Auth {
    /**
     * @param System $system
     * @return User
     * @throws RuntimeException
     */
    public static function getUserFromSession(System $system): User {
        session_start();
        session_write_close();
        
        if(!isset($_SESSION['user_id'])) {
            throw new LoggedOutException("User is not logged in!");
        }

        $system = new System();
        $system->db->connect();

        $user = User::loadFromId($system, $_SESSION['user_id']);

        if(!$system->SC_OPEN && !$user->isContentAdmin()) {
            throw new RuntimeException('Server is temporarily closed!');
        }

        return $user;
    }
}