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
$playerInbox = new InboxManager($system, $player);
$new_inbox_message = $playerInbox->checkIfUnreadMessages();
$new_inbox_alerts = $playerInbox->checkIfUnreadAlerts();
		  
$ajax = true;
require("notifications.php");
displayNotifications();
