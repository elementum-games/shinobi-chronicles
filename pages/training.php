<?php
/*
File: 		training.php
Coder:		Levi Meahan
Created:	11/02/2013
Revised:	03/06/2014 by Levi Meahan
Purpose:	Function for allowing user to train their stats and jutsu
Algorithm:	See master_plan.html
*/
function training() {
	global $system;
	global $player;
	global $self_link;

	$stat_train_length = 600;
	$stat_train_gain = 4 + ($player->rank_num * 4);

	$jutsu_train_gain = User::$jutsu_train_gain;

	// 56.25% of standard
	$stat_long_train_length = $stat_train_length * 4;
	$stat_long_train_gain = $stat_train_gain * 2.25;

    // 30x length (5 hrs), 12x gains: 40% of standard
    $stat_extended_train_length = $stat_train_length * 30;
	$stat_extended_train_gain = $stat_train_gain * 12;

	// Forbidden seal trainings boost
    $stat_long_train_length *= $player->forbidden_seal->long_training_time;
    $stat_long_train_gain *= $player->forbidden_seal->long_training_gains;

    $stat_extended_train_length = round($stat_extended_train_length * $player->forbidden_seal->extended_training_time);
    $stat_extended_train_gain = round($stat_extended_train_gain * $player->forbidden_seal->extended_training_gains);


	$stat_train_gain += $system->TRAIN_BOOST;
	$stat_long_train_gain += $system->LONG_TRAIN_BOOST;
	$stat_extended_train_gain += ($system->LONG_TRAIN_BOOST * 5);

	$player->getInventory();
	if(!empty($_POST['train_type']) && !$player->train_time) {
		try {

            // check if pvp is active at the current location
            if ($player->rank_num > 2 && $player->current_location->location_id && !$player->current_location->pvp_allowed) {
                throw new Exception("You cannot train at this location!");
            }

			$train_length = $stat_train_length;
			$train_gain = $stat_train_gain;
			if($_POST['train_type'] == 'Long') {
				$train_length = $stat_long_train_length;
				$train_gain = $stat_long_train_gain;
			}
			else if($_POST['train_type'] == 'Extended') {
				$train_length = $stat_extended_train_length;
				$train_gain = $stat_extended_train_gain;
			}

			if(!empty($_POST['skill'])) {
				if($player->total_stats >= $player->rank->stat_cap) {
					throw new Exception("You cannot train any more at this rank!");
				}
				switch($_POST['skill']) {
					case 'ninjutsu':
					case 'taijutsu':
					case 'genjutsu':
						break;
					case 'bloodline':
						if(!$player->bloodline_id) {
							throw new Exception("Invalid skill type!");
						}
						break;
					default:
						throw new Exception("Invalid skill type!");
				}
				$train_type = $_POST['skill'] . '_skill';
			}
			else if(!empty($_POST['attributes'])) {
				if($player->total_stats >= $player->rank->stat_cap) {
					throw new Exception("You cannot train any more at this rank!");
				}
				switch($_POST['attributes']) {
					case 'cast_speed':
					case 'speed':
					case 'intelligence':
					case 'willpower':
						break;
					default:
						throw new Exception("Invalid attributes!");
				}
				$train_type = $_POST['attributes'];
			}
			else if(!empty($_POST['jutsu'])) {
				$jutsu_id = (int)$_POST['jutsu'];
				if(!$player->hasJutsu($jutsu_id)) {
					throw new Exception("Invalid jutsu!");
				}
				if($player->jutsu[$jutsu_id]->level >= 100) {
					throw new Exception("You cannot train this jutsu any further!");
				}
				$train_type = 'jutsu:' . System::slug($player->jutsu[$jutsu_id]->name);
				$train_type = $system->clean($train_type);
				$train_gain = $jutsu_id;
				$train_length = 600 + (60 * round(pow($player->jutsu[$jutsu_id]->level, 1.1)));
			}
			else {
				throw new Exception("Invalid training type!");
			}

            // Check for clan training boost
            if($player->clan && $player->clan->boost_type == 'training') {
			 	if($train_type == $player->clan->boost_effect
                     ||
                     (str_contains($train_type, 'jutsu') && $player->clan->boost_effect == 'jutsu')
                ) {
			 		$system->message("Your training was reduced by "
                        . ($train_length * ($player->clan->boost_amount / 100))
                        . " seconds due to your clan boost.");
			 		$train_length *= 1 - ($player->clan->boost_amount / 100);
			 	}
			}

			// Check for sensei training boost
			if($player->sensei_id != 0) {
				$sensei_boost = SenseiManager::getStudentBoostBySensei($player->sensei_id, $system);
				if ($train_type == $sensei_boost['specialization'] . "_skill") {
                    $system->message("Your training was reduced by " . ($train_length * ($sensei_boost['boost_primary'] / 100)) . " seconds due to your student boost.");
					$train_length *= 1 - ($sensei_boost['boost_primary'] / 100);
                }
				else if ($sensei_boost['boost_secondary'] > 0) {
					$system->message("Your training was reduced by " . ($train_length * ($sensei_boost['boost_secondary'] / 100)) . " seconds due to your student boost.");
					$train_length *= 1 - ($sensei_boost['boost_secondary'] / 100);
                }
			}

			$player->log(User::LOG_TRAINING, "Type: {$train_type} / Length: {$train_length}");

			$player->train_type = $train_type;
			$player->train_gain = $train_gain;
			$player->train_time = time() + $train_length;
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}
		$system->printMessage();
	}
	else if(!empty($_GET['cancel_training']) && $player->train_time) {
		$player->train_time = 0;
		$system->message("Training cancelled.");
		$system->printMessage();
	}

	// Add rank stuff
	echo "<table class='table'><tr><th colspan='3'>Academy</th></tr>
		<tr><td colspan='3'>
		<p style='text-align:center;'>Here at the academy, you can take classes to improve your skills, attributes, or skill with a
		jutsu.</p>
		<br />
		<span style='font-weight:bold;'>Skill/Attribute training:</span><br />
			<p style='margin-left:20px;margin-top:5px;margin-bottom:8px;'>
			<label style='font-weight:bold;width:70px;'>Short:</label>
				Takes " . ($stat_train_length / 60) . " minutes, gives $stat_train_gain point" . ($stat_train_gain > 1 ? 's' : '') . "<br />
			<label style='font-weight:bold;width:70px;'>Long:</label>
				Takes " . ($stat_long_train_length / 60) . " minutes, gives $stat_long_train_gain point" . ($stat_long_train_gain > 1 ? 's' : '') . "<br />
            <label style='font-weight:bold;width:70px;'>Extended:</label>
				Takes " . ($stat_extended_train_length / 60) . " minutes, gives $stat_extended_train_gain point" . ($stat_extended_train_gain > 1 ? 's' : '') . "<br />
			</p>
		<span style='font-weight:bold;'>Jutsu training:</span><br />
			<p style='margin-left:20px;margin-top:5px;margin-bottom:8px;'>
			Takes 10 minutes or more depending on the jutsu level, gives {$jutsu_train_gain} level" . ($jutsu_train_gain > 1 ? 's' : '') . ".</p>
		</td></tr>";
	if($player->train_time) {
		echo "<tr><th colspan='3'>Currently Training</th></tr>
		<tr><td colspan='3' style='text-align:center'>";
		if(str_contains($player->train_type, 'jutsu:')) {
			$train_type = str_replace('jutsu:', '', $player->train_type);
			echo "Currently training: " . ucwords(str_replace('_', ' ', $train_type)) . "<br />" .
			System::timeRemaining($player->train_time - time(), 'short', false, true) . " remaining";
		}
		else  {
			echo "Currently training: " . ucwords(str_replace('_', ' ', $player->train_type)) . "<br />" .
			System::timeRemaining($player->train_time - time(), 'short', false, true) . " remaining";
		}
		echo "<br />
		<br /><a href='$self_link&cancel_training=1'>Cancel Training</a>";
		echo "</td></tr></table>";
	}
	else {
		echo "
			<tr>
				<th style='width:33%;'>Skills</th>
				<th style='width:32%;'>Attributes</th>
				<th style='width:33%;'>Jutsu</th>
			</tr>
			<tr>
				<td style='text-align:center;'>
				<form action='$self_link' method='post'>
						<select name='skill'>
							<option value='ninjutsu' " . ($player->train_type == 'ninjutsu_skill' ? "selected='selected'" : "") .
								">Ninjutsu Skill</option>
							<option value='taijutsu'" . ($player->train_type == 'taijutsu_skill' ? "selected='selected'" : "") .
								">Taijutsu Skill</option>
							<option value='genjutsu'" . ($player->train_type == 'genjutsu_skill' ? "selected='selected'" : "") .
								">Genjutsu Skill</option>";
							if($player->bloodline_id) {
								echo "<option value='bloodline'" . ($player->train_type == 'bloodline_skill' ? "selected='selected'" : "") .
									">Bloodline Skill</option>";
							}
						echo "</select><br />
						<input type='submit' name='train_type' value='Short' />
						<input type='submit' name='train_type' value='Long' />
						<input type='submit' name='train_type' value='Extended' />
                </form>
				</td>
				<td style='text-align:center;'>
					<form action='$self_link' method='post'>
						<select name='attributes'>
							<option value='cast_speed'" . ($player->train_type == 'cast_speed' ? "selected='selected'" : "") .
								">Cast speed</option>
							<option value='speed'" . ($player->train_type == 'speed' ? "selected='selected'" : "") .
								">Speed</option>
						</select><br />
						<input type='submit' name='train_type' value='Short' />
						<input type='submit' name='train_type' value='Long' />
						<input type='submit' name='train_type' value='Extended' />
                    </form>
				</td>
				<td style='text-align:center;'>
					<form action='$self_link' method='post'>
						<select name='jutsu'>";
						foreach($player->jutsu as $id => $jutsu) {
							if($jutsu->level >= 100) {
								continue;
							}
							echo "<option value='$id' title='{$jutsu->jutsu_type}'>" . $jutsu->name . "</option>";
						}
						echo "</select><br />
						<input type='submit' name='train_type' value='Train' />
					</form>
				</td>
			</tr>
		</table>";
	}
}
