<?php

require_once __DIR__ . "/User.php";
require_once __DIR__ . "/System.php";
require_once __DIR__ . "/LoggedOutException.php";

class Auth {
    /**
     * @param System $system
     * @return User
     * @throws RuntimeException
     */
    public static function getUserFromSession(System $system): User {
        session_start();
        
        if(!isset($_SESSION['user_id'])) {
            throw new LoggedOutException("User is not logged in!");
        }

        $system = new System();
        $system->db->connect();

        return User::loadFromId($system, $_SESSION['user_id']);
    }
}