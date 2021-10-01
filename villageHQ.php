<?php
/*
File: 		villageHQ.php
Coder:		Levi Meahan
Created:	12/07/2013
Revised:	04/05/2014 by Levi Meahan
Purpose:	Functions for village HQ
Algorithm:	See master_plan.html
*/

function villageHQ() {
	global $system;

	global $player;

	global $self_link;
	global $RANK_NAMES;

	// Sub-menu
	echo "<div class='submenu'>
	<ul class='submenu'>
		<li style='width:30.5%;'><a href='{$self_link}&view=village_info'>Village Info</a></li>
		<li style='width:23%;'><a href='{$self_link}&view=members'>Members</a></li>
		<li style='width:25.5%;'><a href='{$self_link}&view=clans_teams'>Clans/Teams</a></li>
		<li style='width:18.5%;'><a href='{$self_link}&view=world_info'>World Info</a></li>
	</ul>
	</div>
	<div class='submenuMargin'></div>";

	$view = 'village_info';
	if(isset($_GET['view'])) {
		$view = $_GET['view'];
	}

	if($view == 'village_info') {
		$result = $system->query("SELECT * FROM `villages` WHERE `name`='$player->village'");
		$village_data = $system->db_fetch($result);

		$result = $system->query("SELECT
			COUNT(IF(`rank`=1,1,NULL)) as `count_1`,
			COUNT(IF(`rank`=2,1,NULL)) as `count_2`,
			COUNT(IF(`rank`=3,1,NULL)) as `count_3`,
			COUNT(IF(`rank`=4,1,NULL)) as `count_4`
			FROM `users` WHERE `village`='$player->village'");
		$villager_counts = $system->db_fetch($result);


		$leader_name = 'None';
		$leader_avatar = './images/default_avatar.png';
		if($village_data['leader']) {
			$result = $system->query("SELECT `user_name`, `avatar_link` FROM `users` WHERE `user_id`={$village_data['leader']} LIMIT 1");
			if($system->db_num_rows > 0) {
				$result = $system->db_fetch($result);
				$leader_name = $result['user_name'];
				$leader_avatar = $result['avatar_link'];
			}
		}

		echo "<table class='table'><tr><th>$player->village Village</th></tr>
		<tr><td class='villageView'>

		<!--Info-->
		<div style='display:inline-block;padding-top:10px;margin:0px;vertical-align:top;width:25%;'>
			<label style='width:8.7em;font-weight:bold;'>Village Points:</label>" . $village_data['points'] . "<br />
			<br />
			<label style='width:8.7em;font-weight:bold;'>Village Ninja</label><br />
			<p style='margin:0px;padding-left:10px;'>
				" . $villager_counts['count_1'] . ' ' . $RANK_NAMES[1] . "<br />
				" . $villager_counts['count_2'] . ' ' . $RANK_NAMES[2] . "<br />
				" . $villager_counts['count_3'] . ' ' . $RANK_NAMES[3] . "<br />
				" . $villager_counts['count_4'] . ' ' . $RANK_NAMES[4] . "<br />
			</p>
		</div>

		<!--Leader -->
		<div style='display:inline-block;width:25%;margin:1%;margin-left:11%;text-align:center;'>
		<p style='margin:2px;margin-left:auto;margin-right:auto;margin-bottom:4px;padding:2px 5px;
			border:1px solid #000000;border-radius:15px;color:#000000;font-weight:bold;width:100px;
			background:linear-gradient(to bottom, #DCCA12, #FAF000, #DCCA12);'>Kage</p>
		<span style='font-size:1.2em;font-family:\"tempus sans itc\";font-weight:bold;'>$leader_name</span><br />
		<img src='$leader_avatar' style='max-width:125px;max-height:125px;' />
		</div>

		<!--Village Symbol-->
		<div style='float:right;display:inline-block;width:84px;height:74px;margin:10px;margin-top:46px;margin-right:35px;
			border-radius:75px;padding-top:10px;
			background: radial-gradient(#808080, #707070, #000000);text-align:center;'>
			<img src='./images/village_icons/" . strtolower($player->village) . "_large.png' style='max-width:64px;max-height:64px;' />
		</div>
		<br style='margin:0px;clear:both;' />
		</td></tr></table>";
	}
	else if($view == 'members') {
		// Load rank data
		$ranks = array();
		$result = $system->query("SELECT `rank_id`, `name` FROM `ranks`");
		while($rank = $system->db_fetch($result)) {
			$ranks[$rank['rank_id']]['name'] = $rank['name'];
		}

		// Pagination
		$users_per_page = 10;
		$min = 0;
		if(isset($_GET['min'])) {
			$min = (int)$system->clean($_GET['min']);
		}

		$result = $system->query("SELECT `user_name`, `rank`, `level`, `exp` FROM `users`
			WHERE `village`='$player->village' ORDER BY `rank` DESC, `exp` DESC LIMIT $min, $users_per_page");

		echo "<table class='table'><tr><th colspan='4'>Village Members</th></tr>
		<tr>
			<th style='width:30%;'>Username</th>
			<th style='width:20%;'>Rank</th>
			<th style='width:20%;'>Level</th>
			<th style='width:30%;'>Experience</th>
		</tr>";

		$count = 0;
		while($row = $system->db_fetch($result)) {
			$class = '';
			if(is_int($count++ / 2)) {
				$class = 'row1';
			}
			else {
				$class = 'row2';
			}

			echo "<tr id='villagemembertd' class='fourColGrid'>
				<td style='width:29%;' class='$class'><a href='{$system->links['members']}&user={$row['user_name']}'>" . $row['user_name'] . "</a></td>
				<td style='width:20%;text-align:center;' class='$class'>" . $ranks[$row['rank']]['name'] . "</td>
				<td style='width:20%;text-align:center;' class='$class'>" . $row['level'] . "</td>
				<td style='width:30%;text-align:center;' class='$class'>" . $row['exp'] . "</td>
			</tr>";
		}
		echo "</table>";

		// Pagination
		echo "<p style='text-align:center;'>";
		if($min > 0) {
			$prev = $min - $users_per_page;
			if($prev < 0) {
				$prev = 0;
			}
			echo "<a href='$self_link&view=members&min=$prev'>Previous</a>";
		}
		$result = $system->query("SELECT COUNT(`user_id`) as `count` FROM `users` WHERE `village`='$player->village'");
		$result = $system->db_fetch($result);
		if($min + $users_per_page < $result['count']) {
			if($min > 0) {
				echo "&nbsp;&nbsp;|&nbsp;&nbsp;";
			}
			$next = $min + $users_per_page;
			echo "<a href='$self_link&view=members&min=$next'>Next</a>";
		}
		echo "</p>";
	}
	else if($view == 'clans_teams') {
		$user_id_array = array();

		// Clans
		$result = $system->query("SELECT * FROM `clans` WHERE `village`='$player->village' ORDER BY `points` DESC LIMIT 5");
		$clans = array();
		while($row = $system->db_fetch($result)) {
			$clans[] = $row;
			if(array_search($row['leader'], $user_id_array) === false) {
				$user_id_array[] = $row['leader'];
			}
		}

		// Teams
		$result = $system->query("SELECT * FROM `teams` WHERE `village`='$player->village' ORDER BY `points` DESC LIMIT 5");
		$teams = array();
		while($row = $system->db_fetch($result)) {
			$teams[] = $row;
			if(array_search($row['leader'], $user_id_array) === false) {
				$user_id_array[] = $row['leader'];
			}
		}

		// Fetch leader names
		$user_id_string = implode(',', $user_id_array);
		$result = $system->query("SELECT `user_id`, `user_name` FROM `users` WHERE `user_id` IN ($user_id_string)");
		$user_names = array();
		while($row = $system->db_fetch($result)) {
			$user_names[$row['user_id']] = $row['user_name'];
		}

		// Clan display
		echo "<table class='table'><tr><th colspan='3'>Top 5 Clans</th></tr>
		<tr>
			<th>Name</th>
			<th>Leader</th>
			<th>Points</th>
		</tr>";
		foreach($clans as $row) {
			echo "<tr>
				<td>" . $row['name'] . "</td>
				<td>" . ($row['leader'] ? $user_names[$row['leader']] : 'None') . "</td>
				<td>" . $row['points']  . "</td>
			</tr>";
		}
		echo "</table>";

		// Team display
		echo "<table class='table'><tr><th colspan='3'>Top 5 Teams</th></tr><tr>
			<th>Name</th>
			<th>Leader</th>
			<th>Points</th>
		</tr>";
		foreach($teams as $row) {
			echo "<tr>
				<td>" . $row['name'] . "</td>
				<td>" . $user_names[$row['leader']] . "</td>
				<td style='text-align:center;'>" . $row['points']  . "</td>
			</tr>";
		}
		echo "</table>";
	}
	else if($view == 'world_info') {
		// World info
		$result = $system->query("SELECT * FROM `villages`");
		$villages = array();
		while($row = $system->db_fetch($result)) {
			$villages[] = $row;
		}

		$count_query = "SELECT ";
		foreach($villages as $id => $village) {
			$count_query .= "COUNT(IF(`village` = '{$village['name']}', 1, NULL)) AS `" . $village['name'] . "_members`";
			if($id < count($villages) - 1) {
				$count_query .= ', ';
			}
		}
		$count_query .= " FROM `users`";
		$result = $system->query($count_query);
		$village_counts = $system->db_fetch($result);

		echo "<table class='table'><tr><th colspan='4'>Villages</th></tr>
		<tr>
			<th style='width:25%;'>Village</th>
			<th style='width:25%;'>Points</th>
			<th style='width:25%;'>Members</th>
			<th style='width:25%;'>Alliance</th>
		</tr>";
		if(is_array($villages)) {
			foreach($villages as $village) {
				echo "<tr id='villages' class='fourColGrid'>
					<td style='width:25%;'>" . $village['name'] . "</td>
					<td style='width:25%;'>" . $village['points'] . "</td>
					<td style='width:25%;'>" . $village_counts[($village['name'] . '_members')] . "</td>
					<td style='width:25%;'>
						<span style='color:#E0D000;font-weight:bold;'>Neutral</span></td>
				</tr>";
			}
		}
		echo "</table>";
	}


}
?>
