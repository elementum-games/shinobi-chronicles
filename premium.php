<?php
/*
File: 		premium.php
Coder:		Levi Meahan
Created:	04/30/2014
Revised:	04/30/2014 by Levi Meahan
Purpose:	Function for premium credit shop. Resets, name changes, bloodline re-rolls, etc
*/

function premium() {
	global $system;

	global $player;

	global $self_link;

	$costs['user_reset'] = 0;
	$costs['name_change'] = 15;
	$costs['bloodline'][1] = 80;
	$costs['bloodline'][2] = 60;
	$costs['bloodline'][3] = 40;
	$costs['bloodline'][4] = 20;
	$costs['forbidden_seal'][1] = 5;
	$costs['forbidden_seal'][2] = 15;
	$costs['village_change'] = 5 * $player->village_changes;
	$costs['clan_change'] = 5 * $player->clan_changes;
	if($costs['village_change'] > 40) {
		$costs['village_change'] = 40;
	}
	if($costs['clan_change'] > 40) {
		$costs['clan_change'] = 40;
	}

    $free_stat_change_timer = 86400;

	$available_clans = array();

	if($player->clan) {

		$system->query("SELECT `clan_id`, `name` FROM `clans` WHERE `village` = '$player->village' AND `clan_id` != '{$player->clan['id']}' AND `bloodline_only` = '0'");

		while($village_clans = $system->db_fetch()) {
			$available_clans[$village_clans['clan_id']] = stripslashes($village_clans['name']);
		}

	}

	if($player->bloodline_id && $player->clan['id'] != $player->bloodline->clan_id) {
		$system->query(sprintf("SELECT `clan_id`, `name` FROM `clans` WHERE `clan_id` = '%d'", $player->bloodline->clan_id));
		$result = $system->db_fetch();
		$available_clans[$result['clan_id']] = stripslashes($result['name']);
	}

	if(isset($_POST['user_reset'])) {
		try {
			if($player->premium_credits < $costs['user_reset']) {
				throw new Exception("You do not have enough Ancient Kunai!");
			}

			if(!isset($_POST['confirm_reset'])) {
				echo "<table class='table'><tr><th>Confirm Reset</th></tr>
				<tr><td style='text-align:center;'>Are you sure you want to reset your character? You will lose all your stats,
				bloodline, rank, and clan. You will keep your money.<br />
				<form action='$self_link' method='post'>
				<input type='hidden' name='confirm_reset' value='1' />
				<input type='submit' name='user_reset' value='Reset my Account' />
				</form>
				</td></tr></table>";
				return true;
				throw new Exception('');
			}

			if($player->team) {
				throw new Exception("You must leave your team before resetting!");
			}

			$player->level = 1;
			$player->level = 1;
			$player->rank = 1;
			$player->health = 100;
			$player->max_health = 100;
			$player->stamina = 100;
			$player->max_stamina = 100;
			$player->chakra = 100;
			$player->max_chakra = 100;
			$player->regen_rate = 10;
			$player->exp = User::BASE_EXP;
			$player->bloodline_id = 0;
			$player->bloodline_name = '';
			$player->clan = array();
			$player->clan['id'] = 0;
			$player->location = $player->village_location;
			$player->pvp_wins = 0;
			$player->pvp_losses = 0;
			$player->ai_wins = 0;
			$player->ai_losses = 0;
			$player->monthly_pvp = 0;
			$player->ninjutsu_skill = 10;
			$player->genjutsu_skill = 10;
			$player->taijutsu_skill = 10;
			$player->bloodline_skill = 0;
			$player->cast_speed = 5;
			$player->speed = 5;
			$player->strength = 5;
			$player->intelligence = 5;
			$player->willpower = 5;

			//Bug fix: Elements previously was not cleared. -- Shadekun
			$player->elements = [];

			$player->premium_credits -= $costs['user_reset'];

			$player->exam_stage = 0;

			$player->updateData();

			$system->query("DELETE FROM `user_bloodlines` WHERE `user_id`='$player->user_id'");
			$system->query("UPDATE `user_inventory` SET
				`jutsu` = '',
				`items` = '',
				`bloodline_jutsu` = '',
				`equipped_jutsu` = '',
				`equipped_items` = ''
				WHERE `user_id`='$player->user_id'");


			echo "<table class='table'><tr><th>Character Reset</th></tr>
			<tr><td style='text-align:center;'>
			You have reset your character.<br />
			<a href='{$system->link}?id=1'>Continue</a>
			</td></tr>
			</table>";
			return true;
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
	}
	else if(isset($_POST['name_change'])) {
		$new_name = $system->clean($_POST['new_name']);
		$nameCost = 1;
		$akCost = $costs['name_change'];
		try {
			if(!$player->username_changes and $player->premium_credits < $akCost) {
				throw new Exception("You do not have enough Ancient Kunai!");
			}
			if(strlen($new_name) < User::MIN_NAME_LENGTH || strlen($new_name) >= 18) {
				throw new Exception("New user name is to short/long! Please enter a name between 4 and 18 characters long.");
			}
			if($player->user_name == $new_name) {
				throw new Exception("Please select a different name than your current one.");
			}
			if(!preg_match('/^[a-zA-Z0-9_-]+$/', $new_name)) {
				throw new Exception("Only alphanumeric characters, dashes, and underscores are allowed in usernames!");
			}

			if($system->censor_check($new_name)) {
				throw new Exception("Inappropriate language is not allowed in usernames!");
			}

			if($player->username_changes > 0){
				$akCost = 0;
			}
			else {
				$nameCost = 0;
			}

			$result = $system->query("SELECT `user_name` FROM `users` WHERE `user_name`='$new_name' LIMIT 1");
			if($system->db_num_rows) {
				$result = $system->db_fetch();
				if(strtolower($player->user_name) == strtolower($new_name)) {
					$nameCost = 0;
					$akCost = 0;
				}
				else if(strtolower($result['user_name']) == strtolower($new_name)) {
					throw new Exception("Username already in use!");
				}
			}

			if(!isset($_POST['confirm_nameChange'])) {
				echo "<table class='table'><tr><th>Confirm Change</th></tr>
				<tr><td style='text-align:center;'>Are you sure you want to change your username?
				Doing this will also change your log in name to the new selected one.
				Please remember to clear your inbox before changing name.<br />
				<form action='$self_link' method='post'>
				<input type='hidden' name='confirm_nameChange' value='1' />
				<input type='text' name='new_name' value='{$new_name}'/>
				<input type='submit' name='name_change' value='Confirm Change' />
				</form>
				</td></tr></table>";
				return true;
				throw new Exception('');
			}

			$sql = "UPDATE `users` SET `user_name` = '%s',  `premium_credits` = `premium_credits` - %d, `username_changes` = `username_changes` - %d WHERE `user_id` = %d LIMIT 1;";

			$system->query(sprintf($sql, $new_name, $akCost, $nameCost, $player->user_id));
			$player->premium_credits -= $akCost;

			echo "<table class='table'><tr><th>Username Change</th></tr>
			<tr><td style='text-align:center;'>
			You have changed your username to {$new_name}.<br />
			<a href='{$system->link}?id=1'>Continue</a>
			</td></tr>
			</table>";
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
	}
	else if(isset($_POST['stat_reset'])) {
		$stats = array('ninjutsu_skill', 'taijutsu_skill', 'genjutsu_skill', 'cast_speed', 'speed', 'intelligence', 'willpower');
		if($player->bloodline_id) {
			array_unshift($stats, 'bloodline_skill');
		}
		try {
			$stat = $system->clean($_POST['stat']);
			if(array_search($stat, $stats) === false) {
				throw new Exception("Invalid stat!");
			}

			$cost = floor($player->{$stat} / 200);
			if($player->premium_credits < $cost) {
				throw new Exception("You do not have enough Ancient Kunai!");
			}

			// Amount to reset to
			$reset_amount = 5;
			if(strpos($stat, 'skill')) {
				$reset_amount = 10;
			}

			if(!isset($_POST['confirm'])) {
				echo "<table class='table'><tr><th>Confirm stat reset</th></tr>
				<tr><td style='text-align:center;'>
				Are you sure you want to reset your " . ucwords(str_replace('_', ' ', $stat)) . " from " . $player->{$stat} . " -> $reset_amount?
				<form action='$self_link&view=character_changes' method='post'>
				<input type='hidden' name='stat' value='$stat' />
				<input type='hidden' name='confirm' value='1' />
				<input type='submit' name='stat_reset' value='Confirm reset' />
				</form>
				</td></tr></table>";

				throw new Exception('');
			}

			$player->premium_credits -= $cost;



			$exp = ($player->{$stat} - $reset_amount) * 10;

			$player->{$stat} = $reset_amount;
			$player->exp -= $exp;

			$system->message("You have reset your " . ucwords(str_replace('_', ' ', $stat)) . " to $reset_amount.");
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
	else if(isset($_POST['stat_allocate'])) {
		$stats = array('ninjutsu_skill', 'taijutsu_skill', 'genjutsu_skill', 'cast_speed', 'speed', 'intelligence', 'willpower');
		if($player->bloodline_id) {
			array_unshift($stats, 'bloodline_skill');
		}

		try {
			$original_stat = $system->clean($_POST['original_stat']);
			$target_stat = $system->clean($_POST['target_stat']);
			if(array_search($original_stat, $stats) === false) {
				throw new Exception("Invalid original stat!");
			}
			if(array_search($target_stat, $stats) === false) {
				throw new Exception("Invalid target stat!");
			}

            // Check for same stat
            if($original_stat == $target_stat) {
                throw new Exception("You cannot transfer points to the same stat!");
            }

            // Amount to reset to
            $reset_amount = 5;
            if(strpos($original_stat, 'skill') !== false) {
                $reset_amount = 10;
            }

			// Transfer amount
			$transfer_amount = (int)$system->clean($_POST['transfer_amount']);

			if($transfer_amount < 1) {
				throw new Exception("Invalid transfer amount!");
			}
			if($transfer_amount > $player->{$original_stat} - $reset_amount) {
				throw new Exception("Invalid transfer amount!");
			}

            $is_free_stat_change = $transfer_amount <= 10;


			if($is_free_stat_change) {
				$cost = 0;

				// Check for last free stat change
	            if($player->last_free_stat_change > time() - $free_stat_change_timer) {
	                throw new Exception (
	                    "You cannot stat transfer for free currently." . "<br />" .
                        "Time remaining: " . System::timeRemaining(
                            $player->last_free_stat_change - (time() - $free_stat_change_timer),
                            'long',
                            false,
                            true
                        )
                    );
	            }
			} else {
				$cost = 1 + floor($transfer_amount / 300);
			}

			if($player->premium_credits < $cost) {
				throw new Exception("You do not have enough Ancient Kunai!");
			}

			$time = $transfer_amount * 0.2;

			// Check for minimum stat amount
			if($player->{$original_stat} <= $reset_amount) {
				throw new Exception("Stat is already at the minimum!");
			}

			// Check for player training
			if($player->train_time) {
				throw new Exception("Please finish or cancel your training!");
			}

			 if(!isset($_POST['confirm'])) {
				echo "<table class='table'><tr><th>Confirm stat reset</th></tr>
				<tr><td style='text-align:center;'>
				Are you sure you want to transfer $transfer_amount " . ucwords(str_replace('_', ' ', $original_stat)) . " to " .
					ucwords(str_replace('_', ' ', $target_stat)) . "?<br />" .
				ucwords(str_replace('_', ' ', $original_stat)) . ": " . $player->{$original_stat} . " -> " . ($player->{$original_stat} - $transfer_amount) . "<br />" .
				ucwords(str_replace('_', ' ', $target_stat)) . ": " . $player->{$target_stat} . " -> " . ($player->{$target_stat} + $transfer_amount) .
				"<br />
				Cost: $cost AK<br />
				Time: $time minutes<br />
				<form action='$self_link&view=character_changes' method='post'>
				<input type='hidden' name='original_stat' value='$original_stat' />
				<input type='hidden' name='target_stat' value='$target_stat' />
				<input type='hidden' name='transfer_amount' value='$transfer_amount' />
				<input type='hidden' name='confirm' value='1' />
				<input type='submit' name='stat_allocate' value='Confirm transfer' />
				</form>
				</td></tr></table>";

				throw new Exception('');
			}


			$player->premium_credits -= $cost;

			$exp = $transfer_amount * 10;
			$player->exp -= $exp;
			$player->{$original_stat} -= $transfer_amount;

			$player->train_type = $target_stat;
			$player->train_gain = $transfer_amount;
			$player->train_time = time() + ($time * 60);

			if($is_free_stat_change) {
				$player->last_free_stat_change = time();
			}

			$player->updateData();

			echo "<table class='table'><tr><th>Stat Transfer Started</th></tr>
			<tr><td style='text-align:center;'>You have started the transfer of your " . ucwords(str_replace('_', ' ', $original_stat)) . " to " .
				ucwords(str_replace('_', ' ', $target_stat)) . ".<br />
				<b><u>IMPORTANT</u></b>:<br />
				Do not cancel the training that was just started or you will not receive the transferred stats, and staff will not refund you for the
				transfer cost!
				</td></tr></table>";
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
	else if(isset($_POST['purchase_bloodline'])) {
		$bloodline_id = $system->clean($_POST['bloodline_id']);
		try {
			$result = $system->query("SELECT `bloodline_id`, `name`, `clan_id`, `rank` FROM `bloodlines`
				WHERE `bloodline_id`='$bloodline_id' AND `rank` < 5 ORDER BY `rank` ASC");
			if($system->db_num_rows == 0) {
				throw new Exception("Invalid bloodline!");
			}
			$result = $system->db_fetch($result);

			if($player->bloodline_id == $bloodline_id) {
				throw new Exception("You already have this bloodline!");
			}

			if($player->premium_credits < $costs['bloodline'][$result['rank']]) {
				throw new Exception("You do not have enough Ancient Kunai!");
			}

			if($player->clan['leader'] == $player->user_id) {
				$system->query("UPDATE `clans` SET `leader` = '0' WHERE `clan_id` = '{$player->clan['id']}'");
			}
			else if($player->clan['elder_1'] == $player->user_id) {
				$system->query("UPDATE `clans` SET `elder_1` = '0' WHERE `clan_id` = '{$player->clan['id']}'");
			}
			else if($player->clan['elder_2'] == $player->user_id) {
				$system->query("UPDATE `clans` SET `elder_2` = '0' WHERE `clan_id` = '{$player->clan['id']}'");
			}

			if($player->clan_office) {
				$player->clan_office = 0;
			}

			$player->premium_credits -= $costs['bloodline'][$result['rank']];

			// Give bloodline
			$clan_id = $result['clan_id'];
			$bloodline_name = $result['name'];

			require("adminPanel.php");
			$status = giveBloodline($bloodline_id, $player->user_id, false);

			$message = "You now have the bloodline <b>$bloodline_name</b>.";

			// Set clan
			$result = $system->query("SELECT `name` FROM `clans` WHERE `clan_id` = '$clan_id' LIMIT 1");
			if($system->db_num_rows > 0) {
				$clan_result = $system->db_fetch($result);


				$player->clan = array();
				$player->clan['id'] = $clan_id;
				$message .= " With your new bloodline you have been kicked out of your previous clan, and have been accepted by
				the " . $clan_result['name'] . " Clan.";
			}

			echo "<table class='table'><tr><th>New Bloodline!</th></tr>
			<tr><td style='text-align:center;'>$message</td></tr></table>";
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
	}
	else if(isset($_POST['forbidden_seal'])) {
		$seal_level = (int)$system->clean($_POST['seal_level']);
		$seal_length = (int)$system->clean($_POST['seal_length']);
		try {
			// Check seal level
			switch($seal_level) {
				case 1:
				case 2:
					break;
				default:
					throw new Exception("Invalid seal!");
					break;
			}

			// Check seal length
			switch($seal_length) {
				case 30:
				case 60:
				case 90:
					break;
				default:
					throw new Exception("Invalid length!");
					break;
			}

			// Check cost
			$cost = $costs['forbidden_seal'][$seal_level] * ($seal_length / 30);
			if($player->premium_credits < $cost) {
				throw new Exception("You do not have enough Ancient Kunai! ($cost needed)");
			}
			$player->premium_credits -= $cost;

			// Extend
			if($player->forbidden_seal && $player->forbidden_seal['level'] == $seal_level) {
				$player->forbidden_seal['time'] += $seal_length * 86400;
				$system->message("Seal extended!");
			}
			// Purchase new
			else {
				$forbidden_seal = array(
					'level' => $seal_level,
					'time' => time() + ($seal_length * 86400),
					'color' => 'blue'
				);
				$player->forbidden_seal = $forbidden_seal;
				$system->message("Seal infused!");
			}
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
	else if(isset($_POST['change_color']) && ($player->forbidden_seal || $player->premium_credits_purchased)) {
		$color = $system->clean($_POST['name_color']);
		switch($color) {
			case 'blue':
			case 'pink':
			case 'black':
				$player->forbidden_seal['color'] = $color;
				$system->message("Color changed!");
				break;
			case 'gold':
				if(!$player->premium_credits_purchased) {
					$system->message("Invalid color!");
					break;
				}
				$player->forbidden_seal['color'] = $color;
				$system->message("Color changed!");
				break;
			 case 'green':
			/* Shadekun edit for returning administrator color */
                if($player->staff_level < System::SC_MODERATOR) {
                    $system->message("Invalid color!");
                    break;
                }
                $player->forbidden_seal['color'] = $color;
                $system->message("Color changed");
                break;
			case 'teal':
				if ($player->staff_level < System::SC_HEAD_MODERATOR) {
					$system->message("Invalid color!");
					break;
				}
				$player->forbidden_seal['color'] = $color;
				$system->message("Color changed");
				break;
			case 'red':
				if($player->staff_level < System::SC_ADMINISTRATOR) {
					$system->message("Invalid color!");
					break;
				}
				$player->forbidden_seal['color'] = $color;
				$system->message("Color changed");
			break;
			/* End Shadekun edit */
			default:
				$system->message("Invalid color!");
		}
		$system->printMessage();
	}
	else if(isset($_POST['change_village']) && $player->rank >= 2) {
		$village = $_POST['new_village'];
		try {
			if($village == $player->village) {
				throw new Exception("Invalid village!");
			}

			switch($village) {
				case 'Stone':
				case 'Cloud':
				case 'Leaf':
				case 'Sand':
				case 'Mist':
					break;
				default:
					throw new Exception("Invalid village!");
					break;
			}

			if($player->team) {
				$debug = ($player->layout == 'classic_blue') ? "<br /><br />" : "";
				throw new Exception($debug . "You must leave your team first!");
			}

			if($player->premium_credits < $costs['village_change']) {
				throw new Exception("You do not have enough Ancient Kunai!");
			}

			if(!isset($_POST['confirm'])) {
				echo "<table class='table'><tr><th>Confirm Village Change</th></tr>
				<tr><td style='text-align:center;'>
				Are you sure you want to move from the $player->village village to the $village village? You will be kicked out of your clan
				and placed in a random clan in the new village.<br />
				<br />
				<b>(IMPORTANT: This is non-reversable once completed, if you want to return to your original village you will have to pay
				a higher transfer fee)</b><br />
				<form action='$self_link' method='post'>
				<input type='hidden' name='new_village' value='$village' />
				<input type='hidden' name='confirm' value='1' />
				<input type='submit' name='change_village' value='Change Village' />
				</form>
				</td></tr></table>";
				return true;
			}

			if($player->clan['leader'] == $player->user_id) {
				$system->query("UPDATE `clans` SET `leader` = '0' WHERE `clan_id` = '{$player->clan['id']}'");
			}
			else if($player->clan['elder_1'] == $player->user_id) {
				$system->query("UPDATE `clans` SET `elder_1` = '0' WHERE `clan_id` = '{$player->clan['id']}'");
			}
			else if($player->clan['elder_2'] == $player->user_id) {
				$system->query("UPDATE `clans` SET `elder_2` = '0' WHERE `clan_id` = '{$player->clan['id']}'");
			}

			if($player->clan_office) {
				$player->clan_office = 0;
			}

			// Cost
			$player->premium_credits -= ($costs['village_change']);
			$player->village_changes++;

			// Village
			$player->village = $village;

			// Location
			$result = $system->query("SELECT `location` FROM `villages` WHERE `name`='$player->village' LIMIT 1");
			$location = $system->db_fetch($result)['location'];
			$player->location = $location;

			// Clan
			$result = $system->query("SELECT `clan_id`, `name` FROM `clans`
					WHERE `village`='$player->village' AND `bloodline_only`='0'");
			if($system->db_num_rows == 0) {
				$result = $system->query("SELECT `clan_id`, `name` FROM `clans`
				WHERE `bloodline_only`='0'");
			}

			if(! $system->db_num_rows) {
				throw new Exception("No clans available!");
			}

			$clans = array();
			$count = 0;
			while($row = $system->db_fetch($result)) {
				$clans[$row['clan_id']] = $row;
				$count++;
			}

			$query = "SELECT ";
			$x = 0;
			foreach($clans as $id => $clan) {
				$query .= "SUM(IF(`clan_id` = $id, 1, 0)) as `$id`";
				$x++;
				if($x < $count) {
					$query .= ', ';
				}
			}
			$query .= " FROM `users`";

			$clan_counts = array();
			$result = $system->query($query);
			$row = $system->db_fetch($result);
			$total_users = 0;
			foreach($row as $id => $user_count) {
				$clan_counts[$id] = $user_count;
				$total_users += $user_count;
			}

			$average_users = round($total_users / $count);

			$clan_rolls = array();
			foreach($clans as $id => $clan) {
				$entries = 4;
				if($clan_counts[$id] > $average_users) {
					$entries--;
					if($clan_counts[$id] / 3 > $average_users) {
						$entries--;
					}


				}
				for($i = 0; $i < $entries; $i++) {
					$clan_rolls[] = $id;
				}

				$clan_id = $clan_rolls[mt_rand(0, count($clan_rolls) - 1)];


				$player->clan = array();
				$player->clan['id'] = $clan_id;
				$clan_name = $clans[$clan_id]['name'];
			}

			$system->message("You have moved to the $village village, and been placed in the $clan_name clan.");
			$location = explode('.', $player->location);
			$player->x = $location[0];
			$player->y = $location[1];

		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
	//Clan Change
	else if(isset($_POST['change_clan']) && $player->rank >= 2) {
		$new_clan_id = abs((int) $_POST['clan_change_id']);
		try {

			$clan_exists = in_array($new_clan_id, array_keys($available_clans));

			if( ($new_clan_id == $player->clan['id']) || !$clan_exists) {
				throw new Exception("Invalid clan!");
			}

			if($player->premium_credits < $costs['clan_change']) {
				throw new Exception("You do not have enough Ancient Kunai!");
			}

			$clan_name = $available_clans[$new_clan_id];

			if(!isset($_POST['confirm'])) {
				echo "
					<table class='table'><tr><th>Confirm Clan Change</th></tr>
						<tr>
							<td style='text-align:center;'>
								Are you sure you want to move from the {$player->clan['name']} clan to the $clan_name clan?<br />
								<br />
								<b>(IMPORTANT: This is non-reversable once completed, if you want to return to your original clan you will have to pay a higher transfer fee)</b><br />
								<form action='$self_link' method='post'>
									<input type='hidden' name='clan_change_id' value='$new_clan_id' />
									<input type='hidden' name='confirm' value='1' />
									<input type='submit' name='change_clan' value='Change Clan' />
								</form>
							</td>
						</tr>
					</table>";
				return true;
			}

			// Cost
			$player->premium_credits -= ($costs['clan_change']);
			$player->clan_changes++;

			// Village
			if($player->clan['leader'] == $player->user_id) {
				$system->query("UPDATE `clans` SET `leader` = '0' WHERE `clan_id` = '{$player->clan['id']}'");
			}
			else if($player->clan['elder_1'] == $player->user_id) {
				$system->query("UPDATE `clans` SET `elder_1` = '0' WHERE `clan_id` = '{$player->clan['id']}'");
			}
			else if($player->clan['elder_2'] == $player->user_id) {
				$system->query("UPDATE `clans` SET `elder_2` = '0' WHERE `clan_id` = '{$player->clan['id']}'");
			}

			$player->clan['id'] = $new_clan_id;
			$player->clan_office = 0;

			$system->message("You have moved to the $clan_name clan.");

		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
	// End Clan Change


	// Sub-menu
	echo "<div class='submenu'>
	<ul class='submenu'>
		<li style='width:30.5%;'><a href='{$self_link}&view=character_changes'>Character Changes</a></li>
		<li style='width:23%;'><a href='{$self_link}&view=bloodlines'>Bloodlines</a></li>
		<li style='width:25.5%;'><a href='{$self_link}&view=forbidden_seal'>Forbidden Seal</a></li>
		<li style='width:18.5%;'><a href='{$self_link}&view=buy_kunai'>Buy AK</a></li>
	</ul>
	</div>
	<div class='submenuMargin'></div>";

	$system->printMessage();
	// Summary
	echo "<table class='table'><tr><th>Premium</th></tr>
	<tr><td style='text-align:center;'>
	Here you can purchase and spend Ancient Kunai on a variety of boosts and in-game items.<br />
	<br />
	<b>Your Ancient Kunai:</b> $player->premium_credits
	</td></tr></table>";

	$view = 'character_changes';
	if($player->premium_credits == 0) {
		$view = 'buy_kunai';
	}
	if(isset($_GET['view'])) {
		$view = $_GET['view'];
	}

	$kunai_per_dollar = System::KUNAI_PER_DOLLAR;

	if($view == 'character_changes') {
		// Character reset
		echo "<table class='table'><tr><th>Character Reset</th></tr>
		<tr><td style='text-align:center;'>You can reset your character and start over as a level 1 Akademi-sei. This costs
		" . $costs['user_reset'] . " Ancient Kunai.<br />
		<form action='$self_link' method='post'>
		<input type='submit' name='user_reset' value='Reset' />
		</form>
		</td></tr>";

		echo "<tr><th>Username Change</th></tr>
		<tr><td style='text-align:center;'>You can change your username free once per account or for ". $costs['name_change'] . "AK afterward.
		Any changes to the case of your name do not cost.<br />
		<p>Free Changes left: {$player->username_changes}</p>
		<form action='$self_link' method='post'>
		<input type='text' name='new_name'/>
		<input type='submit' name='name_change' value='Change' />
		</form>
		</td></tr>";

		$stats = array('ninjutsu_skill', 'taijutsu_skill', 'genjutsu_skill', 'cast_speed', 'speed', 'intelligence', 'willpower');
		if($player->bloodline_id) {
			array_unshift($stats, 'bloodline_skill');
		}

		$village_change_cost = $costs['village_change'];
		$clan_change_cost = $costs['clan_change'];

		$costs = array();

		echo "<tr><th>Individual Stat Resets</th></tr>
		<tr><td style='text-align:center;'>
		<script type='text/javascript'>
		var costs = new Object;
		function statResetCostDisplay(costs) {
			var display = '';
			display = 'Cost: ' + costs[$('#statResetSelect').val()] + ' AK';
			$('#statResetCost').text(display);
		}
		</script>
		You can reset an individual stat, freeing up space in your total stat cap to train something else higher.<br />
		<form action='$self_link&view=character_changes' method='post'>
		<select id='statResetSelect' name='stat' onchange='statResetCostDisplay(costs);'>";
		foreach($stats as $stat) {
			$costs[$stat] = floor($player->{$stat} / 200);
			echo "<option value='$stat'>" . ucwords(str_replace('_', ' ', $stat)) . '</option>';
		}

		echo "</select>
		<script type='text/javascript'>";
		foreach($costs as $id => $cost) {
			echo "costs.{$id} = $cost;\r\n";
		}

		echo "</script>
		<br />
		<span id='statResetCost'>Cost: " . $costs[$stats[0]] . " AK</span><br />
		<input type='submit' name='stat_reset' value='Reset stat' />
		</form>
		</td></tr>";

		echo "</table>";

		// Stat rellocation
		echo "<table class='table'><tr><th>Stat Transfers</th></tr>
		<tr><td style='text-align:center;'>
		<script type='text/javascript'>
		var stats = new Object;
		function statSelectChange() {
			$('#transferAmount').val(stats[$('#statAllocateSelect').val()]);
			statAllocateCostDisplay();
		}
		function statAllocateCostDisplay() {
			var transferAmount = parseInt($('#transferAmount').val());
			if (transferAmount <= 10) {
				var cost = 0;
			} else {
					var cost = 1 + Math.floor(transferAmount / 300);
				}
			var time = transferAmount * 0.2;
			var display = cost + ' AK / ' + time + ' minutes';
			$('#statAllocateCost').html(display);
		}
		</script>
		You can transfer points from one stat to another. This costs Ancient Kunai and takes time to complete, both cost and time increase
		the higher your stat amount is.<br />
		Stat transfers under 10 are free but have a <b>24 hour cool down</b>.<br />
		<form action='$self_link&view=character_changes' method='post'>
		<br />
		Transfer<br />
		<select id='statAllocateSelect' name='original_stat' onchange='statSelectChange();'>";
		foreach($stats as $stat) {
			echo "<option value='$stat'>" . ucwords(str_replace('_', ' ', $stat)) . '</option>';
		}

		echo "</select><br />
		to<br />
		<select name='target_stat'>";
		foreach($stats as $stat) {
			echo "<option value='$stat'>" . ucwords(str_replace('_', ' ', $stat)) . '</option>';
		}
		echo "</select>
		<script type='text/javascript'>";
		foreach($stats as $stat) {
			if(strpos($stat, 'skill') !== false) {
				echo "stats.$stat = " . ($player->{$stat} - 10) . ";\r\n";
			}
			else {
				echo "stats.$stat = " . ($player->{$stat} - 5) . ";\r\n";
			}
		}

		if($player->bloodline_id) {
			$init_cost = (1 + floor(($player->bloodline_skill - 10) / 300));
			$init_transfer_amount = $player->bloodline_skill - 10;
			$init_length = ($player->bloodline_skill - 10) * 0.25;
		} else {
			$init_cost = (1 + floor(($player->ninjutsu_skill  - 10) / 300));
			$init_transfer_amount = $player->ninjutsu_skill - 10;
			$init_length = ($player->ninjutsu_skill - 10) * 0.25;
		 }

		echo "</script>
			<br />
			<br />
			Transfer amount:<br />
			<input type='text' id='transferAmount' name='transfer_amount' value='{$init_transfer_amount}'
				onkeyup='statAllocateCostDisplay()' /><br />
			<span id='statAllocateCost'>" . $init_cost . " AK / {$init_length} minutes</span><br />
			<input type='submit' name='stat_allocate' value='Transfer Stat Points' />
			</form>
			</td></tr></table>";

		// Village change

		if($player->rank >= 2) {

			echo "
				<form method='POST'>
					<table class='table'>
						<tr>
							<th>Change Clan</th>
						</tr>
						<tr>
							<td style='text-align:center;'>If you choose to abandon your clan now, you must gain the respect of other leaders in order to be accepted into their family. A gift offering of {$clan_change_cost} Ancient Kunai is required.
							<br />
							<br />
							<b>(IMPORTANT: This is non-reversable once completed, if you want to return to your original village you will have to pay a higher transfer fee. Furthermore, you'll be removed from any clan office.)</b>
							<br />Select the clan below:</td>
						</tr>
						<tr>
							<td style='text-align:center;'>
								<select name='clan_change_id'>
			";
								foreach($available_clans as $clan_id => $clan_name) {
									echo sprintf("<option value='%d'>%s</option>", $clan_id, $clan_name);
								}
			echo "
								</select>
							</td>
						</tr>
						<tr>
							<td style='text-align:center;'><input type='submit' name='change_clan' value='Change'></td>
						</tr>
					</table>
				</form>
			";


		}

		if($player->rank >= 2) {
			$villages = array('Stone', 'Cloud', 'Leaf', 'Sand', 'Mist');
			echo "<table class='table'><tr><th>Change Village</th></tr>
			<tr><td style='text-align:center;'>
			You can betray your own village and go to another village if you no longer wish to be a ninja in your own village.
			However to get the other village to accept you, you must offer them " . ($village_change_cost) .
			" Ancient Kunai.<br />
			<br />
			<b>(IMPORTANT: This is non-reversable once completed, if you want to return to your original village you will have to pay
			a higher transfer fee)</b><br />
			<form action='$self_link' method='post'>
			<select name='new_village'>";
			foreach($villages as $village) {
				if($player->village == $village) {
					continue;
				}
				echo "<option value='$village'>$village</option>";
			}

			echo "</select><br />
			<input type='submit' name='change_village' value='Change Village' />
			</form>
			</td></tr></table>";
		}
	}
	else if($view == 'bloodlines') {
		// Bloodline
		if($player->rank >= 2) {
			echo "<table class='table'><tr><th>Purchase New Bloodline</th></tr>
			<tr><td style='text-align:center;'>A researcher from the village will implant another clan's DNA into
			you in exchange for Ancient Kunai, allowing you to use a new bloodline" .
				($player->bloodline_id ? ' instead of your own' : '') . ".<br /><br />";
			if($player->bloodline_skill > 10) {
			    echo "<b>Warning: Your bloodline skill will be reduced by " . (Bloodline::SKILL_REDUCTION_ON_CHANGE * 100) . "% as you must
                   re-adjust to your new bloodline!</b><br />";
            }
			echo "<br />";

			$result = $system->query("SELECT `bloodline_id`, `name`, `rank`
				FROM `bloodlines` WHERE `rank` < 5 ORDER BY `rank` ASC");
			if($system->db_num_rows == 0) {
				echo "No bloodlines available!";
			}
			else {
				$bloodlines = array();
				while($row = $system->db_fetch($result)) {
					if($player->bloodline_id && $player->bloodline_id == $row['bloodline_id']) {
						continue;
					}
					$bloodlines[$row['rank']][$row['bloodline_id']] = $row;
				}

				$ranks = array(1 => 'Legendary', 2 => 'Elite', 3 => 'Common', 4 => 'Lesser');
				foreach($ranks as $id => $rank) {
					if(empty($bloodlines[$id])) {
						continue;
					}
					echo "$rank Bloodlines (" . $costs['bloodline'][$id] . " Ancient Kunai)<br />
					<form action='$self_link' method='post'>
					<select name='bloodline_id'>";
					foreach($bloodlines[$id] as $bloodline_id => $bloodline) {
						echo "<option value='$bloodline_id'>" . $bloodline['name'] . "</option>";
					}
					echo "</select>
					<input type='submit' name='purchase_bloodline' value='Implant' />
					</form>";
				}

			}


			echo "</td></tr></table>";
		}
		else {
			$system->message("You cannot access the Bloodline section until you are a Genin!");
			$system->printMessage();
		}
	}
	else if($view == 'forbidden_seal') {
		echo "<table class='table'><tr><th colspan='2'>Forbidden Seals</th></tr>
		<tr><td style='text-align:center;' colspan='2'>
		Shinobi researchers can imbue you with a forbidden seal, providing you with various benefits, in exchange for Ancient Kunai. The
		specific benefits and their strengths depend on which seal the researchers give you. The seals will recede after 30 days
		naturally, although with extra chakra imbued they can last longer.<br />
		<br />
		<b>Your Forbidden Seal</b><br />";
		if(isset($player->forbidden_seal['level'])) {
			$seals = array(1 => 'Twin Sparrow Seal', 2 => 'Four Dragon Seal');
			$time_remaining = $player->forbidden_seal['time'] - time();

			$days = floor($time_remaining / 86400);
			$time_remaining -= $days * 86400;

			$hours = floor($time_remaining / 3600);
			$time_remaining -= $hours * 3600;

			$minutes = ceil($time_remaining / 60);

			echo $seals[$player->forbidden_seal['level']] . "<br />";
			if($days) {
				echo "$days day(s), $hours hour(s), $minutes minute(s) remaining<br />";
			}
			else if($hours) {
				echo "$hours hour(s), $minutes minute(s) remaining<br />";
			}
			else {
				echo "$minutes minute(s) remaining<br />";
			}
			//Addition - Kengetsu - Paying Player Chat Color
			echo "<br />
			<form action='$self_link&view=forbidden_seal' method='post'>
			<input type='radio' name='name_color' value='blue' " .
			($player->forbidden_seal['color'] == 'blue' ? "checked='checked'" : '') . "/>
			<span class='blue' style='font-weight:bold;'>Blue</span>
			<input type='radio' name='name_color' value='pink' " .
			($player->forbidden_seal['color'] == 'pink' ? "checked='checked'" : '') . "/>
			<span class='pink' style='font-weight:bold;'>Pink</span>
			<input type='radio' name='name_color' value='black' " .
			($player->forbidden_seal['color'] == 'black' ? "checked='checked'" : '') . "/>
			<span style='font-weight:bold;'>Black</span>";
			if ($player->premium_credits_purchased) {
				echo "
				<input type='radio' name='name_color' value='gold' " .
				($player->forbidden_seal['color'] == 'gold' ? "checked='checked'" : '') . "/>
				<span class='gold' style='font-weight:bold;'>Gold</span>";
			}
			if ($player->staff_level >= System::SC_MODERATOR) {
				echo "
				<input type='radio' name='name_color' value='green' " .
				($player->forbidden_seal['color'] == 'green' ? "checked='checked'" : '') . "/>
				<span class='moderator' style='font-weight:bold;'>Green</span>";
			} else if ($player->staff_level >= System::SC_HEAD_MODERATOR) {
				echo "
				<input type='radio' name='name_color' value='teal' " .
				($player->forbidden_seal['color'] == 'teal' ? "checked='checked'" : '') . "/>
				<span class='headModerator' style='font-weight:bold;'>Teal</span>";
			} else if ($player->staff_level >= System::SC_ADMINISTRATOR) {
				echo "
				<input type='radio' name='name_color' value='red' " .
				($player->forbidden_seal['color'] == 'red' ? "checked='checked'" : '') . "/>
				<span class='administrator' style='font-weight:bold;'>Red</span>";
			}
			echo "
			<br />
			<input type='submit' name='change_color' value='Change Name Color' />
			</form>";
		}
		else if ($player->premium_credits_purchased) {
			echo "
			<form action='$self_link&view=forbidden_seal' method='post'>
			<input type='radio' name='name_color' value='black' " .
			($player->forbidden_seal['color'] == 'black' ? "checked='checked'" : '') . "/>
			<span style='font-weight:bold;'>Black</span>
			<input type='radio' name='name_color' value='gold' " .
			($player->forbidden_seal['color'] == 'gold' ? "checked='checked'" : '') . "/>
			<span class='gold' style='font-weight:bold;'>Gold</span>
			";
			if($player->staff_level >= System::SC_ADMINISTRATOR) {
				echo "
				<input type='radio' name='name_color' value='red' " .
				($player->forbidden_seal['color'] == 'red' ? "checked='checked'" : '') . "/>
				<span class='administrator' style='font-weight:bold;'>Red</span>";
			}
			echo "
			<br />
			<input type='submit' name='change_color' value='Change Name Color' />
			</form>";
		}
		// End
		else {
			echo "None";
		}
		echo "</td></tr>
		<tr>
			<th>Twin Sparrow Seal</th>
			<th>Four Dragon Seal</th>
		</tr>
		<tr>
			<td style='width:50%;vertical-align:top;'>
				<p style='font-weight:bold;text-align:center;'>
					{$costs['forbidden_seal'][1]} Ancient Kunai / 30 days</p>
				<br />
				+10% regen rate<br />
				Blue/Pink username color in chat<br />
				Larger avatar (125x125 -> 175x175)<br />
				Longer logout timer (60 -> 90 minutes)<br />
				Larger inbox (50 -> 75 messages)<br />
				Longer journal (1000 -> 2000 characters)<br />
				Larger journal images (300x200 -> 500x500)<br />
				Longer chat posts (350 -> 450 characters)<br />
				Longer PMs (1000 -> 1500 characters)<br />
				<form action='$self_link&view=forbidden_seal' method='post'>
				<p style='width:100%;text-align:center;margin:0px;margin-top:1em;'>
					<input type='hidden' name='seal_level' value='1' />
					<select name='seal_length'>
					<option value='30'>30 days (" . ($costs['forbidden_seal'][1] * 1) . " AK)</option>
					<option value='60'>60 days (" . ($costs['forbidden_seal'][1] * 2) . " AK)</option>
					<option value='90'>90 days (" . ($costs['forbidden_seal'][1] * 3) . " AK)</option>
					</select><br />
					<input type='submit' name='forbidden_seal' value='" .
						($player->forbidden_seal && $player->forbidden_seal['level'] == 1 ? 'Extend' : 'Purchase') . "' />
				</p>
				</form>
			</td>
			<td style='width:50%;vertical-align:top;'>
				<p style='font-weight:bold;text-align:center;'>
					{$costs['forbidden_seal'][2]} Ancient Kunai / 30 days</p>
				<br />
				All benefits of Twin Sparrow Seal<br />
				+20% regen rate<br />
				+1 jutsu equip slots<br />
				+1 weapon equip slots<br />
				+1 armor equip slots<br />
				Enhanced long trainings (1.5x length, 2x gains)<br />
				Enhanced extended trainings (1.5x length, 2x gains)<br />
				<form action='$self_link&view=forbidden_seal' method='post'>
				<p style='width:100%;text-align:center;margin:0px;margin-top:2.2em;'>
					<input type='hidden' name='seal_level' value='2' />
					<select name='seal_length'>
					<option value='30'>30 days (" . ($costs['forbidden_seal'][2] * 1) . " AK)</option>
					<option value='60'>60 days (" . ($costs['forbidden_seal'][2] * 2) . " AK)</option>
					<option value='90'>90 days (" . ($costs['forbidden_seal'][2] * 3) . " AK)</option>
					</select><br />
					<input type='submit' name='forbidden_seal' value='" .
						($player->forbidden_seal && $player->forbidden_seal['level'] == 2 ? 'Extend' : 'Purchase') . "' />
				</p>
				</form>
			</td>
		</tr></table>";
	}
	else if($view == 'buy_kunai') {
		// Buy kunai
		echo <<<HTML
		<table class='table'><tr><th>Buy Ancient Kunai</th></tr>
		<tr><td style='text-align:center;'>
			<p style='width:80%;margin:auto;'>All payments are securely processed through Paypal. You do not need a Paypal account to
			pay with a credit card.</p>
			<br />
			<b>$1 USD = {$kunai_per_dollar} Ancient Kunai</b><br />
			<i>(ignore price per unit on Paypal confirmation screen, the bonus is applied after)</i><br />
			<br />
			<b>Ancient Kunai Specials</b><br />
			$15 = 30 Kunai + 10 bonus<br />
			$25 = 50 Kunai + 20 bonus<br />
			$50 = 100 Kunai + 50 bonus<br />
			<br />
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_xclick">
			<input type="hidden" name="business" value="lsmjudoka05@yahoo.com">
			<input type="hidden" name="cancel_return" value="{$system->link}">
			<input type="hidden" name="return" value="{$system->link}">
			<input type="hidden" name="amount" value="1.00">
			<input type="hidden" name="undefined_quantity" value="1">
			<input type="hidden" name="cn" value="Ancient Kunai">
			<input type="hidden" name="no_note" value="1">
			<input type="hidden" name="no_shipping" value="1">
			<input type="hidden" name="currency_code" value="USD">
			<input type="hidden" name="item_name" value="Ancient Kunai - $player->user_name">
			<input type="hidden" name="custom" value="$player->user_name">
			<input type="hidden" name="notify_url" value="{$system->link}paypal_listener.php">
            <input type='submit' style='background:url(https://www.paypal.com/en_US/i/btn/btn_buynowCC_LG.gif);
					border:0;width:171px;height:47px;cursor:pointer;' value='' name='submit' alt='Buy kunai'>
			</form>
		</td></tr></table>
HTML;


		// Exchange
		premiumCreditExchange();
	}

}

function premiumCreditExchange() {
	global $system;

	global $player;

	global $self_link;
	$self_link .= '&view=buy_kunai';

	$price_min = 1.0;
	$price_max = 10.0;

	// Create offer
	if(isset($_POST['new_offer'])) {
		try {
			// Verify input
			if(isset($_POST['premium_credits'])) {
				$premium_credits = $system->clean($_POST['premium_credits']);
				if(!is_numeric($premium_credits)) {
					throw new Exception("Invalid kunai amount!");
				}
				$premium_credits = (int)$premium_credits;
			}
			else {
				throw new Exception("Invalid kunai amount!");
			}

			if(isset($_POST['money'])) {
				$money = $system->clean($_POST['money']);
				if(!is_numeric($money)) {
					throw new Exception("Invalid money amount!");
				}
				$money = round((float)$money, 1);
				if($money < $price_min || $money > $price_max) {
					throw new Exception("Invalid money amount!");
				}
			}
			else {
				throw new Exception("Invalid money amount!");
			}

			// Check offer is greater than 0
			if($premium_credits <= 0) {
				throw new Exception("Offer must be at least 1 kunai!");
			}

			// Check user has premium_credits
			if($player->premium_credits < $premium_credits) {
				throw new Exception("You do not have enough Ancient Kunai!");
			}


			// Subtract premium_credits from user count and submit offer.
			$player->premium_credits -= $premium_credits;
			$player->updateData();

			$money = $money * $premium_credits * 1000;

			$system->query("INSERT INTO `premium_credit_exchange` (`seller`, `premium_credits`, `money`)
			VALUES ('$player->user_id', '$premium_credits', '$money')");
			if($system->db_affected_rows > 0) {
				$system->message("Offer placed!");
			}
			else {
				$system->message("Error placing offer.");
			}
			$system->printMessage();
		} catch (Exception $e) {
			$system->message($e->getMessage());
			$system->printMessage();
		}
	}

	// Purchase offer
	else if(isset($_GET['purchase'])) {
		try {
			// Validate input for offer id
			$id = (int)$system->clean($_GET['purchase']);
			$result = $system->query("SELECT * FROM `premium_credit_exchange` WHERE `id`='$id' AND `completed`='0' LIMIT 1");
			if($system->db_num_rows == 0) {
				 throw new Exception("Invalid offer!");
			}

			$offer = $system->db_fetch($result);

			// Check user has enough money
			if($player->money < $offer['money']) {
				throw new Exception("You do not have enough money!");
			}

			// Run purchase and log [NOTE: Updating first is to avoid as much server lag and possibility for glitching]
			$system->query("UPDATE `premium_credit_exchange` SET `completed`='1' WHERE `id`='$id' LIMIT 1");

			$player->money -= $offer['money'];
			$player->premium_credits += $offer['premium_credits'];
			$player->updateData();

			$system->query("UPDATE `users` SET `money`=`money` + {$offer['money']}
				WHERE `user_id`='{$offer['seller']}'");
			$system->log("Kunai Exchange", "Completed Sale", "ID# {$offer['id']}; #{$offer['seller']} to #{$player->user_id} ($player->user_name) :: {$offer['premium_credits']} for &yen;{$offer['money']}");
			$system->send_pm('Ancient Kunai Exchange', $offer['seller'], 'Transaction Complete', $player->user_name . " has purchased {$offer['premium_credits']} Ancient Kunai for &yen;{$offer['money']}.");
			$system->message("Ancient Kunai purchased!");
			$system->printMessage();
		} catch(Exception $e) {
			$system->message($e->getMessage());
			$system->printMessage();
		}
	}

	// Cancel offer
	else if(isset($_GET['cancel'])) {
		try {
			// Validate input for offer id
			$id = (int)$system->clean($_GET['cancel']);
			$result = $system->query("SELECT * FROM `premium_credit_exchange` WHERE `id`='$id' AND `completed`='0' LIMIT 1");
			if($system->db_num_rows == 0) {
				 throw new Exception("Invalid offer!");
			}

			$offer = $system->db_fetch($result);

			// Check offer belongs to user
			if($player->user_id != $offer['seller']) {
				throw new Exception("Offer is not yours!");
			}

			// Cancel log [NOTE: Updating first is to avoid as much server lag and possibility for glitching]
			$system->query("UPDATE `premium_credit_exchange` SET `completed`='1' WHERE `id`='$id' LIMIT 1");

			$player->premium_credits += $offer['premium_credits'];
			$player->updateData();

			$system->log("Kunai Exchange", "Cancelled Offer", "ID# {$offer['id']}; {$offer['seller']} - Cancelled :: {$offer['premium_credits']} for &yen;{$offer['money']}=");

			$system->message("Offer cancelled!");
			$system->printMessage();
		} catch(Exception $e) {
			$system->message($e->getMessage());
			$system->printMessage();
		}
	}

	/* [DISPLAY] */

	// View offers
	echo "<table id='kunaiExchange' class='table' cellspacing='0' style='width:95%;'>
	<tr'><th colspan='4'><a href='$self_link'>Ancient Kunai Exchange</a></th></tr>
	<tr>
		<td colspan='4' style='text-align:center;'>
			<div style='width:200px;margin-left:auto;margin-right:auto;text-align:left;'>
			<b>Your money:</b> &yen;$player->money<br />
			<b>Your Ancient Kunai:</b> $player->premium_credits
			</div>
			<br />
			<a href='#createoffer'>Create offer</a>
		</td>
	</tr>
	<tr>
		<th style='width:30%;border-radius:0px;moz-border-radius:0px;-webkit-border-radius:0px;'>Seller</th>
		<th style='width:30%;border-radius:0px;moz-border-radius:0px;-webkit-border-radius:0px;'>Ancient Kunai</th>
		<th style='width:30%;border-radius:0px;moz-border-radius:0px;-webkit-border-radius:0px;'>Money</th>
		<th style='width:10%;border-radius:0px;moz-border-radius:0px;-webkit-border-radius:0px;'>&nbsp;</th>
	</tr>";

	$query = "SELECT * FROM `premium_credit_exchange` WHERE `completed`='0' ORDER BY `id` DESC";
	$result = $system->query($query);

	$credit_users = array();

	//If there are offers in the database
	if($system->db_num_rows) {

		while($row = $system->db_fetch($result)) {

			if(! in_array($credit_users[$row['seller']], $credit_users))
			{
				$query = $system->query("SELECT `user_name` FROM `users` WHERE `user_id`='{$row['seller']}'");
				$user_info = $system->db_fetch();
				$credit_users[$row['seller']] = $user_info['user_name'];
			}

			$sellerName = $credit_users[$row['seller']];

			echo "<tr  class='fourColGrid' >
				<td style='text-align:center;'><a href='{$system->links['members']}&user={$sellerName}'>{$sellerName}</a></td>
				<td style='text-align:center;'>{$row['premium_credits']} AK</td>
				<td style='text-align:center;'>&yen;{$row['money']}</td>";
			if($player->user_id == $row['seller']) {
				echo "<td style='text-align:center;'><a href='$self_link&cancel={$row['id']}'>Cancel</a></td>";
			}
			else {
				echo "<td style='text-align:center;'><a href='$self_link&purchase={$row['id']}'>Purchase</a></td>";
			}
			echo "</tr>";
		}
	}
	else {	// Display no offers message
		echo "<tr><td colspan='4' style='text-align:center;'>No offers!</td></tr>";
	}

	echo "</table>";


	// Show form for create offer
	echo "<a name='createoffer'></a>
		<table class='table'>
		<tr><th>Create Offer</th></tr>
		<tr><td style='text-align:center;'>
		<script type='text/javascript'>
		function calcPreview() {
			var premium_credits = parseInt($('#premium_credits').val());
			var money = parseFloat($('#money option:selected').val());
			var result = premium_credits * (money * 1000);
			if(isNaN(result)) {
				return false;
			}
			else {
				$('#offerPreview').html('<b>' + premium_credits + '</b> Ancient Kunai for &yen;<b>' + result + '</b>');
				return true;
			}
		}
		</script>
		<form action='$self_link' method='post'>
		<div style='width:350px;margin-left:auto;margin-right:auto;text-align:left;'>
		<span style='display:inline-block;width:120px;'>Ancient Kunai to sell:</span>
			<input type='text' name='premium_credits' id='premium_credits' style='width:80px;margin-left:2px;' onKeyUp='calcPreview();' /><br />
		<span style='display:inline-block;width:120px;'>Money per kunai: </span>
		<select onchange='calcPreview();' name='money' id='money'>&yen;";

		for($i = $price_min; $i < $price_max; $i += 0.1) {
			echo "<option value='" . sprintf("%.1f", $i) . "'>" . sprintf("%.1f", $i) . "</option>";
		}

	echo "</select> x 1000 per kunai
		</div>
		<span id='offerPreview'>&nbsp;</span><br />
		<input type='submit' name='new_offer' value='Submit' />
		</form>
		</td></tr></table>";

}
