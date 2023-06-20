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
		<li style='width:20%;'><a href='{$system->router->links['villageHQ']}&view=village_info'>Village Info</a></li>
		<li style='width:18.5%;'><a href='{$system->router->links['villageHQ']}&view=members'>Members</a></li>
		<li style='width:20%;'><a href='{$system->router->links['villageHQ']}&view=clans_teams'>Clans/Teams</a></li>
		<li style='width:18.5%;'><a href='{$system->router->links['villageHQ']}&view=world_info'>World Info</a></li>
		<li style='width:20%;'><a href='{$system->router->links['villageHQ']}&view=sensei'>Sensei</a></li>
	</ul>
	</div>
	<div class='submenuMargin'></div>";

	$view = 'village_info';
	if(isset($_GET['view'])) {
		$view = $_GET['view'];
	}

	if($view == 'village_info') {
		$result = $system->db->query("SELECT * FROM `villages` WHERE `name`='{$player->village->name}'");
		$village_data = $system->db->fetch($result);

		$result = $system->db->query(
            "SELECT
                COUNT(IF(`rank`=1,1,NULL)) as `count_1`,
                COUNT(IF(`rank`=2,1,NULL)) as `count_2`,
                COUNT(IF(`rank`=3,1,NULL)) as `count_3`,
                COUNT(IF(`rank`=4,1,NULL)) as `count_4`
                FROM `users` WHERE `village`='{$player->village->name}'"
        );
		$villager_counts = $system->db->fetch($result);


		$leader_name = 'None';
		$leader_avatar = './images/default_avatar.png';
		if($village_data['leader']) {
			$result = $system->db->query(
                "SELECT `user_name`, `avatar_link` FROM `users` WHERE `user_id`={$village_data['leader']} LIMIT 1"
            );
			if($system->db->last_num_rows > 0) {
				$result = $system->db->fetch($result);
				$leader_name = $result['user_name'];
				$leader_avatar = $result['avatar_link'];
			}
		}

		echo "<table class='table'><tr><th>{$player->village->name} Village</th></tr>
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
			<img src='./images/village_icons/" . strtolower($player->village->name) . "_large.png' style='max-width:64px;max-height:64px;' />
		</div>
		<br style='margin:0px;clear:both;' />
		</td></tr></table>";
	}
	else if($view == 'members') {
		// Load rank data
		$ranks = array();
		$result = $system->db->query("SELECT `rank_id`, `name` FROM `ranks`");
		while($rank = $system->db->fetch($result)) {
			$ranks[$rank['rank_id']]['name'] = $rank['name'];
		}

		// Pagination
		$users_per_page = 10;
		$min = 0;
		if(isset($_GET['min'])) {
			$min = (int)$system->db->clean($_GET['min']);
		}

		$result = $system->db->query(
            "SELECT `user_name`, `rank`, `level`, `exp` FROM `users`
                WHERE `village`='{$player->village->name}' ORDER BY `rank` DESC, `exp` DESC LIMIT $min, $users_per_page"
        );

		echo "<table class='table'><tr><th colspan='4'>Village Members</th></tr>
		<tr>
			<th style='width:30%;'>Username</th>
			<th style='width:20%;'>Rank</th>
			<th style='width:20%;'>Level</th>
			<th style='width:30%;'>Experience</th>
		</tr>";

		$count = 0;
		while($row = $system->db->fetch($result)) {
			$class = '';
			if(is_int($count++ / 2)) {
				$class = 'row1';
			}
			else {
				$class = 'row2';
			}

			echo "<tr id='villagemembertd' class='fourColGrid table_multicolumns'>
				<td style='width:29%;' class='$class'><a href='{$system->router->links['members']}&user={$row['user_name']}'>" . $row['user_name'] . "</a></td>
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
		$result = $system->db->query(
            "SELECT COUNT(`user_id`) as `count` FROM `users` WHERE `village`='{$player->village->name}'"
        );
		$result = $system->db->fetch($result);
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
		$result = $system->db->query(
            "SELECT * FROM `clans` WHERE `village`='{$player->village->name}' ORDER BY `points` DESC LIMIT 5"
        );
		$clans = array();
		while($row = $system->db->fetch($result)) {
			$clans[] = $row;
			if(array_search($row['leader'], $user_id_array) === false) {
				$user_id_array[] = $row['leader'];
			}
		}

		// Teams
		$result = $system->db->query(
            "SELECT * FROM `teams` WHERE `village`='{$player->village->name}' ORDER BY `points` DESC LIMIT 5"
        );
		$teams = array();
		while($row = $system->db->fetch($result)) {
			$teams[] = $row;
			if(array_search($row['leader'], $user_id_array) === false) {
				$user_id_array[] = $row['leader'];
			}
		}

		// Fetch leader names
		$user_id_string = implode(',', $user_id_array);
		$result = $system->db->query("SELECT `user_id`, `user_name` FROM `users` WHERE `user_id` IN ($user_id_string)");
		$user_names = array();
		while($row = $system->db->fetch($result)) {
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
			echo "<tr class='table_multicolumns'>
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
			echo "<tr class='table_multicolumns'>
				<td><a href='{$system->router->links['members']}&view_team={$row['team_id']}'>" . $row['name'] . "</td>
				<td style='text-align: center;'>" . $user_names[$row['leader']] . "</td>
				<td style='text-align:center;'>" . $row['points']  . "</td>
			</tr>";
		}
		echo "</table>";
	}
	else if($view == 'world_info') {
		// World info
		$result = $system->db->query("SELECT * FROM `villages`");
		$villages = array();
		while($row = $system->db->fetch($result)) {
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
		$result = $system->db->query($count_query);
		$village_counts = $system->db->fetch($result);

		echo "<table class='table'><tr><th colspan='4'>Villages</th></tr>
		<tr>
			<th style='width:25%;'>Village</th>
			<th style='width:25%;'>Points</th>
			<th style='width:25%;'>Members</th>
			<th style='width:25%;'>Alliance</th>
		</tr>";
		if(is_array($villages)) {
			foreach($villages as $village) {
				echo "<tr id='villages' class='fourColGrid table_multicolumns'>
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
	else if ($view == 'sensei') {
		// If exam submitted
		if (isset($_POST['submit_exam'])) {
			try {
				// check if already sensei
				if (SenseiManager::isActiveSensei($player->sensei_id, $system)) {
                    throw new Exception('You do not meet the requirements!');
                }
				// check rank
                if ($player->rank_num < 4) {
                    throw new Exception('You do not meet the requirements!');
                }
				// check level
                if ($player->level < 75) {
                    throw new Exception('You do not meet the requirements!');
                }
				// check justu mastered
                $mastered_count = 0;
                $player->getInventory();
                foreach ($player->jutsu as $jutsu) {
                    if ($jutsu->level == 100) {
                        $mastered_count++;
                    }
                }
                if ($mastered_count < 5) {
                    throw new Exception('You do not meet the requirements!');
                }
				$answers = [$_POST['question1'], $_POST['question2'], $_POST['question3'], $_POST['question4'], $_POST['question5'], $_POST['question6']];
				if (SenseiManager::scoreExam($answers, $system)) {
					$success = SenseiManager::addSensei($player->user_id, $_POST['specialization'], $system);
					if (!$success) {
                        throw new Exception('Something went wrong!');
                    }
					$system->message("You passed!");
				}
				else {
                    throw new Exception('Check your answers and try again!');
                }
            }
			catch (Exception $e) {
				$system->message($e->getMessage());
            }
        }
		// If resignation confirmed
		if (isset($_POST['confirm_resignation'])) {
			try {
				// check if sensei
				if (!SenseiManager::isActiveSensei($player->user_id, $system)) {
                    throw new Exception('You are not a sensei!');
                }
                $success = SenseiManager::removeSensei($player->user_id, $system);
				if (!$success) {
                    throw new Exception('Something went wrong!');
                }
				$system->message("You have resigned as Sensei!");
            }
			catch (Exception $e) {
                $system->message($e->getMessage());
            }
        }
		// If resign clicked, set flag
		$resign = false;
		if (isset($_GET['resign'])) {
            if (SenseiManager::isActiveSensei($player->user_id, $system)) {
                $resign = true;
            }
        }
		// If kick student
		if (isset($_GET['kick'])) {
			try {
				$success = SenseiManager::removeStudent($player->user_id, (int)$_GET['kick'], $system);
				if (!$success) {
                    throw new Exception('Something went wrong!');
                }
				$system->message("You have kicked your student!");
            }
			catch (Exception $e) {
                $system->message($e->getMessage());
            }
        }
		// If leave sensei
		if (isset($_GET['leave'])) {
			try {
				$success = SenseiManager::removeStudent($player->sensei_id, $player->user_id, $system);
				if (!$success) {
                    throw new Exception('Something went wrong!');
                }
				$player->sensei_id = 0;
				$system->message("You have left your Sensei!");
            }
			catch (Exception $e) {
                $system->message($e->getMessage());
            }
        }
		// If create application
		if (isset($_GET['apply'])) {
            try {
				$sensei = User::loadFromId($system, (int)$_GET['apply'], true);
				// check if already student
				if ($player->sensei_id != 0) {
                    throw new Exception('You already have a sensei!');
                }
				// check eligibility
				if ($player->rank_num > 2)
                {
                    throw new Exception('You are not eligible to become a student!');
                }
				// check is sensei
				if (!SenseiManager::isActiveSensei($sensei->user_id, $system)) {
                    throw new Exception('Player is not a valid sensei!');
                }
				// check village
				if ($sensei->village->name != $player->village->name) {
                    throw new Exception('Player is not a valid sensei!');
                }
				// check if accepting students
				if (!$sensei->accept_students) {
                    throw new Exception('Player is not accepting students!');
                }
				// check if slot available
				if (!SenseiManager::hasSlot($sensei->user_id, $system)) {
					throw new Exception('No student slots available!');
				}
				$success = SenseiManager::createApplication((int)$_GET['apply'], $player->user_id, $system);
				if (!$success) {
                    throw new Exception('Something went wrong!');
                }
				$system->message("You have submitted an application!");
            }
			catch (Exception $e) {
                $system->message($e->getMessage());
            }
        }
		// If cancel application
		if (isset($_GET['close'])) {
            try {
				$success = SenseiManager::closeApplication((int)$_GET['close'], $player->user_id, $system);
				if (!$success) {
                    throw new Exception('Something went wrong!');
                }
				$system->message("You have closed an application!");
            }
			catch (Exception $e) {
                $system->message($e->getMessage());
            }
        }
		// If accept application
		if (isset($_GET['accept'])) {
            try {
				$success = SenseiManager::acceptApplication($player->user_id, (int)$_GET['accept'], $system);
				if (!$success) {
                    throw new Exception('Something went wrong!');
                }
				$system->message("You have accepted an application!");
            }
			catch (Exception $e) {
                $system->message($e->getMessage());
            }
        }
		// If deny application
		if (isset($_GET['deny'])) {
            try {
				$success = SenseiManager::closeApplication($player->user_id, (int)$_GET['deny'], $system);
				if (!$success) {
                    throw new Exception('Something went wrong!');
                }
				$system->message("You have denied an application!");
            }
			catch (Exception $e) {
                $system->message($e->getMessage());
            }
        }
		// If mod clear message
		if (isset($_GET['clear'])) {
            try {
				if (!$player->staff_manager->isModerator()) {
                    throw new Exception('Not a moderator!');
                }
				$success = SenseiManager::updateStudentRecruitment((int)$_GET['clear'], '', $system);
				if (!$success) {
                    throw new Exception('Something went wrong!');
                }
				$system->message("You have removed a recruitment message!");
            }
			catch (Exception $e) {
                $system->message($e->getMessage());
            }
        }
        $lesson = false;
        if (isset($_POST['lesson'])) {
            try {
                $lesson_data = [];
                $lesson_data['sensei_name'] = $system->db->clean($_POST['sensei_name']);
                $lesson_data['stat'] = $system->db->clean($_POST['lesson']);
                $lesson_data['train_type'] = $system->db->clean($_POST['train_type']);
                $lesson_data['sensei_id'] = $system->db->clean($_POST['sensei_id']);
                $lesson_duration = SenseiManager::getLessonDurationForPlayer($player, $system);
                $lesson_cost = SenseiManager::getLessonCostForPlayer($player, $system);
                switch ($_POST['train_type']) {
                    case 'short':
                        $lesson_data['duration'] = $lesson_duration['short'] / 60;
                        $lesson_data['cost'] = $lesson_cost['short'];
                        break;
                    case 'long':
                        $lesson_data['duration'] = $lesson_duration['long'] / 60;
                        $lesson_data['cost'] = $lesson_cost['long'];
                        break;
                    case 'extended':
                        $lesson_data['duration'] = $lesson_duration['extended'] / 60;
                        $lesson_data['cost'] = $lesson_cost['extended'];
                        break;
                    default:
                        throw new Exception('Invalid train duration!');
                }
                $lesson = true;
            }
			catch (Exception $e) {
                $system->message($e->getMessage());
            }
        }
        if (isset($_POST['confirm_lesson'])) {
            try {
                $lesson_data = [];
                $lesson_data['sensei_id'] = $system->db->clean($_POST['sensei_id']);
                $lesson_data['lesson_stat'] = $system->db->clean($_POST['lesson_stat']);
                $lesson_data['train_type'] = $system->db->clean($_POST['train_type']);
                $lesson_sensei = SenseiManager::getSenseiByID($lesson_data['sensei_id'], $system);

				// If minimum rank
                if ($player->rank_num < 3) {
                    throw new Exception('Insufficient rank!');
                }

				// If not training
                if ($player->train_time > 0) {
                    throw new Exception('Training already in progress!');
                }

				// If sensei
                if (!isset($lesson_sensei)) {
                    throw new Exception('Target player is not a valid sensei!');
                }

				// If active
				if (!SenseiManager::isActiveSensei($lesson_data['sensei_id'], $system)) {
					throw new Exception('Target player is not a valid sensei!');
                }
                $lesson_sensei += SenseiManager::getSenseiUserData($lesson_data['sensei_id'], $system);

				// If in village
				if ($player->village->name != $lesson_sensei['village']) {
					throw new Exception('Target player is not a valid sensei!');
                }

				// If accepting lessons
                if (!((bool) $lesson_sensei['enable_lessons'] && (bool) $lesson_sensei['accept_students'])) {
                    throw new Exception('Target player is not a valid sensei!');
                }

				// If has slot available
                if (!SenseiManager::hasSlot($lesson_data['sensei_id'], $system)) {
                    throw new Exception('Target player is not a valid sensei!');
                }
				$lesson_cost = SenseiManager::getLessonCostForPlayer($player, $system)[$lesson_data['train_type']];
				$lesson_duration = SenseiManager::getLessonDurationForPlayer($player, $system)[$lesson_data['train_type']];
                switch ($_POST['lesson_stat']) {
                    case 'ninjutsu_skill':
                        $lesson_modifier = SenseiManager::getLessonModifier($player->ninjutsu_skill, $player->exp, $lesson_sensei['ninjutsu_skill'], $lesson_sensei['exp'], $lesson_sensei['specialization'] == 'ninjutsu_skill' ? true : false);
                        break;
                    case 'taijutsu_skill':
                        $lesson_modifier = SenseiManager::getLessonModifier($player->taijutsu_skill, $player->exp, $lesson_sensei['taijutsu_skill'], $lesson_sensei['exp'], $lesson_sensei['specialization'] == 'taijutsu_skill' ? true : false);
                        break;
                    case 'genjutsu_skill':
                        $lesson_modifier = SenseiManager::getLessonModifier($player->genjutsu_skill, $player->exp, $lesson_sensei['genjutsu_skill'], $lesson_sensei['exp'], $lesson_sensei['specialization'] == 'genjutsu_skill' ? true : false);
                        break;
                    case 'bloodline_skill':
						if ($player->bloodline_id == $lesson_sensei['bloodline_id']) {
							$lesson_modifier = round(SenseiManager::getLessonModifier($player->bloodline_skill, $player->exp, $lesson_sensei['bloodline_skill'], $lesson_sensei['exp'], true) * 100 - 100, 2);
						} else {
							throw new Exception('Invalid stat!');
                        }
                        break;
                    case 'speed':
                        $lesson_modifier = SenseiManager::getLessonModifier($player->speed, $player->exp, $lesson_sensei['speed'], $lesson_sensei['exp'], $lesson_sensei['specialization'] == 'speed' ? true : false);
                        break;
                    case 'cast_speed':
                        $lesson_modifier = SenseiManager::getLessonModifier($player->cast_speed, $player->exp, $lesson_sensei['cast_speed'], $lesson_sensei['exp'], $lesson_sensei['specialization'] == 'cast_speed' ? true : false);
                        break;
                    default:
                        throw new Exception('Invalid stat!');
                }

				// Verify training has minimum increase
				if ($lesson_modifier <= 1) {
					throw new Exception('Invalid stat!');
                }

				// Verify player can afford training
				$player->subtractMoney($lesson_cost, "Paid " . $lesson_cost . "&yen; for lessons.");

                // Add yen to sensei
                $sensei_player = User::loadFromId($system, $lesson_sensei['sensei_id']);
                $sensei_player->loadData(User::UPDATE_NOTHING);
                $sensei_player->addMoney($lesson_cost / 5, "Earned " . $lesson_cost / 5 . "&yen; for lessons.");
                $sensei_player->updateData();

				// Set training for player
				$stat_train_gain = 4 + ($player->rank_num * 4);
				$stat_long_train_gain = $stat_train_gain * 2.25;
                $stat_extended_train_gain = $stat_train_gain * 12;
				$stat_long_train_gain *= $player->forbidden_seal->long_training_gains;
                $stat_extended_train_gain = round($stat_extended_train_gain * $player->forbidden_seal->extended_training_gains);
                $stat_train_gain += $system->TRAIN_BOOST;
                $stat_long_train_gain += $system->LONG_TRAIN_BOOST;
                $stat_extended_train_gain += ($system->LONG_TRAIN_BOOST * 5);
                switch ($lesson_data['train_type']) {
                    case 'short':
                        $lesson_gain = $stat_train_gain * $lesson_modifier;
                        break;
                    case 'long':
                        $lesson_gain = $stat_long_train_gain * $lesson_modifier;
                        break;
                    case 'extended':
                        $lesson_gain = $stat_extended_train_gain * $lesson_modifier;
                        break;
                    default:
                        throw new Exception('Invalid duration!');
                }
                if ($player->total_stats >= $player->rank->stat_cap) {
                    throw new Exception("You cannot train any more at this rank!");
                }
                $player->log(User::LOG_TRAINING, "Type: {$lesson_data['lesson_stat']} / Length: {$lesson_duration}");
                $player->train_type = $lesson_data['lesson_stat'];
                $player->train_gain = $lesson_gain;
                $player->train_time = time() + $lesson_duration;

				// Create notification for player
                require_once __DIR__ . '/../classes/notification/NotificationManager.php';
                $new_notification = new NotificationDto(
                    type: "training",
                    message: "Training " . System::unSlug($lesson_data['lesson_stat']),
                    user_id: $player->user_id,
                    created: time(),
                    duration: $lesson_duration,
                    alert: false,
                );
                NotificationManager::createNotification($new_notification, $system, NotificationManager::UPDATE_REPLACE);

				// Log lesson
                if (!SenseiManager::logLesson($lesson_sensei['sensei_id'], $player->user_id, $lesson_duration, $lesson_sensei['temp_students'], (int)$lesson_sensei['yen_gained'] + ((int)$lesson_cost / 5), (int)$lesson_sensei['time_trained'] + (int)$lesson_duration, $system)) {
                    throw new Exception('Something went wrong!');
                }

				// Update
				$player->updateData();
                $system->message("Lesson started for " . $lesson_cost . "&yen;.");
            }
			catch (Exception $e) {
                $system->message($e->getMessage());
            }
        }
		// If exam started
		if (isset($_GET['sensei_exam'])) {
			try {
				// check if already sensei
                if (SenseiManager::isActiveSensei($player->user_id, $system)) {
                    throw new Exception('You do not meet the requirements!');
                }
                // check rank
                if ($player->rank_num < 4) {
                    throw new Exception('You do not meet the requirements!');
                }
                // check level
                if ($player->level < 75) {
                    throw new Exception('You do not meet the requirements!');
                }
                // check justu mastered
                $mastered_count = 0;
                $player->getInventory();
                foreach ($player->jutsu as $jutsu) {
                    if ($jutsu->level == 100) {
                        $mastered_count++;
                    }
                }
                if ($mastered_count < 5) {
                    throw new Exception('You do not meet the requirements!');
                }
            }
			catch (Exception $e) {
                $system->message($e->getMessage());
            }
			require 'templates/sensei_exam.php';
        }
		// Default
		else {
			$applications = [];
			// If Sensei
			if (SenseiManager::isActiveSensei($player->user_id, $system)) {
                $applications = SenseiManager::getApplicationsBySensei($player->user_id, $system);
            }
			// If eligible Student
			else if ($player->sensei_id == 0 && $player->rank_num < 3) {
                $applications = SenseiManager::getApplicationsByStudent($player->user_id, $system);
            }
			// If staff
			if (isset($_GET['village'])) {
                if ($player->staff_manager->isModerator()) {
					$sensei_list = SenseiManager::getSenseiByVillage($system->db->clean($_GET['village']), $system);
				}
				else {
					$sensei_list = SenseiManager::getSenseiByVillage($player->village->name, $system);
				}
            }
			// Default
			else {
				$sensei_list = SenseiManager::getSenseiByVillage($player->village->name, $system);
            }
			// Calculate lesson modifiers
            foreach ($sensei_list as &$sensei) {
                $sensei['ninjutsu_modifier'] = round(SenseiManager::getLessonModifier($player->ninjutsu_skill, $player->exp, $sensei['ninjutsu_skill'], $sensei['exp'], $sensei['specialization'] == 'ninjutsu_skill' ? true : false) * 100 - 100, 2);
                $sensei['taijutsu_modifier'] = round(SenseiManager::getLessonModifier($player->taijutsu_skill, $player->exp, $sensei['taijutsu_skill'], $sensei['exp'], $sensei['specialization'] == 'taijutsu_skill' ? true : false) * 100 - 100, 2);
                $sensei['genjutsu_modifier'] = round(SenseiManager::getLessonModifier($player->genjutsu_skill, $player->exp, $sensei['genjutsu_skill'], $sensei['exp'], $sensei['specialization'] == 'genjutsu_skill' ? true : false) * 100 - 100, 2);
                if ($player->bloodline_id == $sensei['bloodline_id']) {
                    $sensei['bloodline_modifier'] = round(SenseiManager::getLessonModifier($player->bloodline_skill, $player->exp, $sensei['bloodline_skill'], $sensei['exp'], true) * 100 - 100, 2);
                }
                $sensei['speed_modifier'] = round(SenseiManager::getLessonModifier($player->speed, $player->exp, $sensei['speed'], $sensei['exp'], $sensei['specialization'] == 'speed' ? true : false) * 100 - 100, 2);
                $sensei['cast_speed_modifier'] = round(SenseiManager::getLessonModifier($player->cast_speed, $player->exp, $sensei['cast_speed'], $sensei['exp'], $sensei['specialization'] == 'cast_speed' ? true : false) * 100 - 100, 2);
                unset($sensei);
            }
            $lesson_cost = SenseiManager::getLessonCostForPlayer($player, $system);
			require 'templates/sensei.php';
        }

		$system->printMessage();
    }
}
?>
