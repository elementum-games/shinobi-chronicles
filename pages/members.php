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
	renderMemberSubmenu();

	// Search box
	echo "<div style='text-align:center;'>
		<form action='$self_link' method='get'>
		<input type='hidden' name='id' value='6' />
		<input type='text' name='user' /><br />
		<input type='submit' value='Search' />
		</form>
	</div>";

	// Display Team profile
	if (isset($_GET['view_team'])) {
		$team_id = $system->clean($_GET['view_team']);
		try {
			$result = $system->query("SELECT `name`, `type`, `points`, `monthly_points`, `village`, `members`, `logo`, `leader` FROM `teams` WHERE `team_id`='{$team_id}'");

			if ($system->db_last_num_rows == 0) {
				throw new Exception ('Team does not exist');
			}

			$team_info = $system->db_fetch($result);
			$team_member_ids = json_decode($team_info['members'], true);

			$result = $system->query("SELECT `user_name`, `avatar_link`, `forbidden_seal` FROM `users` WHERE `user_id`='{$team_info['leader']}'");
			$leader_info = $system->db_fetch($result);
			$forbidden_seal = ($leader_info['forbidden_seal'] != '' ? '175px' : '125px');

			echo "
			<table class='table'>
				<tr>
					<th colspan='3'>
						{$team_info['name']}
					</th>
				</tr>
				<tr>
					<th style='width: 33%;'>
						Info
					</th>
					<th style='width: 33%;'>

					</th>
					<th style='width: 33%;'>
						Leader
					</th>
				</tr>
				<tr>
					<td style='vertical-align: middle;'>
						<b>Village</b>: {$team_info['village']} <br>
						<b>Type</b>: Shinobi<br>
					</td>
					<td style='vertical-align: middle;'>
						<b>Points</b>: {$team_info['points']} <br>
						<b>Monthly Points</b>: {$team_info['monthly_points']}
					</td>
					<td style='vertical-align: middle;text-align: center;' rowspan='2'>
						<a href='{$self_link}&user={$leader_info['user_name']}'>{$leader_info['user_name']}</a> <br>
						<img src='{$leader_info['avatar_link']}' style='margin-top:5px;max-width:{$forbidden_seal};max-height:{$forbidden_seal};'>
					</td>
				</tr>
				<tr>
					<td colspan='2' style='text-align: center;'>
						<img src='{$team_info['logo']}' style='width: 450px; height: 100px;'>
					</td>
				</tr>
			</table>

			<table class='table'>
				<tr>
					<th colspan='3'>
						Members
					</th>
				</tr>
				<tr>
					<th style='width: 33%;'>
						Name
					</th>
					<th style='width: 33%;'>
						Rank
					</th>
					<th style='width: 33%;'>
						Level
					</th>
				</tr>";

			$user_ids = implode(',', $team_member_ids);
			$result = $system->query("SELECT `user_name`, `rank`, `level` FROM `users` WHERE `user_id` IN ($user_ids) ORDER BY `rank` DESC, `level` DESC");

			while ($team_member = $system->db_fetch($result)) {
				echo "
				<tr class='table_multicolumns'>
					<td>
						<a href='{$self_link}&user={$team_member['user_name']}'>{$team_member['user_name']}</a>
					</td>
					<td style='text-align: center;'>
						{$ranks[$team_member['rank']]}
					</td>
					<td style='text-align: center;'>
						{$team_member['level']}
					</td>
				</tr>";
			}
			echo "</table>";
		}
		catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
		$display_list = false;
	}
    // Display user's profile
    else if(isset($_GET['user'])) {
		$user_name = $system->clean($_GET['user']);
		$result = $system->query("SELECT `user_id` FROM `users` WHERE `user_name`='{$user_name}' LIMIT 1");

		try {
			if($system->db_last_num_rows == 0) {
				throw new Exception("User does not exist!");
			}

			$result = $system->db_fetch($result);
			$viewUser = User::loadFromId($system, $result['user_id'], true);
			$viewUser->loadData(User::UPDATE_NOTHING, true);

			$journal_result = $system->query("SELECT `journal` FROM `journals` WHERE `user_id`='{$viewUser->user_id}'");
			if($system->db_last_num_rows == 0) {
				$journal = '';
			}
			else {
				$journal_result = $system->db_fetch($journal_result);
				$journal = $journal_result['journal'];
			}

			// Sensei Section
            $sensei;
            $students = [];
            if ($viewUser->sensei_id != 0) {
                // get sensei table data
                $sensei = SenseiManager::getSenseiByID($viewUser->sensei_id, $system);
                // get sensei user data
                if (!SenseiManager::isSensei($viewUser->user_id, $system)) {
                    $sensei += SenseiManager::getSenseiUserData($viewUser->sensei_id, $system);
                }
            } else if (SenseiManager::isSensei($viewUser->user_id, $system)) {
                // get sensei table data
                $sensei = SenseiManager::getSenseiByID($viewUser->user_id, $system);
                // if sensei has students, get student data
                if (count($sensei['students']) > 0) {
                    $students = SenseiManager::getStudentData($sensei['students'], $system);
                }
            }

            require 'templates/view_user_profile.php';

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
		$online_seconds = 900; // 15 mins
        $results_per_page = 15;

		$query_custom = '';
		$view = 'highest_exp';
		if(isset($_GET['view']) && $_GET['view'] == 'highest_exp') {
			$query_custom = " WHERE `staff_level` <= " . User::STAFF_HEAD_MODERATOR .
                " ORDER BY `exp` DESC, `pvp_wins` DESC";
			$view = 'highest_exp';
		}
		else if(isset($_GET['view']) && $_GET['view'] == 'highest_pvp') {
			$query_custom = " WHERE `staff_level` < " . User::STAFF_ADMINISTRATOR .
                " ORDER BY `pvp_wins` DESC";
			$view = 'highest_pvp';
		}
		//Teams
		else if(isset($_GET['view']) && $_GET['view'] == 'highest_teams') {
			$query_custom = " WHERE `staff_level` < " . User::STAFF_ADMINISTRATOR .
                " ORDER BY `pvp_wins` DESC";
			$view = 'highest_teams';
		}
		else if(isset($_GET['view']) && $_GET['view'] == 'online_users') {
			$query_custom = "WHERE `last_active` > UNIX_TIMESTAMP() - $online_seconds ORDER BY `level` DESC";

			$view = 'online_users';
            $results_per_page = 20;
		}
		else {
            $query_custom = " WHERE `staff_level` <= " . User::STAFF_HEAD_MODERATOR .
                " ORDER BY `exp` DESC, `pvp_wins` DESC";
			$view = 'highest_exp';

		}

		// Pagination
		$min = 0;
		if(isset($_GET['min']) && $view == 'online_users') {
			$min = (int)$system->clean($_GET['min']);
		}

		$result = $system->query("SELECT `user_name`, `rank`, `village`, `exp`, `level` , `pvp_wins` FROM `users`
			$query_custom LIMIT $min, $results_per_page");

		$table_header = 'Level';
		switch($view) {
			case "highest_exp":
				$table_header = 'Experience';
                $list_name = "Top {$results_per_page} Users - Highest Exp";
				break;
			case "online_users":
				$table_header = 'Level';

				$online_users_result = $system->query(
				    "SELECT COUNT(`user_id`) as `online_users` FROM `users` WHERE `last_active` > UNIX_TIMESTAMP() - $online_seconds"
                );
                $online_users = $system->db_fetch($online_users_result)['online_users'];
                $list_name = 'Online Users (' . $online_users . ' currently online)';
				break;
            case "highest_pvp":
                $table_header = 'Pvp Kills';
                $list_name = "Top {$results_per_page} Users - Highest PvP";
                break;
            case "highest_teams":
                $table_header = 'Highest Teams';
                $list_name = "Top {$results_per_page} Teams - Points This Month";
                break;
		}

		// Search box for individual users
		// list top 15 Teams
		if($view == 'highest_teams') {
			// Teams
				$user_id_array = array();
				$result = $system->query("SELECT * FROM `teams` ORDER BY `monthly_points` DESC LIMIT $results_per_page");
				$teams = array();
				while($row = $system->db_fetch($result)) {
					$teams[] = $row;
					if(array_search($row['leader'], $user_id_array) === false) {
							$user_id_array[] = $row['leader'];
						}
					}

				// Fetch leader names
				if(!empty($user_id_array)) {
				$user_id_string = implode(',', $user_id_array);
				$result = $system->query("SELECT `user_id`, `user_name`, `village` FROM `users` WHERE `user_id` IN ($user_id_string)");
				$user_names = array();
				$village = array();
				while($row = $system->db_fetch($result)) {
						$user_names[$row['user_id']] = $row['user_name'];
						$village[$row['village']] = $row['village'];
					}
				}
					// Team display
				   echo "<table id='members_team_table' class='table'><tr><th colspan='4'>Top {$results_per_page} Teams - Points This Month</th></tr><tr>
						   <th>Name</th>
						   <th>Leader</th>
						   <th>Village</th>
						   <th>Points This Month</th>
					   </tr>";
					   foreach($teams as $row) {
						   echo "<tr class='table_multicolumns'>
							   <td><a href='{$system->router->links['members']}&view_team={$row['team_id']}' class='userLink'>" . $row['name'] . "</td>
							   <td style='text-align: center;'><a href='{$system->router->links['members']}&user={$user_names[$row['leader']]}' class='userLink'>" . $user_names[$row['leader']] . "</td>
							   <td style='text-align: center;'><img src='./images/village_icons/" . strtolower($row['village']) . ".png' style='max-height:18px;max-width:18px;' /> " . $row['village'] . "</td>
							   <td style='text-align:center;'>" . $row['monthly_points']  . "</td>
						   </tr>";
					   }
				   }

		// List top 15 users by experience
		else {
			echo "<table id='members_table' class='table'><tr><th colspan='4'>$list_name</th></tr>
				<tr>
					<th style='width:30%;'>Username</th>
					<th style='width:20%;'>Rank</th>
					<th style='width:20%;'>Village</th>
					<th style='width:30%;'>" . ($table_header) . "</th>
				</tr>";
	}

	 if($view == 'highest_teams' && $system->db_last_num_rows == 0) {
			echo "<tr><td colspan='4'>No teams found!</td></tr>
		</table>";
		}
		else if($system->db_last_num_rows == 0) {

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
					else if($view == 'highest_pvp') {
						echo $row['pvp_wins'];
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
				$prev = $min - $results_per_page;
				if($prev < 0) {
					$prev = 0;
				}
				echo "<a href='$self_link&view=$view&min=$prev'>Previous</a>";
			}
			$result = $system->query("SELECT COUNT(`user_id`) as `count` FROM `users` $query_custom");
			$result = $system->db_fetch($result);
			if($min + $results_per_page < $result['count'] && $view == 'online_users') {
				if($min > 0) {
					echo "&nbsp;&nbsp;|&nbsp;&nbsp;";
				}
				$next = $min + $results_per_page;
				echo "<a href='$self_link&view=$view&min=$next'>Next</a>";
			}
			echo "</p>";
		}

	}
	else if($display_list == 'staff') {
		$result = $system->query("SELECT `user_name`, `rank`, `staff_level`, `village` FROM `users` WHERE `staff_level` > 0
			ORDER BY `staff_level` DESC");

		echo "<table class='table'><tr><th colspan='3'>Administrators</th></tr>";

		if($system->db_last_num_rows == 0) {
			echo "<tr><td colspan='3'>No users found!</td></tr>
			</table>";
		}
		else {
			$last_staff_level = User::STAFF_ADMINISTRATOR;
			while($row = $system->db_fetch($result)) {
				$class = '';
				if(is_int($count++ / 2)) {
					$class = 'row1';
				}
				else {
					$class = 'row2';
				}

				switch($row['staff_level']) {
					case User::STAFF_MODERATOR:
						$link_class = 'moderator';
						break;
					case User::STAFF_HEAD_MODERATOR:
						$link_class = 'headModerator';
						break;
                    case User::STAFF_CONTENT_ADMIN:
                        $link_class = 'contentAdmin';
                        break;
					case User::STAFF_ADMINISTRATOR:
						$link_class = 'administrator';
						break;
					case User::STAFF_HEAD_ADMINISTRATOR:
						$link_class = 'administrator';
						break;
				}

				// Headers
				if($row['staff_level'] < $last_staff_level) {
					$last_staff_level = $row['staff_level'];
					switch($row['staff_level']) {
						case User::STAFF_HEAD_MODERATOR:
							echo "<tr><th colspan='3'>Head Moderators</th></tr>";
							break;
						case User::STAFF_MODERATOR:
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

function renderMemberSubmenu() {
    global $system;
    global $player;
    global $self_link;

    $submenu_links = [
        [
            'link' => $system->router->links['members'] . "&view=highest_exp",
            'title' => 'Highest Exp',
        ],
        [
            'link' => $system->router->links['members'] . "&view=online_users",
            'title' => 'Online Users',
        ],
        $submenu_links[] = [
            'link' => $system->router->links['members'] . "&view=highest_pvp",
            'title' => 'Highest PvP',
        ],
        $submenu_links[] = [
            'link' => $system->router->links['members'] . "&view=highest_teams",
            'title' => 'Top Teams',
        ],
		$submenu_links[] = [
            'link' => $system->router->links['members'] . "&view=staff",
            'title' => 'Game Staff',
        ],
	];

	    echo "
			<div class='submenu'>
	    		<ul class='submenu'>";
	    			$submenu_link_width = round(100 / count($submenu_links), 1);
	    				foreach($submenu_links as $link) {
	        				echo "<li style='width:{$submenu_link_width}%;'><a href='{$link['link']}'>{$link['title']}</a></li>";
	    				}
	    		echo "</ul>
	    	</div>
	    	<div class='submenuMargin'></div>
	    ";
}
