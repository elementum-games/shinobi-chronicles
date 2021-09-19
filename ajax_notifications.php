<?php
session_start();
if(!isset($_SESSION['user_id'])) {
	echo "<!--LOGOUT-->";
	exit;
}
require_once("classes.php");
$system = new System();
$player = new User($_SESSION['user_id']);
$player->loadData(0); // Load data without calling regen/training updates
$ajax = true;
require("notifications.php");
displayNotifications();