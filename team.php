<?php
/* 
File: 		team.php
Coder:		Levi Meahan
Created:	05/27/2014
Revised:	05/27/2014 by Levi Meahan
Purpose:	Functions for team management and activties
Algorithm:	See master_plan.html
*/

function team() {
	global $system;

	global $player;

	global $self_link;
	$self_id = 24;
	global $RANK_NAMES;
	
	if(!$player->team) {
		createTeam();
		return false;
	}

	if(isset($_GET['leave_team'])) {
		$members = $player->team['members'];
		$self = false;
		$count = 0;
		foreach($members as $id => $member) {
			if($member != 0) {
				$count++;
			}
			
			if($member == $player->user_id) {
				$self = $id;
			}
		}
		if($self !== false) {
			unset($members[$self]);
		}	
		
		if(!isset($_POST['leave_team'])) {
			echo "<table class='table'><tr><th>Leave Team</th></tr>
			<tr><td style='text-align:center;'>
			Are you sure you want to leave <b>" . $player->team['name'] . "</b>?<br />
			<form action='$self_link&leave_team=1' method='post'>";
			
			if($player->user_id == $player->team['leader']) {
				if($count > 1) {		
					$user_ids = implode(',', $members);
					$result = $system->query("SELECT `user_id`, `user_name` FROM `users` 
						WHERE `user_id` IN ($user_ids)");
					
					echo "Give leader spot to <select name='new_leader'>";
					while($row = $system->db_fetch($result)) {
						echo "<option value='{$row['user_id']}'>{$row['user_name']}</option>";
					}
					echo "</select>";
				}
			}
			
			echo "<input type='submit' name='leave_team' value='Leave Team' />
			</form>
			</td></tr></table>";
			return true;
		}
		
		try {
			// Leader check
			if($player->user_id == $player->team['leader']) {
				if($count > 1) {
					$new_leader = (int)$system->clean($_POST['new_leader']);
					if(array_search($new_leader, $members) === false) {
						throw new Exception("Invalid new leader!");
					}
				}
			}
			
			// delete team if only one member
			if($count == 1) {
				$result = $system->query("DELETE FROM `teams` WHERE `team_id`={$player->team['id']} LIMIT 1");
				if($system->db_affected_rows > 0) {
					$system->message("You have left your team. <a href='$self_link'>Continue</a>");
					$system->printMessage();
					$player->team = array();
					$player->team['id'] = 0;
					return true;
				}
				else {
					throw new Exception("Error leaving team!");
				}
			}
			// Shift member ids
			else {
				if($self !== false) {
					$members[] = 0;
				}
				$members = json_encode($members);
				$query = "UPDATE `teams` SET `members`='$members'";
				if($player->user_id == $player->team['leader']) {
					$query .= ", `leader`='$new_leader'";
				}
				$query .= "WHERE `team_id`={$player->team['id']}";
				$system->query($query);
				if($system->db_affected_rows > 0) {
					$system->message("You have left your team. <a href='$self_link'>Continue</a>");
					$system->printMessage();
					$player->team = array();
					$player->team['id'] = 0;
					return true;
				}
				else {
					throw new Exception("Error leaving team!");
				}
			}
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
	else if (isset($_POST['set_boost'], $_POST['set_amount'])) {

		$boost_valid = true;
		$Boost = $system->clean($_POST['set_boost']);
		$Amount = $system->clean($_POST['set_amount']);

		$allowed_boosts = [
			'Training' => [
				'Amount' => [
					'2.5' => [
						'Cost' => 5
					],
					'5.0' => [
						'Cost' => 10
					],
					'10.0' => [
						'Cost' => 25
					],
					'15.0' => [
						'Cost' => 30
					]
				]
			],
			'AI' => [
				'Amount' => [
					'2.5' => [
						'Cost' => 5
					],
					'5.0' => [
						'Cost' => 10
					],
					'10.0' => [
						'Cost' => 25
					],
					'15.0' => [
						'Cost' => 30
					]
				]
			]];
		
		if ($player->user_id != $player->team['leader']) {
			$system->message('You are not the leader of this team!');
			$boost_valid = false;
		}

		if (!array_key_exists($Boost, $allowed_boosts)) {
			$system->message('This boost does not exist!');
			$boost_valid = false;
		}

		if (!array_key_exists($Amount, $allowed_boosts[$Boost]['Amount'])) {
			$system->message('You cannot boost by this amount!');
			$boost_valid = false;
		}

		if ($allowed_boosts[$Boost]['Amount'][$Amount]['Cost'] > $player->team['points']) {
			$system->message('Your team does not have enough points for this boost!');
			$boost_valid = false;
		}
		
		if ($boost_valid) {
			$new_points = $player->team['points'] - $allowed_boosts[$Boost]['Amount'][$Amount]['Cost'];
			$boost_time = time();
			try {
				$result = $system->query("UPDATE `teams` SET `boost`='{$Boost}', `boost_amount`='{$Amount}', `points`='{$new_points}', `boost_time`='{$boost_time}' WHERE `team_id`='{$player->team['id']}' ");
				$system->message('Boost set!');
			}
			catch (Exception $e) {
				$system->message($e->getMessage());
			}
		}
		
		
		$system->printMessage();

	}
	else if(isset($_GET['join_mission']) && $player->team['mission_id']) {
		$mission_id = $player->team['mission_id'];
		$mission = new Mission($mission_id, $player, $player->team);
	
		$player->mission_id = $mission_id;
		
		$system->message("Mission joined!");
		$system->printMessage();
	}
	// Controls
	else if($player->user_id == $player->team['leader']) {
		if(isset($_GET['invite'])) {
			$user_name = $system->clean($_GET['user_name']);
			try {
				$result = $system->query("SELECT `user_id`, `rank`, `team_id`, `village` FROM `users` WHERE `user_name`='$user_name'");
				if($system->db_num_rows == 0) {
					throw new Exception("Invalid user!");
				}
				$user_data = $system->db_fetch($result);
				
				if($user_data['rank'] < 3) {
					throw new Exception("Player must be Chuunin or higher!");
				}
				
				if($user_data['village'] != $player->village) {
					throw new Exception("Player must be in the same village!");
				}
				
				if(!empty($user_data['team_id'])) {
					throw new Exception("Player is already in a team/invited to one!");
				}
				
				$result = $system->query("UPDATE `users` SET `team_id`='invite:{$player->team['id']}' 
					WHERE `user_id`='{$user_data['user_id']}' LIMIT 1");
					
				$system->message("Player invited!");
			} catch (Exception $e) {
				$system->message($e->getMessage());
			}
			$system->printMessage();
		}
		else if(isset($_POST['kick'])) {
			$kick = (int)$system->clean($_POST['user_id']);
			
			$members = $player->team['members'];
			$kick_key = false;
			$count = 0;
			foreach($members as $id => $member) {
				if($member != 0) {
					$count++;
				}
				
				if($member == $kick) {
					$kick_key = $id;
				}
			}
			
			try {
				if(!$kick_key) {
					throw new Exception("Invalid user!");
				}
				
				$result = $system->query("SELECT `user_name` FROM `users` WHERE `user_id`='$kick'");
				if($system->db_num_rows == 0) {
					throw new Exception("Invalid user!");
				}
				$user_name = $system->db_fetch($result)['user_name'];
				
				if(!isset($_GET['confirm'])) {
					echo "<table class='table'><tr><th>Leave Team</th></tr>
					<tr><td style='text-align:center;'>
					Are you sure you want to kick <b>$user_name</b> from the team?<br />
					<form action='$self_link&confirm=1' method='post'>
					<input type='hidden' name='user_id' value='$kick' />
					<input type='submit' name='kick' value='Kick Member' />
					</form>
					</td></tr></table>";
				}
				else {
					unset($members[$kick_key]);
					$members[] = 0;
					
					$player->team['members'] = $members;
					$members = json_encode($members);
					
					$query = "UPDATE `teams` SET `members`='$members' WHERE `team_id`={$player->team['id']}";
					$system->query($query);
					
					$query = "UPDATE `users` SET `team_id`=0 WHERE `user_id`='$kick' LIMIT 1";
					$system->query($query);
					
					if($system->db_affected_rows > 0) {
						$system->message("You have kicked <b>$user_name</b>.");
					}
					else {
						throw new Exception("Error kicking <b>$user_name</b>!");
					}	
				}
			} catch(Exception $e) {
				$system->message($e->getMessage());
			}
			$system->printMessage();
						
		}
		else if(isset($_POST['logo_link'])) {
			$avatar_link = $system->clean($_POST['logo_link']);
			try {
				$system->query("UPDATE `teams` SET `logo`='{$avatar_link}' WHERE `team_id`={$player->team['id']} LIMIT 1");
				$player->team['logo'] = $avatar_link;
				$system->message("Logo updated!");
			} catch (Exception $e) {
				$system->message($e->getMessage());
			}
			$system->printMessage();
		}
		else if(isset($_POST['start_mission'])) {
			$mission_id = (int)$system->clean($_POST['mission_id']);
			$result = $system->query("SELECT `mission_id` FROM `missions` WHERE `mission_id`=$mission_id AND `mission_type`=3");
			if($system->db_num_rows == 0) {
				$system->message("Invalid mission!");
			}
			else if($player->team['mission_id']) {
				$system->message("Team is already on a mission!");
			}
			else if($player->mission_id) {
				$system->message("You are already on a solo mission!");
			}
			else {
				$player->team['mission_id'] = $mission_id;
				$mission = new Mission($mission_id, $player, $player->team);
			
				$player->mission_id = $mission_id;
				
				$system->query("UPDATE `teams` SET `mission_id`=$mission_id WHERE `team_id`={$player->team['id']} LIMIT 1");
				$system->message("Mission started!");
			}
			$system->printMessage();
		}
		else if(isset($_GET['cancel_mission'])) {
			$mission_id = $player->team['mission_id'];
			$result = $system->query("UPDATE `teams` SET `mission_id`=0, `mission_stage`='' WHERE `team_id`={$player->team['id']} LIMIT 1");
			$result = $system->query("UPDATE `users` SET `mission_id`=0 WHERE `team_id`={$player->team['id']} AND `mission_id`=$mission_id");
			
			$player->team['mission_id'] = 0;
			
			if($player->mission_id == $mission_id) {
				$player->clearMission();
			}
			
			$system->message("Mission cancelled.");
			$system->printMessage();
		}
	}
	
	echo "<table class='table'>
	<tr><th colspan='3'>" . $player->team['name'] . "</th></tr>
	<tr>
		<th style='width: 33%;'>Information</th>
		<th style='width: 33%;'>Boost</th>
		<th style='width: 33%;'>Leader</th>
	</tr>
	<tr>
		<td style='vertical-align: top;'>
			<b>Team Type:</b> Shinobi<br />
			<br>
			<b>Points:</b> " . $player->team['points'] . "<br />
			<br>
			<a href='$self_link&leave_team=1'><p class='button'>Leave Team</p></a>
		</td>";
			// <label style='width:7.2em;'>Boost:</label>";
			// $boost = explode(':', $player->team['boost']);
			// if($boost[0] == 'training') {
			// 	echo (int)$player->team['boost_amount'] . "% faster " . ucwords(str_replace('_', ' ', $boost[1])) . " training<br />";
			// }
			// else {
			// 	echo "None<br />";
	
	$result = $system->query("SELECT `user_name`, `avatar_link`, `forbidden_seal` FROM `users` WHERE `user_id`='" . $player->team['leader'] . "' LIMIT 1");
	if($system->db_num_rows > 0) {
		$result = $system->db_fetch($result);
		$leader = $result['user_name'];
		$leader_avatar = $result['avatar_link'];
		$leader_avatar_size = '125px';
		if($result['forbidden_seal']) {
			$leader_avatar_size = '175px';
		}
	}
	else {
		$leader = 'None';
		$leader_avatar = './images/default_avatar.png';
	}

	if ($player->team['boost'] != 'none') {
		$boost_text = $player->team['boost'] . ' -- ' . $player->team['boost_amount'] . '%';
		$time_left = $player->team['boost_time'] + (60*60*24*7) - time();
		$boost_time = $system->timeRemaining($time_left, 'long');
	} else {
		$boost_text = 'none';
		$boost_time = 'n/a';
	}
	
	echo "
	<td style='text-align: center;vertical-align: middle;'>
		<b>Current Boosts:</b> {$boost_text}<br>
		<b>Time Remaining:</b><br>
		{$boost_time}
	</td>";
	
		
	echo "
	<td rowspan='2' style='text-align: center;vertical-align: middle;'>
			<img src='$leader_avatar' style='max-width:125px;max-height:125px;' />
	</td>
	</tr>
	<tr>
		<td colspan='2' style='text-align: center;'>
			<img src='{$player->team['logo']}' style='width: 450px; height: 100px;'>
		</td>
	</tr>
	</table>";
	
	// Start mission
	echo "<table class='table'>
	<tr><th colspan='2'>Missions</th></tr>
	<tr><td style='text-align:center; width: 50%;'>";

	if($player->team['mission_id']) {
		echo "<p style='margin:0px;margin-top:5px;'>
			<a href='$self_link&cancel_mission=1'><span class='button'>Cancel Mission</span></a></p>";
	}
	else {
		$result = $system->query("SELECT `mission_id`, `name`, `rank` FROM `missions` WHERE `mission_type`=3");
		
		echo "<form action='$self_link' method='post'>
		<select name='mission_id'>";
		while($mission = $system->db_fetch($result)) {
			echo "<option value='{$mission['mission_id']}'>{$mission['name']}</option>";
		}
		echo "</select><br />
		<input type='submit' name='start_mission' value='Start Mission' />
		</form>";
	}

	echo "</td>";
	// Mission display
	echo "<td style='text-align: center;'><div>
	<p style='font-size:1.1em;font-weight:bold;text-decoration:underline;margin-top:0px;margin-bottom:5px;'>Current Mission</p>";
	if($player->team['mission_id']) {
		$result = $system->query("SELECT `name` FROM `missions` WHERE `mission_id`={$player->team['mission_id']} LIMIT 1");
		$name = $system->db_fetch($result)['name'];
		if($player->mission_id == $player->team['mission_id']) {
			echo "<b>$name</b><br />" .
			$player->mission_stage['description'];
			if(is_array($player->team['mission_stage']) && $player->team['mission_stage']['count_needed']) {
				echo ' (' . $player->team['mission_stage']['count'] . '/' . $player->team['mission_stage']['count_needed'] . ' remaining)';
			}
		}
		else {
			$result = $system->query("SELECT `name` FROM `missions` WHERE `mission_id`={$player->team['mission_id']} LIMIT 1");
			$name = $system->db_fetch($result)['name'];
			echo "<b>$name</b><br />
			<br />
			<a href='$self_link&join_mission=1'><span class='button'>Join Mission</span></a>";
		}
	}
	else {
		echo "None";
	}
	echo "</div>
	</table>";

	// Members
	$user_ids = implode(',', $player->team['members']);
	$result = $system->query("SELECT `user_name`, `rank`, `level`, `monthly_pvp` FROM `users` 
		WHERE `user_id` IN ($user_ids) ORDER BY `rank` DESC, `level` DESC");
	
	echo "<table class='table'>
	<tr>
		<th colspan='4'>
			Team Members
		</th>
	</tr>
	<tr>
		<th style='width:30%;'>Username</th>
		<th style='width:20%;'>Rank</th>
		<th style='width:20%;'>Level</th>
		<th style='width:30%;'>PvP this month</th>
	</tr>";
	
	
	while($row = $system->db_fetch($result)) {
		echo "<tr class='table_multicolumns'>
			<td style='width:29%;'><a href='{$system->links['members']}&user={$row['user_name']}'>" . $row['user_name'] . "</a></td>
			<td style='width:20%;text-align:center;'>" . $RANK_NAMES[$row['rank']] . "</td>
			<td style='width:20%;text-align:center;'>" . $row['level'] . "</td>
			<td style='width:30%;text-align:center;'>" . $row['monthly_pvp'] . "</td>
		</tr>";
	}
	echo "</table>";

	// Leader tools
	if($player->user_id == $player->team['leader']) {
		$members = $player->team['members'];
		$self = false;
		$count = 0;
		foreach($members as $id => $member) {
			if($member != 0) {
				$count++;
			}
			
			if($member == $player->user_id) {
				$self = $id;
			}
		}
		if($self !== false) {
			unset($members[$self]);
		}
			
		echo "<table class='table'><tr><th colspan='3'>Team Controls</th></tr>";
		
		// Team members (invite/kick)
		echo "<tr>
			<th style='width: 33%;'>Invite</th>
			<th style='width: 33%;'>Logo</th>
			<th style='width: 33%;'>Boost</th>
		</tr>
		<tr><td style='text-align:center;'>
		<br />
		<form action='$self_link' method='get'>
		<input type='hidden' name='id' value='$self_id'>
		<input type='text' name='user_name' /><br />
		<input type='submit' name='invite' value='Invite Player' />
		</form>
		<br />";
		
		// Kick
		if($count > 1) {
			$user_ids = implode(',', $members);
			$result = $system->query("SELECT `user_id`, `user_name` FROM `users` 
				WHERE `user_id` IN ($user_ids)");
			
			echo "<form action='$self_link' method='post'>
				<select name='user_id'>";
				while($row = $system->db_fetch($result)) {
					echo "<option value='{$row['user_id']}'>{$row['user_name']}</option>";
				}
				echo "</select><br />
				<input type='submit' name='kick' value='Kick Member' />
			</form><br />";	
		}
		
		echo "</td>
		<td style='text-align: center;'>
		<br>
			<form action='$self_link' method='post'>
				<input type='text' name='logo_link' value='{$player->team['logo']}'><br>
				<button type='submit'>Change Logo</button><br>
				Dimensions: 450x100<br>
			</form>
		</td>
		<td style='text-align:center;'>
		<br>
		<form action='$self_link' method='post'>
			<select name='set_boost'>
				<option value='Training'>Training</option>
				<option value='AI'>AI Gains</option>
			</select><br>
			<select name='set_amount'>
				<option value='2.5'>2.5% - 5 Points</option>
				<option value='5.0'>5% - 10 Points</option>
				<option value='10.0'>10% - 20 Points</option>
				<option value='15.0'>15% - 30 Points</option>
			</select><br>
			<button type='submit'>Set Boost</button>
		</form>
		<br />
	
		</td></tr></table>";
	}
}

function createTeam() {
	global $system;

	global $player;

	global $self_link;
	global $RANK_NAMES;
	
	$min_name_length = 5;
	$max_name_length = 35;
	
	if(isset($_POST['create_team'])) {
		$name = $system->clean($_POST['name']);
		try {
			// Name
			if(strlen($name) < $min_name_length) {
				throw new Exception("Please enter a name longer than " . ($min_name_length - 1) . " characters!");
			}
			if(strlen($name) > $max_name_length) {
				throw new Exception("Please enter a name shorter than " . ($max_name_length + 1) . " characters!");
			}
			
			if(!preg_match('/^[a-zA-Z0-9 _-]+$/', $name)) {
				throw new Exception("Only alphanumeric characters, dashes, spaces, and underscores are allowed in names!");
			}
			
			// check for at least 3 letters
			$letter_count = 0;
			$num_symbol_count = 0;
			for($i = 0; $i < strlen($name); $i++) {
				if(ctype_alpha($name[$i])) {
					$letter_count++;
				}
				else {
					$num_symbol_count++;
				}
			}
			if($num_symbol_count >= $letter_count) {
				throw new Exception("Name must be more than half letters!");
			}
			
			// Banned words
			$banned_words = array(
				'fuck',
				'shit',
				'asshole',
				'bitch',
				'cunt',
				'fag',
				'asshat',
				'pussy',
				' dick',
				'whore'
			);
			foreach($banned_words as $word) {
				if(strpos(strtolower($name), $word) !== false) {
					throw new Exception("Inappropriate language is not allowed in team name!");
				}
			}
			
			// Check for name exising
			$result = $system->query("SELECT `team_id` FROM `teams` WHERE `name`='$name' LIMIT 1");
			if($system->db_num_rows > 0) {
				throw new Exception("Name is already in use!");
			}
			
			
			$query = "INSERT INTO `teams` 
				(`name`, `type`, `village`, `boost`, `boost_amount`, `points`, `monthly_points`, `leader`, `members`, `mission_id`, `logo`) VALUES
				('$name', 1, '$player->village', 'none', 0, 0, 0, '$player->user_id', '[$player->user_id,0,0,0]', 0, './images/default_avatar.png')";
			$system->query($query);
			
			
			if($system->db_affected_rows > 0) {
				$system->message("Team created! <a href='$self_link'>Continue</a>");
				$player->team = array();
				$player->team['id'] = $system->db_insert_id;	
			}
			else {
				$system->message("There was an error creating your team.");
			}
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
	}
	else if(isset($_GET['accept_invite']) && $player->team_invite) {
		try {
			$team_id = $player->team_invite;
			$result = $system->query("SELECT * FROM `teams` WHERE `team_id`='$team_id' LIMIT 1");
			if($system->db_num_rows == 0) {
				throw new Exception("Invalid team!");
			}
			$team_data = $system->db_fetch($result);
			
			if($team_data['village'] != $player->village) {
				throw new Exception("You must be in the same village to join a team!");
			}
		
			$members = json_decode($team_data['members'], true);
			$open_slot_found = false;
			foreach($members as $id => $member) {
				if($member == 0) {
					$members[$id] = $player->user_id;
					$open_slot_found = true;
					break;
				}
			}
		
			if(!$open_slot_found) {
				throw new Exception("Team is full!");
			}
		
			$members = json_encode($members);
			$result = $system->query("UPDATE `teams` SET `members`='$members' WHERE `team_id`='{$team_data['team_id']}'");
			$player->team = array();
			$player->team['id'] = $team_data['team_id'];
			$player->team_invite = 0;
			
			$system->message("You have joined <b>{$team_data['name']}</b>. <a href='$self_link'>Continue</a>");
			$system->printMessage();
			return true;
		} catch (Exception $e) {
			$player->team_invite = 0;
			$system->query("UPDATE `users` SET `team_id`=0 WHERE `user_id`='$player->user_id' LIMIT 1");
			$system->message($e->getMessage());
		}
	}
	else if(isset($_GET['decline_invite']) && $player->team_invite) {
		$player->team_invite = 0;
		$system->query("UPDATE `users` SET `team_id`=0 WHERE `user_id`='$player->user_id' LIMIT 1");
		$system->message("Invite declined.");
	}
	
	
	// Display
	$system->printMessage();
	echo "<table class='table'><tr><th>Teams</th></tr>
	<tr><td style='text-align:center;'>
	Now that you are a Chuunin, you can create or join a team with up to 3 other ninja. If you want to join a team, check the village HQ
	to find a team and PM the leader for an invite.
	</td></tr></table>";
	
	if($player->team_invite) {
		$team_id = $player->team_invite;
		$result = $system->query("SELECT * FROM `teams` WHERE `team_id`='$team_id' LIMIT 1");
		if($system->db_num_rows == 0) {
			$player->team_invite = 0;
		}
		else {
			$team_data = $system->db_fetch($result);
			$result = $system->query("SELECT `user_name`, `rank`, `avatar_link` FROM `users` WHERE `user_id`='{$team_data['leader']}'");
			$leader_data = $system->db_fetch($result);
			
			echo "<table class='table'><tr><th>Invited to Team</th></tr>
			<tr><td style='text-align:center;'>
			<div style='display:inline-block;width:350px;vertical-align:top;margin-top:10px;'>
				You have been invited to join the team <b>{$team_data['name']}</b><br />
				<br />
				Team type: Shinobi<br />
				Points: {$team_data['points']}<br />
				Boost: {$team_data['boost']}<br />
				<br />
				<a href='$self_link&accept_invite=1'><span class='button' style='width:8em;'>Accept</span></a>
				<a href='$self_link&decline_invite=1'><span class='button' style='width:8em;'>Decline</span></a>
			</div>
			<div style='display:inline-block;width:150px;height:145px;'>
				<p style='margin:2px;margin-bottom:4px;padding:3px 5px;border:1px solid #000000;border-radius:15px;color:#000000;font-weight:bold;
					background:linear-gradient(to bottom, #DCCA12, #EFDA17, #DCCA12);'>Team Leader</p>
				<span style='font-size:1.2em;font-family:\"tempus sans itc\";font-weight:bold;'>{$leader_data['user_name']}</span><br>
				<img src='{$leader_data['avatar_link']}' style='max-width:100px;max-height:100px;' /><br />
				" . $RANK_NAMES[$leader_data['rank']] . "
			</div>
			<br />
			
			</td></tr></table>";
		}
	}
	
	
	echo "<table class='table'>
        <tr><th>Create Team</th></tr>
        <tr><td style='text-align:center;'>
        <form action='$self_link' method='post'>
            <b>Name</b><br />
            <i>($min_name_length-$max_name_length characters, only letters, numbers, spaces, dashes, and underscores allowed)</i><br />
            <input type='text' name='name' value='" . (isset($name) ? $name : '') . "' /><br />
            <!--TYPE-->
            <input type='submit' name='create_team' value='Create' />
        </form>
        </td></tr>
	</table>";

}