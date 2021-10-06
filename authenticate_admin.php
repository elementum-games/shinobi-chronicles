<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    exit;
}

require __DIR__ . "/classes.php";

$system = new System();
$system->dbConnect();

$user = new User($_SESSION['user_id']);
$user->loadData();

if($user->staff_level < System::SC_ADMINISTRATOR) {
    exit;
}
