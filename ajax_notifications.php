<?php
session_start();
if(!isset($_SESSION['user_id'])) {
	echo "<!--LOGOUT-->";
	exit;
}
require_once("classes.php");
$system = new System();
$player = new User($_SESSION['user_id']);
$player->loadData(User::UPDATE_NOTHING);
$ajax = true;
require("notifications.php");
displayNotifications();