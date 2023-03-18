<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    exit;
}

require __DIR__ . "../classes/_autoload.php";

$system = new System();
$system->dbConnect();

$user = User::loadFromId($system, $_SESSION['user_id']);
$user->loadData();

if(!$user->isHeadAdmin()) {
    exit;
}
