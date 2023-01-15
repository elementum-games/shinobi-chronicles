<?php

function displayNotifications() {
	global $system;
	global $player;
	global $new_inbox_message;
	global $new_inbox_alerts;
	
	// Notifications
	$notifications = array();
	
	if($player->battle_id > 0) {
		$result = $system->query("SELECT `battle_type` FROM `battles` WHERE `battle_id`='$player->battle_id' LIMIT 1");
		if($system->db_last_num_rows == 0) {
			$player->battle_id = 0;
		}
		else {
			$result = $system->db_fetch($result);
			$link = null;
			switch($result['battle_type']) {
                case Battle::TYPE_AI_ARENA:
                    $link = $system->links['arena'];
					break;
                case Battle::TYPE_AI_MISSION:
                    $link = $system->links['mission'];
                    break;
                case Battle::TYPE_AI_RANKUP:
                    $link = $system->links['rankup'];
                    break;
				case Battle::TYPE_SPAR:
				    $link = $system->links['spar'];
					break;
                case Battle::TYPE_FIGHT:
                    $link = $system->links['battle'];
                    break;
               /* case Battle::TYPE_CHALLENGE:
                    $link = $system->links['spar'];
                    break;*/
			}
			if($link) {
                $notifications[] = "<a class='link red' href='{$link}'>In battle!</a>";
            }
		}
	}
	
	if($new_inbox_message || $new_inbox_alerts) {
		$notifications[] = "<a class='link' href='{$system->link}?id=2'>You have unread PM(s)</a>";
	}

	if($player->isModerator()) {
		$result = $system->query("SELECT `report_id` FROM `reports` WHERE `status` = 0 AND `staff_level` < $player->staff_level LIMIT 1");
		if($system->db_last_num_rows > 0) {
			$notifications[] = "<a class='link' href='{$system->links['report']}&page=view_all_reports'>New report(s)!</a>";
		}
	}

	if($player->challenge) {
		$notifications[] = "<a class='link' href='{$system->links['spar']}}'>Challenged!</a>";
	}
	
	if($player->team_invite) {
		$notifications[] = "<a class='link' href='{$system->link}?id=24'>Invited to team!</a>";
	}

    if($player->spouse < 0) {
        $notifications[] = "<a class='link' href='{$system->links['marriage']}'>Proposal received!</a>";
    }
	
	
	global $ajax;
	if(!$ajax) {
			echo "<div id='notifications'>";	
	}
	
	if(!empty($notifications)) {
		if($player->layout == 'shadow_ribbon' || $player->layout === 'blue_scroll') {
			
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
