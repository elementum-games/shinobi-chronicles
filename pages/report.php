<?php
/* 
File: 		report.php
Coder:		Levi Meahan
Created:	12/14/2013
Revised:	12/14/2013 by Levi Meahan
Purpose:	Functions for reports and handling by mods
Algorithm:	See master_plan.html
*/

function report() {
	global $system;
	
	global $player;
	
	global $self_link;
	
	$page = 'report';
	if(!empty($_GET['page'])) {
		$page = $_GET['page'];
	}
	
	// Submenu
	if($player->isModerator()) {
		echo "<div class='submenu'>
		<ul class='submenu'>
			<li style='width:99.5%;'><a href='{$self_link}&page=view_all_reports'>View New Reports</a></li>
		</ul>
		</div>
		<div class='submenuMargin'></div>";
	}
	
	/* Report types
	1- Profile/Journal
	2- Private Message
	3- Chat post
	*/
	$report_types = array(1 => 'Profile/Journal', 2 => 'Private Message', 3 => 'Chat Post');
	$report_reasons = array('Spamming', 'Harassment', 'Explicit Language/Content');
	
	
	// Submit report
	if(!empty($_POST['submit_report'])) {
		// Content already reported(if not profile) 
		
		try {
			$content_id = (int)$system->clean($_POST['content_id']);
			$report_type = (int)$system->clean($_POST['report_type']);
			$reason = $system->clean($_POST['reason']);
			$notes = $system->clean($_POST['notes']);
			
			
			// Profile/Journal
			if($report_type == 1) {
				$result = $system->query("SELECT `user_name`, `staff_level` FROM `users` WHERE `user_id`='$content_id' LIMIT 1");
				if(! $system->db_last_num_rows) {
					throw new Exception("Invalid user!");
				}
				
				$content_data = $system->db_fetch($result);
				$user_id = $content_id;
				$staff_level = $content_data['staff_level'];
				$time = time();
				$content = '';
			}
			// Private message
			else if($report_type == 2) {
				$content_data = Inbox::getInfoFromMessageId($system, $content_id);
				if(!$content_data) {
					throw new Exception("Invalid message!");
				}
				
				$user_id = $content_data['sender_id'];
				$user_name = $content_data['user_name'];
				$staff_level = $content_data['staff_level'];
				$time = $content_data['time'];
				$content = $content_data['message'];
			}
			// Chat post
			else if($report_type == 3) {
				$result = $system->query("SELECT `user_name`, `message`, `time` FROM `chat` WHERE `post_id`='$content_id' LIMIT 1");
				if($system->db_last_num_rows == 0) {
					throw new Exception("Invalid user!");
				}
				
				$content_data = $system->db_fetch($result);
				
				$result = $system->query("SELECT `user_id`, `staff_level` FROM `users` WHERE `user_name`='" . $content_data['user_name'] . "' LIMIT 1");
				if(! $system->db_last_num_rows) {
					throw new Exception("Invalid user!");
				}
				$result = $system->db_fetch($result);
				$user_id = $result['user_id'];
				$staff_level = $result['staff_level'];
				$time = $content_data['time'];
				$content = $content_data['message'];
			}
			else {
				throw new Exception("Invalid report type!");
			}
		
			if($staff_level == User::STAFF_HEAD_ADMINISTRATOR) {
				$staff_level--;
			}
		
			if($user_id == $player->user_id && !$player->isModerator()) {
				throw new Exception("You cannot report yourself!");
			}

			// Check for existing report
			if($report_type != 1) {
				$result = $system->query("SELECT `report_id` FROM `reports` WHERE `content_id`='$content_id' AND `report_type`='$report_type'");
				if($system->db_last_num_rows > 0) {
					throw new Exception("This content has already been reported!");
				}
			}
			
			// Reason
			if(!isset($report_reasons[$reason])) {
				throw new Exception("Invalid reason!");
			}
			
			if(strlen($notes) > 1000) {
				throw new Exception("Notes are too long! (" . strlen($notes) . "/1000 chars)");
			}
			
			$query = "INSERT INTO `reports` (`report_type`, `content_id`, `content`, `user_id`, `reporter_id`, `staff_level`, `reason`, `notes`, `status`, `time`)
			VALUES ('$report_type', '$content_id', '$content', '$user_id', '$player->user_id', '$staff_level', '{$report_reasons[$reason]}', '$notes', 0, '$time')";
			
			$system->query($query);
			
			if($system->db_last_affected_rows == 1) {
				$system->message("Report sent!");
				$page = '';
			}
			else {
				$system->message("Error submitting report!");
			}
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
	// Handle report
	if(!empty($_POST['handle_report']) && $player->isModerator()) {
		$page = 'view_report';
		
		try {
			$report_id = (int)$system->clean($_GET['report_id']);
			$result = $system->query("SELECT `report_id`, `status`, `moderator_id` FROM `reports` WHERE `report_id`='$report_id' AND `staff_level` < $player->staff_level");
			if($system->db_last_num_rows == 0) {
				throw new Exception("Invalid report!");
			}
			
			$report = $system->db_fetch($result);
			if($report['status'] != 0 && !$player->isHeadModerator()) {
				throw new Exception("Report has already been handled!");
			}
			
			if($_POST['handle_report'] == 'Guilty') {
				$verdict = 1;
			}
			else if($_POST['handle_report'] == 'Not Guilty') {
				$verdict = 2;
			}
			else {
				throw new Exception("Invalid verdict!");
			}
			
			$system->query("UPDATE `reports` SET `status` = $verdict, `moderator_id`='$player->user_id' WHERE `report_id` = $report_id LIMIT 1");
			if($system->db_last_affected_rows == 1) {
				$system->message("Report handled!");
			}
			else {
				$system->message("Error handling report!");
			}
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
	
	if(!$player->isModerator()) {
		// [MOD] View report
		// [MOD] View all reports
	}
	
	
	// Display page
	if($page == 'report') {
		try {
			$report_type = $_GET['report_type'];
			$content_id = (int)$system->clean($_GET['content_id']);
		
			// Profile/Journal
			if($report_type == 1) {
				$result = $system->query("SELECT `user_name` FROM `users` WHERE `user_id`='$content_id' LIMIT 1");
				if(! $system->db_last_num_rows) {
					throw new Exception("Invalid user!");
				}
				
				$content_data = $system->db_fetch($result);
				$user_id = $content_id;
				$user_name = $content_data['user_name'];
			}
			// Private message
			else if($report_type == 2) {
				$content_data = Inbox::getInfoFromMessageId($system, $content_id);
				if(!$content_data) {
					throw new Exception("Invalid message!");
				}

				$user_id = $content_data['user_id'];
				$user_name = $content_data['user_name'];
			}
			// Chat post
			else if($report_type == 3) {
				$result = $system->query("SELECT `user_name`, `message` FROM `chat` WHERE `post_id`='$content_id' LIMIT 1");
				if($system->db_last_num_rows == 0) {
					throw new Exception("Invalid user!");
				}
				
				$content_data = $system->db_fetch($result);
				
				$result = $system->query("SELECT `user_id` FROM `users` WHERE `user_name`='" . $content_data['user_name'] . "' LIMIT 1");
				if($system->db_last_num_rows == 0) {
					throw new Exception("Invalid user!");
				}
				$result = $system->db_fetch($result);
				$user_id = $result['user_id'];
				$user_name = $content_data['user_name'];
			}
			else {
				throw new Exception("Invalid report type!");
			}
		
			// Check for existing report
			if($report_type != 1) {
				$result = $system->query("SELECT `report_id` FROM `reports` WHERE `content_id`='$content_id' AND `report_type`='$report_type'");
				if($system->db_last_num_rows > 0) {
					throw new Exception("This content has already been reported!");
				}
			}
		
			if($user_name == $player->user_name && !$player->isModerator()) {
				throw new Exception("You cannot report yourself!");
			}
						
			echo "<table class='table'><tr><th>Submit Report</th></tr>
			<tr><td>
				<form action='$self_link' method='post'>";
				echo "<style type='text/css'>
				label {
					display:inline-block;
					width:110px;
					font-weight:bold;
				}
				</style>";
				
				echo "<label>Reported user:</label>" . $user_name . "<br />";
				echo "<label>Report type:</label>" . $report_types[$report_type] . "<br /><br />";
				if($report_type != 1) {
					echo "<label>Reported content:</label><br />
					<p style='width:500px;margin-top:3px;border:1px solid #000;margin-left:25px;padding:4px;'>" . 
						wordwrap($system->html_parse(stripslashes($content_data['message'])), 70) . "</p>";
				}
				
				echo "<label for='reason'>Reason:</label><br />
				<p style='margin-top:2px;margin-left:25px;'>
					<select name='reason'>";
					foreach($report_reasons as $id=>$reason) {
						echo "<option value='$id'>$reason</option>";
					}
				echo "</select></p>
				<label for='notes'>Notes (optional)</label><br />
				<textarea name='notes' style='height:55px;width:300px;margin-left:25px;margin-top:5px;'></textarea><br />
				
				<input type='hidden' name='content_id' value='$content_id' />
				<input type='hidden' name='report_type' value='$report_type' />
				<p style='text-align:center;'>
					<input type='submit' name='submit_report' value='Submit' />
				</p>
				</form>
			</td></tr></table>";
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
	else if($page == 'view_all_reports' && $player->isModerator()) {
		echo "<table class='table'><tr><th colspan='4'>Reports</th></tr>";
		
		$result = $system->query("SELECT * FROM `reports` WHERE `staff_level` < $player->staff_level AND `status` = 0");
		if($system->db_last_num_rows == 0) {
			echo "<tr><td style='text-align:center;' colspan='4'>No reports!</td></tr>";
		}
		else {
			echo "<tr>
				<th>Reported User</th>
				<th>Reported By</th>
				<th>Reason</th>
				<th></th>
			</tr>";
			$user_ids = array();
			$user_names = array();
			$reports = array();
			while($row = $system->db_fetch($result)) {
				$user_ids[] = $row['user_id'];
				$user_ids[] = $row['reporter_id'];
				$reports[] = $row;
			}
			
			
			$user_ids = implode(',', $user_ids);
			// Get user names
			$result = $system->query("SELECT `user_id`, `user_name` FROM `users` WHERE `user_id` IN ($user_ids)");
			while($row = $system->db_fetch($result)) {
				$user_names[$row['user_id']] = $row['user_name'];
			}
			
			foreach($reports as $report) {
				echo "<tr>
					<td>" . $user_names[$report['user_id']] . "</td>
					<td>" . $user_names[$report['reporter_id']] . "</td>
					<td>" . $report['reason'] . "</th>
					<td><a href='{$system->links['report']}&page=view_report&report_id=" . $report['report_id'] . "'>View</a></td>
				</tr>";
			}
		}
		echo "</table>";
	}
	else if($page == 'view_report' && $player->isModerator()) {
		try {
			$report_id = (int)$system->clean($_GET['report_id']);
			if(!$report_id) {
				throw new Exception("Invalid report!");
			}
			
			$result = $system->query("SELECT * FROM `reports` WHERE `report_id`='$report_id' AND `staff_level` < $player->staff_level");
			if($system->db_last_num_rows == 0) {
				throw new Exception("Invalid report!");
			}
			$report = $system->db_fetch($result);
			
			$result = $system->query("SELECT `user_id`, `user_name` FROM `users` WHERE `user_id` IN (" . $report['user_id'] . ',' . $report['reporter_id'] . ")");
			$user_names = array();
			while($row = $system->db_fetch($result)) {
				$user_names[$row['user_id']] = $row['user_name'];
			}
			
			echo "<table class='table'><tr><th>View Report</th></tr>
			<tr><td>";
				echo "<style type='text/css'>
				label {
					display:inline-block;
					width:10em;
					font-weight:bold;
				}
				</style>";
				
				echo "<label>Reported user:</label>" . $user_names[$report['user_id']] . "<br />";
				echo "<label>Reported by:</label>" . $user_names[$report['reporter_id']] . "<br />";
				echo "<label>Report type:</label>" . $report_types[$report['report_type']] . "<br /><br />";
				if($report['report_type'] != 1) {
					echo "<label>Reported content:</label><br />
					<p style='width:500px;margin-top:3px;border:1px solid #000;margin-left:25px;padding:4px;'>" . 
						wordwrap($system->html_parse(stripslashes($report['content'])), 70) . "</p>";
				}
				
				echo "<label for='reason'>Reason:</label><br />
				<p style='margin-top:2px;margin-left:25px;'>" . $report['reason'];
				echo "</select></p>";
				if($report['notes']) {
					echo "<label for='notes'>Notes</label><br />
					<p style='margin-left:25px;margin-top:5px;'>" .
						wordwrap($system->html_parse(stripslashes($report['notes'])), 70) . "</p>";
				}
				
				echo "<label for='verdict'>Verdict:</label>
				<p style='margin-left:25px;margin-top:5px;'>";
				if($report['status'] == 0) {
					echo "<form action='$self_link&page=view_report&report_id=" . $report['report_id'] . "' method='post'>
						<input type='submit' name='handle_report' value='Guilty' />
						<input type='submit' name='handle_report' value='Not Guilty' /> 
					</form>";
				}
				else {
					echo ($report['status'] == 1 ? 'Guilty' : 'Not Guilty');
				}
				echo "</p>";
				
			echo "</td></tr></table>";
		} catch (Exception $e) {
			$system->message($e->getMessage());
			$system->printMessage();
		}
	}
	
}