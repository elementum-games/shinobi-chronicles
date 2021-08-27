<?php

function displayNotifications() {
	require("variables.php");
	
	global $system;
	global $player;
	
	// Notifications
	$notifications = array();
	
	if($player->battle_id > 0) {
		$result = $system->query("SELECT `battle_type` FROM `battles` WHERE `battle_id`='$player->battle_id' LIMIT 1");
		if($system->db_num_rows == 0) {
			$player->battle_id = 0;
		}
		else {
			$result = $system->db_fetch($result);
			switch($result['battle_type']) {
				case 1:
					$notifications[] = "<a class='link red' href='$battle_link'>In battle!</a>";
					break;
				case 2:
					$notifications[] = "<a class='link red' href='$spar_link'>In battle!</a>";
					break;
			}
		}
	}
	else if($player->battle_id == -1) {
		$notifications[] = "<a class='link red' href='" . ($link . '?id=' . $_SESSION['battle_page']) . "'>In battle!</a>";
	}
	
	$result = $system->query("SELECT `message_id` FROM `private_messages` 
		WHERE `recipient`='{$player->user_id}' AND `message_read`=0 LIMIT 1");
	if($system->db_num_rows) {
		$notifications[] = "<a class='link' href='$link?id=2'>You have unread PM(s)</a>";
	}

	if($player->staff_level >= $SC_MODERATOR) {
		$result = $system->query("SELECT `report_id` FROM `reports` WHERE `status` = 0 AND `staff_level` < $player->staff_level LIMIT 1");
		if(mysql_num_rows($result) > 0) {
			$notifications[] = "<a class='link' href='$report_link&page=view_all_reports'>New report(s)!</a>";
		}
	}

	if($player->challenge) {
		$notifications[] = "<a class='link' href='$spar_link'>Challenged!</a>";
	}
	
	if($player->team_invite) {
		$notifications[] = "<a class='link' href='$link?id=24'>Invited to team!</a>";
	}
	
	
	global $ajax;
	if(!$ajax) {
			echo "<div id='notifications'>";	
	}
	
	if(!empty($notifications)) {
		if($player->layout == 'shadow_ribbon') {
			
			if(count($notifications) > 1) {	
				echo "<img class='slideButtonLeft' onclick='slideNotificationLeft()' src='./images/left_arrow.png' />";
			}
			
			echo "<div id='notificationSlider'>";
			
			foreach($notifications as $id => $notification) {
				echo "<p class='notification' data-notification-id='$id'>" . $notification . "</p>";
			}
			
			echo "</div>";
			if(count($notifications) > 1) {	
				echo "<img class='slideButtonRight' onclick='slideNotificationRight()' src='./images/right_arrow.png' />";
			}
			
			
		}
		else if($player->layout == 'geisha') {
			foreach($notifications as $id => $notification) {
				echo "<p class='notification' style='margin-top:5px;margin-bottom:10px;'>" . $notification . "</p>";
			}
		}
		else {
			echo "<div style='margin:0px;border:1px solid #AAAAAA;border-radius:inherit;'>
			<div class='header'>
			Notifications
			</div>";
			
			foreach($notifications as $id => $notification) {
				echo "<p class='notification'>" . $notification . "</p>";
			}
			echo "</div>";
		}
	}
	
	if(!$ajax) {
		echo "</div>";
	}
}
