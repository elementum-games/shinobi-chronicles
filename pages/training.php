<?php
/*
File: 		training.php
Coder:		Levi Meahan
Created:	11/02/2013
Revised:	03/06/2014 by Levi Meahan
Purpose:	Function for allowing user to train their stats and jutsu
Algorithm:	See master_plan.html
*/

require_once __DIR__ . '/../classes/notification/NotificationManager.php';

function training() {
	global $system;
	global $player;
	global $self_link;

    $trainingManager = $player->trainingManager;

	$player->getInventory();
	if(!empty($_POST['train_type']) && !$player->train_time) {
		try {

            // check if pvp is active at the current location
            if ($player->rank_num > 2 && $player->current_location->location_id && !$player->current_location->pvp_allowed) {
                throw new RuntimeException("You cannot train at this location!");
            }

			// track if notification already created
            $notification_created = false;

			$train_length = $trainingManager->stat_train_length;
			$train_gain = $trainingManager->stat_train_gain;
			if($_POST['train_type'] == 'Long') {
				$train_length = $trainingManager->stat_long_train_length;
				$train_gain = $trainingManager->stat_long_train_gain;
			}
			else if($_POST['train_type'] == 'Extended') {
				$train_length = $trainingManager->stat_extended_train_length;
				$train_gain = $trainingManager->stat_extended_train_gain;
			}

			if(!empty($_POST['skill'])) {
				if($player->total_stats >= $player->rank->stat_cap) {
					throw new RuntimeException("You cannot train any more at this rank!");
				}
				switch($_POST['skill']) {
					case 'ninjutsu':
					case 'taijutsu':
					case 'genjutsu':
						break;
					case 'bloodline':
						if(!$player->bloodline_id) {
							throw new RuntimeException("Invalid skill type!");
						}
						break;
					default:
						throw new RuntimeException("Invalid skill type!");
				}
				$train_type = $_POST['skill'] . '_skill';
			}
			else if(!empty($_POST['attributes'])) {
				if($player->total_stats >= $player->rank->stat_cap) {
					throw new RuntimeException("You cannot train any more at this rank!");
				}
				switch($_POST['attributes']) {
					case 'cast_speed':
					case 'speed':
					case 'intelligence':
					case 'willpower':
						break;
					default:
						throw new RuntimeException("Invalid attributes!");
				}
				$train_type = $_POST['attributes'];
			}
			else if(!empty($_POST['jutsu'])) {
				$jutsu_id = (int)$_POST['jutsu'];
				if(!$player->hasJutsu($jutsu_id)) {
					throw new RuntimeException("Invalid jutsu!");
				}
				if($player->jutsu[$jutsu_id]->level >= 100) {
					throw new RuntimeException("You cannot train this jutsu any further!");
				}
				$train_type = 'jutsu:' . System::slug($player->jutsu[$jutsu_id]->name);
				$train_type = $system->db->clean($train_type);
				$train_gain = $jutsu_id;
				$train_length = 600 + (60 * round(pow($player->jutsu[$jutsu_id]->level, 1.1)));

                // Create notification
                if (!$notification_created) {
                    $new_notification = new NotificationDto(
                        type: NotificationManager::NOTIFICATION_TRAINING,
                        message: "Training " . System::unSlug($player->jutsu[$jutsu_id]->name),
                        user_id: $player->user_id,
                        created: time(),
                        duration: $train_length,
                        alert: false,
                    );
                    NotificationManager::createNotification($new_notification, $system, NotificationManager::UPDATE_REPLACE);
                    $notification_created = true;
                }
			}
            else if (isset($_POST['bloodline_jutsu'])) {
                $jutsu_id = (int) $_POST['bloodline_jutsu'];
                if (!isset($player->bloodline)) {
                    throw new RuntimeException("No Bloodline!");
                }
				if (!isset($player->bloodline->jutsu[$_POST['bloodline_jutsu']])) {
                    throw new RuntimeException("Invalid jutsu!");
                }
				if ($player->rank_num < $player->bloodline->jutsu[$_POST['bloodline_jutsu']]->rank) {
                    throw new RuntimeException("Invalid jutsu!");
                }
                if ($player->bloodline->jutsu[$jutsu_id]->level >= 100) {
                    throw new RuntimeException("You cannot train this jutsu any further!");
                }
                $train_type = 'bloodline_jutsu:' . System::slug($player->bloodline->jutsu[$jutsu_id]->name);
                $train_type = $system->db->clean($train_type);
                $train_gain = $jutsu_id;
                $train_length = 600 + (60 * round(pow($player->bloodline->jutsu[$jutsu_id]->level, 1.1)));

                // Create notification
                if (!$notification_created) {
                    $new_notification = new NotificationDto(
                        type: NotificationManager::NOTIFICATION_TRAINING,
                        message: "Training " . System::unSlug($player->bloodline->jutsu[$jutsu_id]->name),
                        user_id: $player->user_id,
                        created: time(),
                        duration: $train_length,
                        alert: false,
                    );
                    NotificationManager::createNotification($new_notification, $system, NotificationManager::UPDATE_REPLACE);
                    $notification_created = true;
                }
            }
			else {
				throw new RuntimeException("Invalid training type!");
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
				$sensei_boost = SenseiManager::getTrainingBoostForTrainType($player->sensei_id, $train_type, $player->bloodline_id, $system);
                $system->message("Your training was reduced by " . ($train_length * ($sensei_boost / 100)) . " seconds due to your student boost.");
                $train_length *= 1 - ($sensei_boost / 100);
			}

			$player->log(User::LOG_TRAINING, "Type: {$train_type} / Length: {$train_length}");

			$player->train_type = $train_type;
			$player->train_gain = $train_gain;
			$player->train_time = time() + $train_length;

			// Create notification
            if (!$notification_created) {
                $new_notification = new NotificationDto(
                    type: NotificationManager::NOTIFICATION_TRAINING,
                    message: "Training " . System::unSlug($train_type),
                    user_id: $player->user_id,
                    created: time(),
                    duration: $train_length,
                    alert: false,
                );
                NotificationManager::createNotification($new_notification, $system, NotificationManager::UPDATE_REPLACE);
            }

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
	echo "<table class='table'><tr><th colspan='4'>Academy</th></tr>
		<tr><td colspan='4'>
		<p style='text-align:center;'>Here at the academy, you can take classes to improve your skills, attributes, or skill with a
		jutsu.</p>
		<br />
		<span style='font-weight:bold;'>Skill/Attribute training:</span><br />
			<p style='margin-left:20px;margin-top:5px;margin-bottom:8px;'>
			<label style='font-weight:bold;width:70px;'>Short:</label>
				Takes " . ($trainingManager->stat_train_length / 60) . " minutes, gives $trainingManager->stat_train_gain point" . ($trainingManager->stat_train_gain > 1 ? 's' : '') . "<br />
			<label style='font-weight:bold;width:70px;'>Long:</label>
				Takes " . ($trainingManager->stat_long_train_length / 60) . " minutes, gives $trainingManager->stat_long_train_gain point" . ($trainingManager->stat_long_train_gain > 1 ? 's' : '') . "<br />
            <label style='font-weight:bold;width:70px;'>Extended:</label>
				Takes " . ($trainingManager->stat_extended_train_length / 60) . " minutes, gives $trainingManager->stat_extended_train_gain point" . ($trainingManager->stat_extended_train_gain > 1 ? 's' : '') . "<br />
			</p>
		<span style='font-weight:bold;'>Jutsu training:</span><br />
			<p style='margin-left:20px;margin-top:5px;margin-bottom:8px;'>
			Takes 10 minutes or more depending on the jutsu level, gives {$trainingManager->jutsu_train_gain} level" . ($trainingManager->jutsu_train_gain > 1 ? 's' : '') . ".</p>
		</td></tr>";
	if($player->train_time) {
		echo "<tr><th colspan='4'>Currently Training</th></tr>
		<tr><td colspan='4' style='text-align:center'>";
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
				<th style='width:25%;'>Skills</th>
				<th style='width:25%;'>Attributes</th>
				<th style='width:25%;'>Jutsu</th>
				<th style='width:25%;'>Bloodline Jutsu</th>
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
				<td style='text-align:center;'>
					<form action='$self_link' method='post'>
						<select name='bloodline_jutsu'>";
						foreach($player->bloodline->jutsu as $id => $jutsu) {
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
