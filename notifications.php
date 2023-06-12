<?php

function displayNotifications() {
	global $system;
	global $player;
	global $new_inbox_message;
	global $new_inbox_alerts;

    // Staff check
    if($player->staff_manager->isModerator()) {
        require_once 'classes/ReportManager.php';
        $reportManager = new ReportManager($system, $player, true);
    }
	// Notifications
	$notifications = array();

    // Battle - in Notifications
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
                    $link = $system->router->links['arena'];
					break;
                case Battle::TYPE_AI_MISSION:
                    $link = $system->router->links['mission'];
                    break;
                case Battle::TYPE_AI_RANKUP:
                    $link = $system->router->links['rankup'];
                    break;
				case Battle::TYPE_SPAR:
				    $link = $system->router->links['spar'];
					break;
                case Battle::TYPE_FIGHT:
                    $link = $system->router->links['battle'];
                    break;
               /* case Battle::TYPE_CHALLENGE:
                    $link = $system->router->links['spar'];
                    break;*/
			}
			if($link) {
                $notifications[] = "<a class='link red' href='{$link}'>In battle!</a>";
            }
		}
	}
	// New PM - in Notifications
	if($new_inbox_message || $new_inbox_alerts) {
		$notifications[] = "<a class='link' href='{$system->router->base_url}?id=2'>You have unread PM(s)</a>";
	}
    // Official Warning - in Notifications
    if($player->getOfficialWarnings(true)) {
        $notifications[] = "<a class='link' href='{$system->router->getUrl('account_record')}'>New Official Warning(s)!</a>";
    }
    //Reports - in Notifications
	if($player->staff_manager->isModerator() && $reportManager->getActiveReports(true)) {
        $notifications[] = "<a class='link' href='{$system->router->links['report']}&page=view_all_reports'>New report(s)!</a>";
	}
    //Spar - in Notifications
	if($player->challenge) {
		$notifications[] = "<a class='link' href='{$system->router->links['spar']}}'>Challenged!</a>";
	}
	//Team - in Notifications
	if($player->team_invite) {
		$notifications[] = "<a class='link' href='{$system->router->base_url}?id=24'>Invited to team!</a>";
	}
    //Proposal - in Notifications
    if($player->spouse < 0) {
        $notifications[] = "<a class='link' href='{$system->router->links['marriage']}'>Proposal received!</a>";
    }
	

	if(!$system->is_legacy_ajax_request) {
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
	
	if(!$system->is_legacy_ajax_request) {
		echo "</div>";
	}
}
