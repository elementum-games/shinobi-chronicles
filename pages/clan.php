<?php
/* 
File: 		clan.php
Coder:		Levi Meahan
Created:	04/27/2014
Revised:	04/27/2014 by Levi Meahan
Purpose:	Functions for clan management and activties
Algorithm:	See master_plan.html
*/
function clan() {
	global $system;
	global $player;
	global $self_link;
	global $RANK_NAMES;
	if(!$player->clan) {
		return false;
	}
	$page = 'HQ';
	if($_GET['page']) {
		$page = $_GET['page'];
	}
	// Available boosts
	$training_boosts = array(
		'ninjutsu_skill',
		'taijutsu_skill',
		'genjutsu_skill',
		'bloodline_skill',
		'cast_speed',
		'speed',
		'intelligence',
		'willpower',
		'jutsu',
	);
	$boost = explode(':', $player->clan['boost']);
	$current_boost_id = array_search($boost[1], $training_boosts);
	if($current_boost_id) {
		unset($training_boosts[$current_boost_id]);
	}
	// Mission stuff
	$max_mission_rank = 1;
	if($player->rank == 3) {
		$max_mission_rank = 3;
	}
	else if($player->rank == 4) {
		$max_mission_rank = 4;
	}
	$mission_rank_names = array(1 => 'D-Rank', 2 => 'C-Rank', 3 => 'B-Rank', 4 => 'A-Rank', 5 => 'S-Rank');
	$result = $system->query("SELECT `mission_id`, `name`, `rank` FROM `missions` WHERE `mission_type`=2 AND `rank` <= $max_mission_rank");	
	$missions = array();
	while($row = $system->db_fetch($result)) {
		$missions[$row['mission_id']] = $row;
	}
	// Check start mission
	if($_GET['start_mission']) {
		$mission_id = $_GET['start_mission'];
		try {
			if(!isset($missions[$mission_id])) {
				throw new Exception("Invalid mission!");
			}
			Mission::start($player, $mission_id);

			require("missions.php");
			runActiveMission();
			return true;
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}		
	}
	// Challenge stuff
	if($player->rank >= 3 && $page == 'challenge') {
		if($_GET['challenge']) {
			$challenge_position = $_GET['challenge'];
			$positions = array (
				1 => 'leader',
				2 => 'elder_1',
				3 => 'elder_2',
			);
			$position_holders = array();
			$result = $system->query("SELECT `leader`, `elder_1`, `elder_2` FROM `clans` WHERE `clan_id` = '{$player->clan['id']}'");
			if($system->db_last_num_rows > 0) {
					while($row = $system->db_fetch($result)) {
						foreach ($row as $position => $id) {
							$position_holders[] = $id;
						}
					}

			}
			try {
				if($player->rank < 4 && $challenge_position == 1) {
					throw new Exception("Unable to claim leader position at this rank.");
				}
				if($player->clan_office == $challenge_position) {
					throw new Exception("You cannot challenge yourself!");
				}
				if($player->clan_office) {
					throw new Exception("Please resign from your current position before taking a new one!");
				}
				// Check cooldown
				if($position_holders[$challenge_position - 1]) {
					throw new Exception("Position already taken!");
				}
				// Claim empty seat
				
				// Update clan data
                /** @noinspection SqlResolve */
                $system->query("UPDATE `clans` SET `{$positions[$challenge_position]}`='$player->user_id' WHERE `clan_id`='{$player->clan['id']}' LIMIT 1");
				// Update player data
				$player->clan_office = $challenge_position;
				// Display message
				$system->message(sprintf("You have claimed the clan %s %s position!", $player->clan['name'], ucfirst($positions[$challenge_position])));
				$page = 'controls';

			} catch (Exception $e) {
				$system->message($e->getMessage());
				$page = 'HQ';
			}
			$system->printMessage();
		}
	}
	// Office controls
	if($player->clan_office && $page == 'controls') {
		if($_POST['resign']) {
			$office = $player->clan_office;
			$office_names = array(1 => 'leader', 2 => 'elder_1', 3 => 'elder_2');
			if($_POST['confirm_resign']) {
                /** @noinspection SqlResolve */
                $system->query("UPDATE `clans` SET `{$office_names[$office]}`=0 WHERE `clan_id`='{$player->clan['id']}' LIMIT 1");
				$player->clan_office = 0;
				$player->clan[$office_names[$office]] = 0;
				$system->message("You have resigned as Clan {$player->clan['name']} " . ucfirst($office_names[$office]) . '.');
				$system->printMessage();
				$page = 'HQ';
			}
			else {
				echo "<table class='table'>
					<tr><th>Resign Office</th></tr>
					<tr><td style='text-align:center;'>
						Are you sure you want to resign as Clan {$player->clan['name']} " . ucfirst($office_names[$office]) . "?
						<br />
						<form action='$self_link&page=controls' method='post'>
							<input type='hidden' name='resign' value='1' />
							<input type='hidden' name='confirm_resign' value='1' />
							<button type='submit'>Resign</button>
						</form>
					</td></tr>
				</table>";
			}
		}
		else if($_POST['motto']) {
			$motto = $system->clean($_POST['motto']);
			try {
				if(strlen($motto) > 180) {
					throw new Exception("Motto is too long!");
				}
				$system->query("UPDATE `clans` SET `motto`='$motto' WHERE `clan_id`='{$player->clan['id']}' LIMIT 1");
				$player->clan['motto'] = $motto;
				$system->message("Motto updated!");
			} catch (Exception $e) {
				$system->message($e->getMessage());
			}
			$system->printMessage();
		}
		else if($_POST['logo']) {
			$logo = $system->clean($_POST['logo']);
			try {
				if(strlen($logo) > 150) {
					throw new Exception("Link is too long!");
				}
				$system->query("UPDATE `clans` SET `logo`='$logo' WHERE `clan_id`='{$player->clan['id']}' LIMIT 1");
				$player->clan['logo'] = $logo;
				$system->message("Logo updated!");
			} catch (Exception $e) {
				$system->message($e->getMessage());
			}
			$system->printMessage();
		}
		else if($_POST['boost']) {
			$new_boost = $system->clean($_POST['boost']);
			try {
				if(array_search($new_boost, $training_boosts) === false) {
					throw new Exception("Invalid boost!");
				}
				if($player->clan['points'] < 100) {
					throw new Exception("Not enough points!");
				}
				$new_boost = 'training:' . $new_boost;
				$boost_amount = 20;
				$player->clan['points'] -= 100;
				$system->query("UPDATE `clans` SET `boost`='$new_boost', `boost_amount`='$boost_amount', `points`=`points` - 100 
					WHERE `clan_id`='{$player->clan['id']}' LIMIT 1");
				$player->clan['boost'] = $new_boost;
				$player->clan['boost_amount'] = $boost_amount;
				$system->message("Boost updated!");
				$boost = explode(':', $player->clan['boost']);
			} catch (Exception $e) {
				$system->message($e->getMessage());
			}
			$system->printMessage();
		}
		else if($_POST['info']) {
			$info = $system->clean($_POST['info']);
			try {
				if(strlen($info) > 700) {
					throw new Exception("Clan info is too long!");
				}
				$system->query("UPDATE `clans` SET `info`='$info' WHERE `clan_id`='{$player->clan['id']}' LIMIT 1");
				$player->clan['info'] = $info;
				$system->message("Clan info updated!");
			} catch (Exception $e) {
				$system->message($e->getMessage());
			}
			$system->printMessage();
		}
	}
	// Submenu
	echo "<div class='submenu'>
	<ul class='submenu'>";
	// Leader/elder menu
	if($player->clan_office) {
		echo "<li style='width:24%;'><a href='{$self_link}'>Clan HQ</a></li>
		<li style='width:24%;'><a href='{$self_link}&page=members'>Members</a></li>
		<li style='width:25%;'><a href='{$self_link}&page=missions'>Missions</a></li>
		<li style='width:24%;'><a href='{$self_link}&page=controls'>Controls</a></li>";
	}
	else {
		echo "<li style='width:32.5%;'><a href='{$self_link}'>Clan HQ</a></li>
		<li style='width:33%;'><a href='{$self_link}&page=members'>Members</a></li>
		<li style='width:33%;'><a href='{$self_link}&page=missions'>Missions</a></li>";
	}
	echo "</ul>
	</div>
	<div class='submenuMargin'></div>";
	echo "<table class='table'><tr><th>" . $player->clan['name'] . " Clan</th></tr>
	<tr><td>
	<style>
	label {
		display: inline-block;
	}
	</style>
	<!--Clan Symbol-->
	<div style='float:right;width:100px;height:100px;margin:10px;margin-right:12px;'>
		<img src='{$player->clan['logo']}' style='max-width:100px;max-height:100px;' /></div>
	<div style='float:left;margin-top:8px;max-width:500px;'>
		<label style='width:7.2em;'>Village:</label>" . $player->clan['village'] . "<br />";
		if($boost[0] == 'training') {
			echo "<label style='width:7.2em;'>Boost:</label>" . 
				(int)$player->clan['boost_amount'] . "% faster " . ucwords(str_replace('_', ' ', $boost[1])) . " training<br />";
		}
		echo "<label style='width:7.2em;'>Reputation:</label>" . $player->clan['points'] . "<br />
		<p style='font-style:italic;text-align:center;width:75%;'>" . $player->clan['motto'] . "</p>
	</div>
	<br style='clear:both;margin:0;' />
	</td></tr></table>";
	// Members
	if($page == 'members') {
		// Pagination
		$users_per_page = 10;
		$min = 0;
		if($_GET['min']) {
			$min = (int)$system->clean($_GET['min']);
		}
		$result = $system->query("SELECT `user_name`, `rank`, `level`, `exp` FROM `users` 
			WHERE `clan_id`='{$player->clan['id']}' ORDER BY `rank` DESC, `exp` DESC LIMIT $min, $users_per_page");
		echo "<table class='table'><tr><th colspan='4'>Clan Members</th></tr>
		<tr>
			<th style='width:30%;'>Username</th>
			<th style='width:20%;'>Rank</th>
			<th style='width:20%;'>Level</th>
			<th style='width:30%;'>Experience</th>
		</tr>";
		while($row = $system->db_fetch($result)) {
			$class = '';
			if(is_int($count++ / 2)) {
				$class = 'row1';
			}
			else {
				$class = 'row2';
			}
			echo "<tr>
				<td style='width:29%;' class='$class'><a href='{$system->links['members']}&user={$row['user_name']}'>" . $row['user_name'] . "</a></td>
				<td style='width:20%;text-align:center;' class='$class'>" . $RANK_NAMES[$row['rank']] . "</td>
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
			echo "<a href='$self_link&page=members&min=$prev'>Previous</a>";
		}
		$result = $system->query("SELECT COUNT(`user_id`) as `count` FROM `users` WHERE `clan_id`='{$player->clan['id']}'");
		$result = $system->db_fetch($result);
		if($min + $users_per_page < $result['count']) {
			if($min > 0) {
				echo "&nbsp;&nbsp;|&nbsp;&nbsp;";
			}
			$next = $min + $users_per_page;
			echo "<a href='$self_link&page=members&min=$next'>Next</a>";
		}
		echo "</p>";
	}
	if($page == 'missions') {
		// Display missions
		$system->printMessage();
		$view = $max_mission_rank;
		if($_GET['view_rank']) {
			$view = (int)$_GET['view_rank'];
			if($view < 1 or $view > $max_mission_rank) {
				$view = $max_mission_rank;
			}
		}
		echo "<table class='table'><tr><th>Clan Missions</th></tr>
		<tr><td style='text-align:center;'>You can go on clan missions here.</td></tr>
		<tr><td style='text-align:center;'>";
		foreach($missions as $id => $mission) {
			echo "<a href='$self_link&start_mission=$id'><p class='button' style='margin:5px;'>" . $mission['name'] . "</p></a><br />";
		}
		echo "</td></tr></table>";
		return true;
	}
	// Officer controls
	if($player->clan_office && $page == 'controls') {
		// Resign
		echo "<table class='table' style='margin-bottom:0px;border-bottom-left-radius:0px;border-bottom-right-radius:0px;'>
			<tr><th>Controls</th></tr>
			<tr><td style='text-align:center;'>
					<form action='$self_link&page=controls' method='post'>
						<input type='hidden' name='resign' value='1' />
						<button type='submit'>Resign</button>
					</form>
			</td></tr>
		</table>
		<table class='table' style='margin-top:0px;border-top-left-radius:0px;border-top-right-radius:0px;'>
		";
		if($player->clan_office == 1) {
			echo "
			<tr>
				<th style='width:60%;border-radius:0px;'>Motto</th>
				<th style='width:40%;border-radius:0px;'>Logo</th>
			<tr>
				<td>	
					<!--Motto-->
					<div style='text-align:center;'>
						<form action='$self_link&page=controls' method='post'>
							<textarea name='motto' style='width:350px;'>{$player->clan['motto']}</textarea><br />
							<button type='submit'>Edit</button>
						</form>
					</div>
				</td>
				<td>	
					<!--Logo-->
					<div style='text-align:center;'>
						<form action='$self_link&page=controls' method='post'>
							<input type='text' name='logo' style='width:200px;' value='{$player->clan['logo']}' /><br />
							<button type='submit'>Edit</button>
						</form>
					</div>
				</td>
			</tr>
			<tr><th colspan='2'>Change Boost (100 Reputation)</th></tr>
			<tr><td colspan='2' style='text-align:center;'>
				<!--Boost-->
				<div style='text-align:center;'>
					<form action='$self_link&page=controls' method='post'>
						<select name='boost'>";
						foreach($training_boosts as $boost) {
							echo "<option value='$boost'>" . ucwords(str_replace('_', ' ', $boost)) . " training</option>";
						}
						echo "</select><br />
						<button type='submit'>Change Boost</button>
					</form>
				</div>
			</td></tr>";
			}
			echo "
			<tr><th colspan='2'>Clan Info</th></tr>
			<tr><td colspan='2' style='text-align:center;'>	
				<!--Info-->
				<form action='$self_link&page=controls' method='post'>
					<textarea name='info' style='width:550px;height:250px;'>{$player->clan['info']}</textarea><br />
					<button type='submit'>Edit</button>
				</form>
			</td></tr>
		</table>";
	}
	// Leaders / Clan Hall info stuff
	if($page == 'HQ') {
		// Load leader/elder IDs into array
		$officers = array();
		$query = "SELECT `leader`, `elder_1`, `elder_2` FROM `clans` WHERE `clan_id` ='{$player->clan['id']}'";
		$result = $system->query($query);
		if($system->db_last_num_rows > 0) {
			while ($row = $system->db_fetch($result)) {
				foreach ($row as $position => $id) {
					$officers[] = $id;
				}
			}
		}
		// Load data
		if(count($officers) > 0) {
			$query = "SELECT `user_name`, `avatar_link`, `clan_office` FROM `users` WHERE `user_id` IN (" . implode(',', $officers) . ")";
			$result = $system->query($query);
			$officers = array();
			$positions = array(
				"Elder 1" => 2,
				"Leader" => 1,
				"Elder 2" => 3,
			);
			if($system->db_last_num_rows > 0) {
				while($row = $system->db_fetch($result)) {
					$officers[$row['clan_office']] = $row;
				}
			}
		}
		echo "<table class='table'><tr><th>Clan Leaders</th></tr>
		<tr><td style='text-align:center;'>";
		foreach ($positions as $office => $position) {
			if($position == 1) {
				echo "
					<div style='display:inline-block;height:125px;width:125px;margin-right:20px;'>
				";
			}
			else {
				echo "
					<div style='display:inline-block;height:100px;width:100px;margin-right:20px;'>
				";
			}		
			if(isset($officers[$position])) {
				echo "<img src='" . $officers[$position]['avatar_link'] . "' /><br />
				<span style='font-weight:bold;'>
					<a href='{$system->links['members']}&user={$officers[$position]['user_name']}'>" . $officers[$position]['user_name'] . "</a></span><br />";
				if($player->rank >= 4 && $player->clan_office != $position) {
					// echo "<a href='$self_link&page=challenge&challenge=$position'>(Challenge)</a>";
				}
				echo "<br />";
			}
			else {
				echo "<img src='../images/default_avatar.png' style='max-width:100px;max-height:100px;' /><br />
				<span style='font-weight:bold;'>None</span><br />";
				if(!$player->clan_office) {
					if($player->rank >= 4 && $position == 1) {
						 echo " <a style='text-decoration:none;' href='$self_link&page=challenge&challenge=$position'>(Claim)</a>";
					}
					else if($player->rank >= 3) {
						echo " <a style='text-decoration:none;' href='$self_link&page=challenge&challenge=$position'>(Claim)</a>";
					}
				}
				echo "<br />";
			}
			echo "$office 
			</div>";
		}
		echo "
		</td></tr>
		</table>
		<table class='table'><tr><th>Clan Hall</th></tr>
		<tr><td style='text-align:center;'>
		{$player->clan['info']}
		</td></tr>
		</table>";
	}
}