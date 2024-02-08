<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    exit;
}

require __DIR__ . "/../classes/_autoload.php";

$system = System::initialize(load_layout: false);
$system->db->connect();

$user = User::loadFromId($system, $_SESSION['user_id']);
$user->loadData();

$arthesia_override = !$system->isDevEnvironment() && $user->user_id == 1603;

if(!$user->isHeadAdmin() && !$arthesia_override) {
    exit;
}
