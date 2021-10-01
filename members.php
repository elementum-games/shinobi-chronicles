<?php 
/* 
File: 		members.php
Coder:		Levi Meahan
Created:	08/24/2013
Revised:	05/02/2014 by Levi Meahan
Purpose:	Lists of users as well as view profile of specific users
Algorithm:	See master_plan.html
*/

function members() {
	global $system;

	global $player;
	
	global $self_link;
	
	$display_list = 'standard';
	
	// Load rank data
	global $RANK_NAMES;
	$ranks = $RANK_NAMES;
	
	
	// Sub-menu
	echo "<div class='submenu'>
	<ul class='submenu'>
		<li style='width:33%;'><a href='{$self_link}&view=highest_exp'>Highest Exp</a></li>
		<li style='width:33%;'><a href='{$self_link}&view=online_users'>Online Users</a></li>
		<li style='width:32.5%;'><a href='{$self_link}&view=staff'>Game Staff</a></li>
	</ul>
	</div>
	<div class='submenuMargin'></div>";
	
	// Search box
	echo "<div style='text-align:center;'>
		<form action='$self_link' method='get'>
		<input type='hidden' name='id' value='6' />
		<input type='text' name='user' /><br />
		<input type='submit' value='Search' />
		</form>
	</div>";
	
	// Display user's profile
	if(isset($_GET['user'])) {
		$user_name = $system->clean($_GET['user']);
		$result = $system->query("SELECT `user_id` FROM `users` WHERE `user_name`='{$user_name}' LIMIT 1");
			
		try {
			if($system->db_num_rows == 0) {
				throw new Exception("User does not exist!");
			}
		
			$result = $system->db_fetch($result);
			$viewUser = new User($result['user_id']);
			$viewUser->loadData(false, true);
			
			$journal_result = $system->query("SELECT `journal` FROM `journals` WHERE `user_id`='{$viewUser->user_id}'");
			if($system->db_num_rows == 0) {
				$journal = '';
			}
			else {
				$journal_result = $system->db_fetch($journal_result);
				$journal = $journal_result['journal'];
			}
			
			
			$avatar_size = '125px';
			if($viewUser->forbidden_seal) {
				$avatar_size = '175px';
			}
			echo "<table id='viewprofile' class='table'>
			<tr><th colspan='2'>View Profile</th>";

			echo "<tr><td colspan='2' style='text-align:center;'>";
				// Online/activity display
				if($viewUser->last_active > time() - 120) {
					echo "<span style='font-weight:bold;color:#00C000;'>Online</span>";
				}
				else if($viewUser->last_active > time() - 300) {
					echo "<span style='font-weight:bold;color:#E0D000;'>Inactive</span>";
				}
				else {
					echo "<span style='font-weight:bold;color:#D02020;'>Offline</span>";
				}
				echo "<br />";
				$last_active = time() - $viewUser->last_active;
				if($player->staff_level >= System::SC_MODERATOR) {
					echo "(Last active " . System::timeRemaining($last_active, 'long') . " ago)";
				}
				else {
					$days = floor($last_active / 86400);
					if($days == 0) {
						echo "(Last active today)";
					}
					else if($days == 1) {
						echo "(Last active yesterday)";
					}
					else {
						echo "(Last active $days days ago)";
					}
				}
				
			echo "</td></tr>
			<tr><td style='width:50%;text-align:center;'>
			<span style='font-size:1.3em;font-family:\"tempus sans itc\";font-weight:bold;'>" . $viewUser->user_name . "</span><br />
			<img src='{$viewUser->avatar_link}' style='margin-top:5px;max-width:$avatar_size;max-height:$avatar_size;' /><br />
			</td>";
			
			
			echo "<td style='width:50%;'>
			<label style='width:6em;'>Level:</label> 	$viewUser->level<br />
			<label style='width:6em;'>Exp:</label> 	$viewUser->exp<br />
			<label style='width:6em;'>Rank:</label> 	" . $ranks[$viewUser->rank] . "<br />" .
			"<br />
			<label style='width:6em;'>Gender:</label> 	$viewUser->gender<br />
			<label style='width:6em;'>Village:</label> $viewUser->village<br />
			<label style='width:6em;'>Bloodline:</label> " . ($viewUser->bloodline_id ? $viewUser->bloodline_name : "None") . 
				"<br />";
			if($viewUser->clan) {
				$clan_positions = array(
					1 => 'Leader',
					2 => 'Elder 1',
					3 => 'Elder 2',
				);
				echo "<label style='width:6em;'>Clan:</label> " . $viewUser->clan['name'] . "<br />";
				echo $viewUser->clan_office ? "<label style='width:6em;'>Clan Rank:</label> " . $clan_positions[$viewUser->clan_office] . "<br />" : "";
			}
			if($viewUser->team) {
				echo "<label style='width:6em;'>Team:</label> " . $viewUser->team['name'] . "<br />";
			}
			echo "<br />
			<label style='width:6em;'>PvP wins:</label>	$viewUser->pvp_wins<br />
			<label style='width:6em;'>PvP losses:</label> 	$viewUser->pvp_losses<br />
			<label style='width:6em;'>AI wins:</label>		$viewUser->ai_wins<br />
			<label style='width:6em;'>AI losses:</label>	$viewUser->ai_losses<br />
			</td></tr>";

			// $system->audioType got lost
			/*if($viewUser->profile_song) {
				$profile_song = $system->audioType($viewUser->profile_song);
				echo "<br />
				<tr><th colspan='2'>Profile Song</th></tr>
				<tr><td colspan='2' style='text-align: center;'>
				<audio controls>";
				echo "$profile_song";
				echo "
					Your browser does not support the audio element.
				</audio>";
			}*/

			//send message/money/ak
			echo "<tr style='text-align:center;'><td style='text-align:center;' colspan='2'>
			<a href='{$system->link}?id=2&page=new_message&sender={$viewUser->user_name}'>Send Message</a>";

			if($player->rank > 1) {
                echo "&nbsp;&nbsp;|&nbsp;&nbsp;<a href='{$system->links['profile']}&page=send_money&recipient={$viewUser->user_name}'>Send Money</a>";
                echo "&nbsp;&nbsp;|&nbsp;&nbsp;<a href='{$system->links['profile']}&page=send_ak&recipient={$viewUser->user_name}'>Send AK</a>";
            }
			if($viewUser->rank >= 3 && $player->team) {
				if($player->user_id == $player->team['leader'] && !$viewUser->team && !$viewUser->team_invite &&
				$player->village == $viewUser->village) {
					echo "&nbsp;&nbsp; |  &nbsp;&nbsp;
					<a href='{$system->link}?id=24&invite=1&user_name=$viewUser->user_name'>Invite to Team</a>";
				}
			}

			if($journal) {
				if(strpos($journal, "\n") === false) {
					$journal = wordwrap($journal, 100, "\r\n", true);
				}
				$journal = $system->html_parse(stripslashes($journal), true, true);

				$class_name = $player->forbidden_seal ? 'forbidden_seal' : 'normal';
				echo "<style type='text/css'>
                    #journal {
                        white-space: pre-wrap;
                    }
                    #journal.normal img {
                        max-width: 400px;
                        max-height: 300px;
                    }
                </style>
                <tr><th colspan='2'>Journal</th></tr>
				<tr><td colspan='2' >
				    <div id='journal' class='{$class_name}'>" . $journal . "</div>
                </td></tr>";
			}

			//report player
			echo
			"<tr>
				<td style='text-align: center;'colspan='2'>
					<a href='{$system->links['report']}&report_type=1&content_id=$viewUser->user_id'>Report Profile/Journal</a>
				</td>
			</tr>
			";
			echo "</td></tr></table>";

			if($player->staff_level >= System::SC_MODERATOR) {
				echo "<table class='table'><tr><th colspan='2'>Staff Info</th></tr>
				<tr><td colspan='2'>
					IP address: $viewUser->current_ip<br />
					Email address: $viewUser->email<br />
					<h3>Ban status:</h3>";
					$banned = false;
					if($viewUser->ban_type) {
						echo ucwords($viewUser->ban_type) . " banned: " . $system->time_remaining($viewUser->ban_expire - time()) . " remaining<br />";
						$banned = true;
					}
					
					if($viewUser->journal_ban) {
						echo "Journal banned<br />";
						$banned = true;
					}
					if($viewUser->avatar_ban) {
						echo "Avatar banned<br />";
						$banned = true;
					}
					if($viewUser->song_ban) {
						echo "Profile song banned<br />";
						$banned = true;
					}
					
					if(!$banned) {
						echo "No bans!<br />";
					}
					
					// Bot info
					if($player->staff_level >= System::SC_HEAD_ADMINISTRATOR) {
						echo "</td></tr>
						<tr><td colspan='2' style='text-align:center;'>";
						
						// Last chat post
						$result = $system->query("SELECT `time` FROM `chat` WHERE `user_name`='{$viewUser->user_name}' ORDER BY `post_id` DESC LIMIT 1");
						if($system->db_num_rows > 0) {
							$last_post = $system->db_fetch($result)['time'];
							echo "Last chat post: " . System::timeRemaining(time() - $last_post, 'long') . " ago<br />";
						}
						
						// Last AI
						echo "Last AI battle started: " . System::timeRemaining(time() - $viewUser->last_ai, 'short') . " ago<br />";
						
						// Current training
						$display = '';
						if(strpos($viewUser->train_type, 'jutsu:') !== false) {
							$train_type = str_replace('jutsu:', '', $viewUser->train_type);
							$display = "<br />Training: " . ucwords(str_replace('_', ' ', $train_type)) . "<br />" .
								System::timeRemaining($viewUser->train_time - time(), 'short', false, true) . " remaining";
						}
						else  {
							$display = "<br />Training: " . ucwords(str_replace('_', ' ', $viewUser->train_type)) . "<br />" .
								System::timeRemaining($viewUser->train_time - time(), 'short', false, true) . " remaining";
						}
						echo $display;
					}
					
					echo "</td></tr>
					<tr><td colspan='2' style='text-align:center;'>
					<a href='{$system->links['mod']}&view_record={$viewUser->user_name}'>View Record</a>&nbsp;&nbsp;|&nbsp;&nbsp;
					<a href='{$system->links['mod']}&ban_user_name={$viewUser->user_name}'>Ban user</a>";
				
				
				if($player->staff_level >= System::SC_HEAD_MODERATOR) {
					echo "&nbsp;&nbsp;|&nbsp;&nbsp;<a href='{$system->links['mod']}&unban_user_name={$viewUser->user_name}'>Unban user</a>";
					echo "&nbsp;&nbsp;|&nbsp;&nbsp;<a href='{$system->links['mod']}&ban_ip_address={$viewUser->last_ip}'>Ban IP</a>";
					echo "&nbsp;&nbsp;|&nbsp;&nbsp;<a href='{$system->links['mod']}&unban_ip_address={$viewUser->last_ip}'>Unban IP</a>";
				}
				if($player->staff_level >= System::SC_ADMINISTRATOR) {
					echo "&nbsp;&nbsp;|&nbsp;&nbsp;<a href='{$system->links['admin']}&page=edit_user&user_name={$viewUser->user_name}'>Edit user</a>";
				}
			
				echo "</td></tr>";
			}
			echo "</table>";
			$display_list = false;
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
	else if(isset($_GET['view'])) {
		if($_GET['view'] == 'staff') {
			$display_list = 'staff';
		}
	}
	
	$count = 0;

	if($display_list == 'standard') {
		$online_seconds = 120;

		$query_custom = '';
		$view = 'highest_exp';
		if(isset($_GET['view']) && $_GET['view'] == 'highest_exp') {
			$query_custom = " WHERE `staff_level` < " . System::SC_ADMINISTRATOR .
                " ORDER BY `exp` DESC, `pvp_wins` DESC";
			$list_name = 'Top 10 Users - Highest Exp';
			$view = 'highest_exp';
		}
		else if(isset($_GET['view']) && $_GET['view'] == 'online_users') {
			$query_custom = "WHERE `last_active` > UNIX_TIMESTAMP() - $online_seconds ORDER BY `level` DESC";
			
			$result = $system->query("SELECT COUNT(`user_id`) as `online_users` FROM `users` WHERE `last_active` > UNIX_TIMESTAMP() - $online_seconds");
			$result = $system->db_fetch($result);
			$list_name = 'Online Users (' . $result['online_users'] . ' currently online)';
			$view = 'online_users';		
		}
		else {
            $query_custom = " WHERE `staff_level` < " . System::SC_ADMINISTRATOR .
                " ORDER BY `exp` DESC, `pvp_wins` DESC";
			$list_name = 'Top 10 Users - Highest Exp';
			$view = 'highest_exp';
		
		}
		
		// Pagination
		$users_per_page = 15;
		$min = 0;
		if(isset($_GET['min']) && $view != 'highest_exp') {
			$users_per_page = 10;
			$min = (int)$system->clean($_GET['min']);
		}
		
		$result = $system->query("SELECT `user_name`, `rank`, `village`, `exp`, `level` FROM `users` 
			$query_custom LIMIT $min, $users_per_page");
		
		// Search box for individual users
		// List top 10 users by experience
		echo "<table class='table'><tr><th colspan='4'>$list_name</th></tr>
		<tr>
			<th style='width:30%;'>Username</th>
			<th style='width:20%;'>Rank</th>
			<th style='width:20%;'>Village</th>
			<th style='width:30%;'>" . ($view == 'highest_exp' ? 'Experience' : 'Level') . "</th>
		</tr>";
		
		
		if($system->db_num_rows == 0) {
			echo "<tr><td colspan='4'>No users found!</td></tr>
			</table>";
		}
		else {		
			while($row = $system->db_fetch($result)) {
				$class = '';
				if(is_int($count++ / 2)) {
					$class = 'row1';
				}
				else {
					$class = 'row2';
				}

				echo "<tr class='table_multicolumns'>
					<td class='$class' style='width:30%;'>
						<a href='$self_link&user={$row['user_name']}' class='userLink'>" . $row['user_name'] . "</a></td>
					<td class='$class' style='width:20%;text-align:center;'>" . $ranks[$row['rank']] . "</td>
					<td class='$class' style='width:20%;text-align:center;'>
						<img src='./images/village_icons/" . strtolower($row['village']) . ".png' style='max-height:18px;max-width:18px;' /> " . 
						$row['village'] . "</td>
					<td class='$class' style='width:30%;text-align:center;'>";
					if($view == 'highest_exp') {
						echo $row['exp'];
					}
					else {
						echo $row['level'];
					}
					
					echo "</td>
				</tr>";
			}
			echo "</table>";	
			
			// Pagination
			echo "<p style='text-align:center;'>";
			if($min > 0 && $view != 'highest_exp') {
				$prev = $min - $users_per_page;
				if($prev < 0) {
					$prev = 0;
				}
				echo "<a href='$self_link&view=$view&min=$prev'>Previous</a>";
			}
			$result = $system->query("SELECT COUNT(`user_id`) as `count` FROM `users` $query_custom");
			$result = $system->db_fetch($result);
			if($min + $users_per_page < $result['count'] && $view != 'highest_exp') {
				if($min > 0) {
					echo "&nbsp;&nbsp;|&nbsp;&nbsp;";
				}
				$next = $min + $users_per_page;
				echo "<a href='$self_link&view=$view&min=$next'>Next</a>";
			}
			echo "</p>";
		}
	
	}
	else if($display_list == 'staff') {	
		$result = $system->query("SELECT `user_name`, `rank`, `staff_level`, `village` FROM `users` WHERE `staff_level` > 0
			ORDER BY `staff_level` DESC");
		
		echo "<table class='table'><tr><th colspan='3'>Administrators</th></tr>";
			
		if($system->db_num_rows == 0) {
			echo "<tr><td colspan='3'>No users found!</td></tr>
			</table>";
		}
		else {		
			$last_staff_level = System::SC_ADMINISTRATOR;
			while($row = $system->db_fetch($result)) {
				$class = '';
				if(is_int($count++ / 2)) {
					$class = 'row1';
				}
				else {
					$class = 'row2';
				}
				
				switch($row['staff_level']) {
					case System::SC_MODERATOR:
						$link_class = 'moderator';
						break;
					case System::SC_HEAD_MODERATOR:
						$link_class = 'headModerator';
						break;
					case System::SC_ADMINISTRATOR:
						$link_class = 'administrator';
						break;
					case System::SC_HEAD_ADMINISTRATOR:
						$link_class = 'administrator';
						break;
				}
				
				// Headers
				if($row['staff_level'] < $last_staff_level) {
					$last_staff_level = $row['staff_level'];
					switch($row['staff_level']) {
						case System::SC_HEAD_MODERATOR:
							echo "<tr><th colspan='3'>Head Moderators</th></tr>";
							break;
						case System::SC_MODERATOR:
							echo "<tr><th colspan='3'>Moderators</th></tr>";
							break;
					}
				}

				echo "<tr class='threeColumns' >
					<td class='$class' style='width:45%;'>
						<a class='$link_class userLink' href='$self_link&user={$row['user_name']}'>" . $row['user_name'] . "</a></td>
					<td class='$class' style='width:20%;text-align:center;'>" . $ranks[$row['rank']] . "</td>
					<td class='$class' style='width:35%;text-align:center;'>
						<img src='./images/village_icons/" . strtolower($row['village']) . ".png' style='max-height:18px;max-width:18px;' /> " . 
						$row['village'] . "</td>
				</tr>";
			}
			echo "</table>";	
		}
	
	}
}

