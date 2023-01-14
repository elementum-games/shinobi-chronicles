<?php
/* 
File: 		modPanel.php
Coder:		Levi Meahan
Created:	12/09/2013
Revised:	12/09/2013 by Levi Meahan
Purpose:	Functions for mod bans/etc
Algorithm:	See master_plan.html
*/
function modPanel() {
	global $system;
	global $player;
	global $self_link;


	// Staff level check
	if(!$player->isModerator()) {
		return false;
	}


	// $page = $_GET['page'];
	$display_menu = true;
	// Submenu
    echo "<div class='submenu'>
            <ul class='submenu'>
                <li style='width:32.9%;'><a href='$self_link'>Menu</a></li>
                <li style='width:32.9%;'><a href='$self_link&view=banned_users'>Banned Users</a></li>
                <li style='width:32.9%;'><a href='$self_link&view=locked_out_users'>Locked Out Users</a></li>
            </ul>
        </div>
        <div class='submenuMargin'></div>";

	// Social/game ban
	if(!empty($_POST['ban'])) {
		try {
			if(!isset($_POST['user_name'])) {
				throw new Exception("Invalid username!");
			}
			if(!isset($_POST['ban_type'])) {
				throw new Exception("Invalid ban type!");
			}
			if(!isset($_POST['ban_length'])) {
				throw new Exception("Invalid ban length!");
			}
			$user_name = $system->clean($_POST['user_name']);
			$ban_type = $system->clean($_POST['ban_type']);
			$ban_length = $system->clean($_POST['ban_length']);
			$ban_types = array('tavern', 'game');
			if(array_search($ban_type, $ban_types) === false) {
				throw new Exception("Invalid ban type!");
			}
			$result = $system->query("SELECT `user_id`, `user_name`, `staff_level`, `ban_type`, `ban_expire` FROM `users` WHERE `user_name`='$user_name'");
			if($system->db_last_num_rows == 0) {
				throw new Exception("Invalid username!");
			}
			$user_data = $system->db_fetch($result);
			if($user_data['staff_level'] >= User::STAFF_MODERATOR and !$player->isHeadAdmin()) {
				throw new Exception("You cannot ban fellow staff members!");
			}
			if(!empty($user_data['ban_type']) && !$player->isHeadModerator()) {
				if($ban_type == 'social' && $user_data['ban_type'] == 'game') {
					throw new Exception("You cannot reduce a ban!");
				}
				$current_ban_length = ($user_data['ban_expire'] - time()) / 86400;
				if($ban_length < $current_ban_length) {
					throw new Exception("You cannot reduce a ban!");
				}
			}
			// Run query if confirmed
			if(!isset($_POST['confirm'])) {
				echo "<table class='table'><tr><th>Confirm Ban</th></tr>
				<tr><td style='text-align:center;'>" .
				ucwords($ban_type) . " ban " . $user_data['user_name'] . " for " . $ban_length . " day(s)?<br />" .
				"<form action='$self_link' method='post'>
				<input type='hidden' name='user_name' value='{$user_data['user_name']}' />
				<input type='hidden' name='ban_type' value='$ban_type' />
				<input type='hidden' name='ban_length' value='$ban_length' />
				<input type='hidden' name='confirm' value='1' />
				<input type='submit' name='ban' value='Confirm' />
				</form>
				</td></tr></table>";
			}
			else {
				$ban_expire = time() + ($ban_length * 86400);
				$system->query("UPDATE `users` SET `train_type`='', `train_time`=0, `ban_type`='$ban_type', `ban_expire`='$ban_expire' 
					WHERE `user_id`='{$user_data['user_id']}' LIMIT 1");
				if($system->db_last_affected_rows == 1) {
					$system->message("User banned!");
				}
				else {
					$system->message("Error banning user!");
				}
			}
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
	}
	// Journal/avatar/profile song ban + remove
	else if(!empty($_POST['profile_ban'])) {
		try {
			$journal = false;
			$song = false;
			$avatar = false;
			if(!empty($_POST['journal'])) {
				$journal = $_POST['journal'];
			}
			if(!empty($_POST['avatar'])) {
				$avatar = $_POST['avatar'];
			}
			if(!empty($_POST['song'])) {
				$song = $_POST['song'];
			}
			$ban_journal = false;
			$remove_journal = false;
			$ban_avatar = false;
			$remove_avatar = false;
			$ban_song = false;
			$remove_song = false;
			if($journal) {
				if(array_search('ban', $journal) !== false) {
					$ban_journal = true;
				}
				if(array_search('remove', $journal) !== false) {
					$remove_journal = true;
				}
			}
			if($avatar) {
				if(array_search('ban', $avatar) !== false) {
					$ban_avatar = true;
				}	
				if(array_search('remove', $avatar) !== false) {
					$remove_avatar = true;
				}				
			}
			if($song) {
				if(array_search('ban', $song) !== false) {
					$ban_song = true;
				}	
				if(array_search('remove', $song) !== false) {
					$remove_song = true;
				}				
			}
			if(!$ban_journal && !$remove_journal && !$ban_avatar && !$remove_avatar && !$ban_song && !$remove_song) {
				throw new Exception("Please select an option!");
			}
			// Check username
			$user_name = $system->clean($_POST['user_name']);		
			$result = $system->query("SELECT `user_id`, `user_name`, `staff_level` FROM `users` WHERE `user_name`='$user_name'");
			if($system->db_last_num_rows == 0) {
				throw new Exception("Invalid username!");
			}
			$user_data = $system->db_fetch($result);
            // TODO: rewrite this logic to take content admins out of it
			if($user_data['staff_level'] >= $player->staff_level and !$player->isHeadAdmin()) {
				throw new Exception("You cannot ban fellow staff members!");
			}
			// Build query
			$add_comma = false;
			$query = "UPDATE `users` SET ";
			if($ban_journal) {
				$query .= "`journal_ban`='1'";
				$add_comma = true;
			}
			if($ban_avatar) {
				if($add_comma) {
					$query .= ", ";
					$add_comma = false;
				}
				$query .= "`avatar_ban`='1'";
				$add_comma = true;
			}
			if($ban_song) {
				if($add_comma) {
					$query .= ", ";
					$add_comma = false;
				}
				$query .= "`song_ban`='1'";
				$add_comma = true;
			}
			if($remove_avatar) {
				if($add_comma) {
					$query .= ", ";
					$add_comma = false;
				}
				$query .= "`avatar_link`=''";
				$add_comma = true;
			}
			if($remove_song) {
				if($add_comma) {
					$query .= ", ";
					$add_comma = false;
				}
				$query .= "`profile_song`=''";
				$add_comma = true;
			}
			$query .= " WHERE `user_id` = '{$user_data['user_id']}' LIMIT 1";
			if($add_comma) {
				$system->query($query);
			}
			// Set error flags
			$error = false;
			if($system->db_last_affected_rows == 0) {
				$error = true;
				if($ban_journal) {
					$ban_journal = -1;
				}
				if($ban_avatar) {
					$ban_avatar = -1;
				}
				if($ban_song) {
					$ban_song = -1;
				}
				if($remove_avatar) {
					$remove_avatar = -1;
				}
				if($remove_song) {
					$remove_song = -1;
				}
			}
			// Run remove journal query
			if($remove_journal) {
				$system->query("UPDATE `journals` SET `journal`='' WHERE `user_id` = '{$user_data['user_id']}' LIMIT 1");
				if($system->db_last_affected_rows == 0) {
					$error = true;
					$remove_journal = -1;
				}
			}
			// Error message
			if($error) {
				if($ban_journal == -1 || $ban_avatar == -1 || $ban_song == -1 || $remove_avatar == -1 || $remove_avatar == -1) {
					$system->message("Error banning journal/avatar/profile song! (or it is already banned)");
				}
				if($remove_journal == -1) {
					$system->message("Error removing journal! (or it is already blank)");
				}
			}
			// Success message	
			if(!$error) {
				$add_comma = false;
				$message = '';
				if($ban_journal) {
					$message .= "journal banned";
					$add_comma = true;
				}
				if($remove_journal) {
					if($add_comma) {
						$message .= ', ';
					}
					$message .= "journal removed";
					$add_comma = true;
				}
				if($ban_avatar) {
					if($add_comma) {
						$message .= ', ';
					}
					$message .= "avatar banned";
					$add_comma = true;
				}
				if($ban_song) {
					if($add_comma) {
						$message .= ', ';
					}
					$message .= "profile song banned";
					$add_comma = true;
				}
				if($remove_avatar) {
					if($add_comma) {
						$message .= ', ';
					}
					$message .= "avatar removed";
				}
				if($remove_song) {
					if($add_comma) {
						$message .= ', ';
					}
					$message .= "profile song removed";
				}
				$message .= '!';
				$message = ucfirst($message);
				$system->message($message);
			}
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
	}
	// View record
	else if(!empty($_GET['view_record'])) {
		try {
			$user_name = $system->clean($_GET['view_record']);
			$result = $system->query("SELECT `user_id`, `user_name`, `staff_level` FROM `users` WHERE `user_name`='$user_name' LIMIT 1");
			if($system->db_last_num_rows == 0) {
				throw new Exception("Invalid user!");
			}
			$result = $system->db_fetch($result);
			$user_id = $result['user_id'];
			$user_name = $result['user_name'];
			$staff_level = $result['staff_level'];
			if($staff_level >= $player->staff_level && !$player->isHeadAdmin()) {
				throw new Exception("You do not have authorization to view this record!");
			}
			$result = $system->query("SELECT * FROM `reports` WHERE `user_id`='$user_id'");
			$reports = array();
			$user_ids = array();
			$users = [];
			while($row = $system->db_fetch($result)) {
				$reports[$row['report_id']] = $row;
				if($row['moderator_id']) {
					$users[$row['moderator_id']] = $row['moderator_id'];
				}
			}
			// Fetch user names of moderators
			if(count($users) > 0) {
				$user_ids_string = implode(',', $users);
				$result = $system->query("SELECT `user_id`, `user_name` FROM `users` WHERE `user_id` IN($user_ids_string)");
				while($row = $system->db_fetch($result)) {
					$users[$row['user_id']] = $row['user_name'];
				}
			}
			$report_types = array(1 => 'Profile/Journal', 2 => 'Private Message', 3 => 'Chat Post');
			$verdicts = array(0 => 'Unhandled', 1 => 'Guilty', 2 => 'Not Guilty');
			echo "<table class='table'><tr><th colspan='5'>Reports for <b>$user_name</b></th></tr>";
			echo "<tr>
				<th>Reason</th>
				<th>Moderator</th>
				<th>Report Type</th>
				<th>Verdict</th>
				<th></th>
			</tr>";
			foreach($reports as $id => $report) {
				echo "<tr>
					<td>" . $report['reason'] . "</td>
					<td>" . ($report['moderator_id'] ? $users[$report['moderator_id']] : 'N/A') . "</td>
					<td>" . $report_types[$report['report_type']] . "</td>
					<td>" . $verdicts[$report['status']] . "</td>
					<td><a href='{$system->links['report']}&page=view_report&report_id=$id'>View</a></td>
				</tr>";
			}
			echo "</table>";
			$display_menu = false;	
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
	// Banned user list
	// Locked out users
	if(!empty($_GET['unlock_account']) && $player->isHeadModerator()) {
		$user_id = (int)$system->clean($_GET['unlock_account']);
		$result = $system->query("UPDATE `users` SET `failed_logins`=0 WHERE `user_id`='$user_id' LIMIT 1");
		if($system->db_last_affected_rows > 0) {
			$system->message("Account unlocked!");
		}
		else {
			$system->message("Invalid account!");
		}
		$system->printMessage();
	}
	// HM actions
	if($player->isHeadModerator()) {
		// Ban IP
		if(!empty($_POST['ban_ip'])) {
			try {
				$ip_address = $system->clean($_POST['ip_address']);
				$result = $system->query("SELECT `id` FROM `banned_ips` WHERE `ip_address`='$ip_address' LIMIT 1");
				if($system->db_last_num_rows > 0) {
					throw new Exception("IP address has already been banned!");
				}
				$system->query("INSERT INTO `banned_ips` (`ip_address`, `ban_level`) VALUES ('$ip_address', 2)");
				if($system->db_last_affected_rows == 1) {
					$system->message("IP address '$ip_address' banned!");
				}
				else {
					$system->message("Error banning IP address '$ip_address'!");
				}
			} catch (Exception $e) {
				$system->message($e->getMessage());
			}
		}
		// Social/game unban
		if(!empty($_POST['unban'])) {
			try {
				if(!isset($_POST['user_name'])) {
					throw new Exception("Invalid username!");
				}
				$user_name = $system->clean($_POST['user_name']);
				$result = $system->query("SELECT `user_id`, `user_name`, `staff_level`, `ban_type`, `ban_expire` FROM `users` WHERE `user_name`='$user_name'");
				if($system->db_last_num_rows == 0) {
					throw new Exception("Invalid username!");
				}
				$user_data = $system->db_fetch($result);
                // TODO: rewrite this logic to take content admins out of it
				if($user_data['staff_level'] >= $player->staff_level and !$player->isHeadAdmin()) {
					throw new Exception("You cannot unban fellow staff members!");
				}
				if(!$user_data['ban_type']) {
					throw new Exception("User is not banned!");
				}
				// Run query if confirmed
				if(!isset($_POST['confirm'])) {
					echo "<table class='table'><tr><th>Confirm Ban Removal</th></tr>
					<tr><td style='text-align:center;'>" .
					"Remove " . $user_data['user_name'] . "'s " . ucwords($user_data['ban_type']) . " ban?<br />" .
					"<form action='$self_link' method='post'>
					<input type='hidden' name='user_name' value='{$user_data['user_name']}' />
					<input type='hidden' name='confirm' value='1' />
					<input type='submit' name='unban' value='Confirm' />
					</form>
					</td></tr></table>";
				}
				else {
					$system->query("UPDATE `users` SET `ban_type`='', `ban_expire`='0' 
						WHERE `user_id`='{$user_data['user_id']}' LIMIT 1");
					if($system->db_last_affected_rows == 1) {
						$system->message("User unbanned!");
					}
					else {
						$system->message("Error unbanning user!");
					}
				}
			} catch (Exception $e) {
				$system->message($e->getMessage());
			}
		}
		// Unban IP
		if(!empty($_POST['unban_ip'])) {
			try {
				$ip_address = $system->clean($_POST['ip_address']);
				$result = $system->query("SELECT `id` FROM `banned_ips` WHERE `ip_address`='$ip_address' LIMIT 1");
				if($system->db_last_num_rows == 0) {
					throw new Exception("IP address is not banned!");
				}
				$system->query("DELETE FROM `banned_ips` WHERE `ip_address`='$ip_address' LIMIT 1");
				if($system->db_last_affected_rows == 1) {
					$system->message("IP address '$ip_address' unbanned!");
				}
				else {
					$system->message("Error unbanning IP address '$ip_address'!");
				}
			} catch (Exception $e) {
				$system->message($e->getMessage());
			}
		}
		// Journal/avatar/profile song unban
		else if(!empty($_POST['profile_unban'])) {
			try {
				if(!empty($_POST['journal'])) {
					$journal = $_POST['journal'];
				}
				if(!empty($_POST['avatar'])) {
					$avatar = $_POST['avatar'];
				}
				if(!empty($_POST['song'])) {
					$song = $_POST['song'];
				}
				$unban_journal = false;
				$unban_avatar = false;
				$unban_song = false;
				if($journal == 'unban') {
					$unban_journal = true;
				}				
				if($avatar == 'unban') {
					$unban_avatar = true;
				}
				if($song == 'unban') {
					$unban_song = true;
				}
				if(!$unban_journal && !$unban_avatar && !$unban_song) {
					throw new Exception("Please select an option!");
				}
				// Check username
				$user_name = $system->clean($_POST['user_name']);		
				$result = $system->query("SELECT `user_id`, `user_name`, `staff_level` FROM `users` WHERE `user_name`='$user_name'");
				if($system->db_last_num_rows == 0) {
					throw new Exception("Invalid username!");
				}
				$user_data = $system->db_fetch($result);
				// TODO: rewrite this logic to take content admins out of it
				if($user_data['staff_level'] >= $player->staff_level and !$player->isHeadAdmin()) {
					throw new Exception("You cannot unban fellow staff members!");
				}
				// Build query
				$add_comma = false;
				$query = "UPDATE `users` SET ";
				if($unban_journal) {
					$query .= "`journal_ban`='0'";
					$add_comma = true;
				}
				if($unban_avatar) {
					if($add_comma) {
						$query .= ", ";
						$add_comma = false;
					}
					$query .= "`avatar_ban`='0'";
					$add_comma = true;
				}
				if($unban_song) {
					if($add_comma) {
						$query .= ", ";
						$add_comma = false;
					}
					$query .= "`song_ban`='0'";
					$add_comma = true;
				}
				$query .= " WHERE `user_id` = '{$user_data['user_id']}' LIMIT 1";
				$system->query($query);
				// Set error flags
				$error = false;
				if($system->db_last_affected_rows == 0) {
					$error = true;
					if($unban_journal) {
						$ban_journal = -1;
					}
					if($unban_avatar) {
						$ban_avatar = -1;
					}
					if($unban_song) {
						$ban_avatar = -1;
					}
				}
				// Error message
				if($error) {
					if($unban_journal == -1 || $unban_avatar == -1 || $unban_song == -1) {
						$system->message("Error unbanning journal/avatar/profile song! (or it is already banned)");
					}
				}
				// Success message	
				if(!$error) {
					$add_comma = false;
					$message = '';
					if($unban_journal) {
						$message .= "journal unbanned";
						$add_comma = true;
					}
					if($unban_avatar) {
						if($add_comma) {
							$message .= ', ';
						}
						$message .= "avatar unbanned";
						$add_comma = true;
					}
					if($unban_song) {
						if($add_comma) {
							$message .= ', ';
						}
						$message .= "profile song unbanned";
						$add_comma = true;
					}
					$message .= '!';
					$message = ucfirst($message);
					$system->message($message);
				}
			} catch (Exception $e) {
				$system->message($e->getMessage());
			}
		}
		// Unlock account
		else if(!empty($_GET['locked_out_users'])) {
		}
		// Global message
		else if(!empty($_POST['global_message'])) {
			$message = $system->clean($_POST['global_message']);
			try {
				if(strlen($message) < 5) {
					throw new Exception("Please enter a message!");
				}
				if(strlen($message) > 1000) {
					throw new Exception("Message is too long! (" . strlen($message) . "/1000 chars)");
				}
				$system->query("UPDATE `system_storage` SET `global_message`='$message', `time`='".time()."'");
				$system->query("UPDATE `users` SET `global_message_viewed`=0");
				$player->global_message_viewed = 0;
				$system->message("Message posted!");
			} catch (Exception $e) {
				$system->message($e->getMessage());
			}
			$system->printMessage();
		}
	}

	// Logged message
	$system->printMessage();

	// Display forms
    $view = $_GET['view'] ?? '';

	if($view == 'banned_users') {
		try {
			$result = $system->query("SELECT `user_id`, `user_name`, `ban_type`, `ban_expire`, `journal_ban`, `avatar_ban`, `song_ban` FROM `users`
				WHERE `ban_type` != '' OR `journal_ban` = 1 OR `avatar_ban` = 1 OR `song_ban` = 1");
			if($system->db_last_num_rows == 0) {
				throw new Exception("No banned users!");
			}
			echo "<table class='table'><tr><th colspan='2'>Banned Users</th></tr>
			<tr>
				<th>Username</th>
				<th>Ban type(s)</th>
			</tr>";
			while($user = $system->db_fetch($result)) {
				echo "<tr>
					<td><a href='{$system->links['members']}&user={$user['user_name']}'>" . $user['user_name'] . "</a></td>
					<td>";
					$add_comma = false;
					if($user['ban_type']) {
						echo ucwords($user['ban_type']) . ' Ban';
						$add_comma = true;
					}
					if($user['journal_ban']) {
						if($add_comma) {
							echo ', ';
						}
						echo "Journal Ban";
						$add_comma = true;
					}
					if($user['avatar_ban']) {
						if($add_comma) {	
							echo ', ';
						}
						echo "Avatar Ban";
						$add_comma = true;
					}
					if($user['song_ban']) {
						if($add_comma) {	
							echo ', ';
						}
						echo "Profile Song Ban";
						$add_comma = true;
					}
				echo "</td>
				</tr>";
			}
			echo "</table>";
			$system->printMessage();
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
	}	
	else if($view == 'locked_out_users') {
		try {
			$result = $system->query("SELECT `user_id`, `user_name`, `failed_logins` FROM `users`
				WHERE `failed_logins` > 2 ORDER BY `failed_logins` DESC");
			if($system->db_last_num_rows == 0) {
				throw new Exception("No locked out users!");
			}
			echo "<table class='table'><tr><th colspan='3'>Locked Out Users</th></tr></table>
			<table class='table'><tr>
				<th style='width:60%;'>Username</th>
				<th style='width:20%;'>Type</th>
				<th style='width:20%;'>&nbsp;</th>
			</tr>";
			while($user = $system->db_fetch($result)) {
				echo "<tr>
					<td><a href='{$system->links['members']}&user={$user['user_name']}'>" . $user['user_name'] . "</a></td>
					<td>" . ($user['failed_logins'] >= 5 ? 'Full' : 'Partial') . "</td>
					<td>";
					if($player->isHeadModerator()) {
						echo "<a href='$self_link&view=locked_out_users&unlock_account={$user['user_id']}'>Unlock</a>";
					}
					else {
						echo "&nbsp;";
					}
					echo "</td>
				</tr>";
			}
			echo "</table>";
			$system->printMessage();
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
	}
	else if($display_menu) {
		// Social/game ban
		echo "<table id='mod_panel' class='table'>
		<tr>
			<th style='width:50%;'>Ban user</th>
			<th style='width:50%;'>Ban journal/avatar/profile song</th>
		</tr>
		<tr><td>
			<form action='$self_link' method='post'>
			<style type='text/css'>
			label {
				display:inline-block;
				width: 80px;
			}
			</style>
			<div style='width:210px;margin-left:auto;margin-right:auto;text-align:center;'>
				<p>Username</p>
				<input type='text' name='user_name' value='" . ($_GET['ban_user_name'] ?? "") . "' /><br />
				<div style='text-align:left;padding-top:13px;'>
				<label for='ban_type'>Ban type:</label>
					<select name='ban_type' style='width:100px;'>
						<option value='tavern' /> Tavern ban</option>
						<option value='game' /> Game ban</option>
					</select>
				<p style='margin-top:8px;'>
					<label for='ban_length'>Ban length:</label>
						<select name='ban_length' style='width:100px;'>
							<option value='1'>1 day</option>
							<option value='3'>3 days</option>
							<option value='7'>1 week</option>
							<option value='30'>1 month</option>
							<option value='90'>3 months</option>
							<option value='365'>1 year</option>
						</select>
				</p>
				</div>
			</div>
			<p style='margin-top:3px;text-align:center;'>	
				<input type='submit' name='ban' value='Ban'  />
			</p>
			</form>
		</td>
		<td style='text-align:center;'>
			<form action='$self_link' method='post'>
			<div style='width:210px;margin-left:auto;margin-right:auto;'>
				<p>Username</p>
				<input type='text' name='user_name' value='" . ($_GET['ban_user_name'] ?? "") . "' /><br />
				<div style='width:50%;float:left;text-align:left;margin-left:9%;'>
					<p>Journal</p>
					<input type='checkbox' name='journal[]' value='ban' /> Ban<br />
					<input type='checkbox' name='journal[]' value='remove' /> Remove<br />
				</div>
				<div style='width:40%;float:right;text-align:left;'>
					<p>Avatar</p>
					<input type='checkbox' name='avatar[]' value='ban' /> Ban<br />
					<input type='checkbox' name='avatar[]' value='remove' /> Remove<br />
				</div>
				<div style='width:50%;float:left;text-align:left;margin-left:9%;'>
					<p>Profile Song</p>
					<input type='checkbox' name='song[]' value='ban' /> Ban<br />
					<input type='checkbox' name='song[]' value='remove' /> Remove<br />
				</div>
				<br style='clear:both;' />
			</div>
			<p style='text-align:center;margin-top:3px;'>
				<input type='submit' name='profile_ban' />
			</p>
			</form>
		</td></tr>";

		// View record
		echo "<tr><th colspan='2'>View Record</th></tr>
		<tr><td colspan='2' style='text-align:center;'>
		<form action='$self_link' method='get'>
			<input type='hidden' name='id' value='16' />
			Username<br />
			<input type='text' name='view_record' /><br />
			<input type='submit' value='View' />
		</form>
		</td></tr>";
		echo "</table>";

		// HM actions
		if($player->isHeadModerator()) {
			echo "<br />
			<table class='table'><tr><th colspan='2'>Head Moderator actions</th></tr>
			<tr><th style='width:50%;'>Unban user</th>
				<th style='width:50%;'>Unban journal/avatar/profile song</th>
			</tr>
			<tr><td>
				<form action='$self_link' method='post'>
				<style type='text/css'>
				label {
					display:inline-block;
					width: 80px;
				}
				</style>
				<div style='width:210px;margin-left:auto;margin-right:auto;text-align:center;'>
					<p>Username</p>
					<input type='text' name='user_name' value='" . ($_GET['unban_user_name'] ?? "") . "' /><br />
				</div>
				<p style='margin-top:3px;text-align:center;'>	
					<input type='submit' name='unban' value='Unban'  />
				</p>
				</form>
			</td>
			<td style='text-align:center;'>
				<form action='$self_link' method='post'>
				<div style='width:210px;margin-left:auto;margin-right:auto;'>
					<p>Username</p>
					<input type='text' name='user_name' value='" . ($_GET['unban_user_name'] ?? "") . "' /><br />
					<div style='width:50%;float:left;text-align:left;margin-left:9%;'>
						<p>Journal</p>
						<input type='checkbox' name='journal' value='unban' /> Unban<br />
					</div>
					<div style='width:40%;float:right;text-align:left;'>
						<p>Avatar</p>
						<input type='checkbox' name='avatar' value='unban' /> Unban<br />
					</div>
					<div style='width:50%;float:left;text-align:left;margin-left:9%;'>
						<p>Profile Song</p>
						<input type='checkbox' name='song' value='unban' /> Unban<br />
					</div>
					<br style='clear:both;' />
				</div>
				<p style='text-align:center;margin-top:3px;'>
					<input type='submit' name='profile_unban' />
				</p>
				</form>
			</td></tr>";
			// Ban IP
			echo "<tr>
				<th>Ban IP Address</th>
				<th>Unban IP Address</th>
			</tr>
			<tr>
				<td style='text-align:center;'>
					<form action='$self_link' method='post'>
						<label for='ip_address'>IP address</label><br />
						<input type='text' name='ip_address' value='" . ($_GET['ban_ip_address'] ?? "") . "' /><br />
						<input type='submit' name='ban_ip' value='Ban' />
					</form>
				</td>
				<td style='text-align:center;'>
					<form action='$self_link' method='post'>
						<label for='ip_address'>IP address</label><br />
						<input type='text' name='ip_address' value='" . ($_GET['unban_ip_address'] ?? "") . "' /><br />
						<input type='submit' name='unban_ip' value='Unban' />
					</form>
				</td>
			</tr>";
			// Global Message
			echo "<tr><th colspan='2'>Global Message</th></tr>
			<tr><td colspan='2' style='text-align:center;'>
			<form action='$self_link' method='post'>
			<textarea name='global_message' style='width:475px;height:175px;'></textarea><br />
			<input type='submit' value='Post' />
			</form>
			</td></tr>";
			echo "</table>";
		}

		// Global message
		// Rules/manual edit
		// View locked out accounts / links to unlock
	}
}