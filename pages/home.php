<?php
function home() {
    global $system;
    global $player;

    // Process login
    if(!empty($_POST['login'])) {
        $user_name = $system->db->clean($_POST['user_name']);
        $password = $system->db->clean($_POST['password']);
        Auth::processLogin(system: $system, user_name: $user_name, password: $password);
    }
    // Process register
    require_once (__DIR__ . '/../new_register.php');
    require (__DIR__ . '/../templates/home.php');
}