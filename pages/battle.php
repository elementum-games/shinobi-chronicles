<?php
/*
File: 		battle.php
Coder:		Levi Meahan
Created:	12/18/2013
Revised:	05/02/2014 by Levi Meahan
Purpose:	Functions for initiating combat and distributing post-combat rewards
Algorithm:	See master_plan.html
*/

/**
 * @return bool
 * @throws RuntimeException
 */
function battle(): bool {
	global $system;
	global $player;
	global $self_link;

	if($player->battle_id) {
        if($system->USE_NEW_BATTLES) {
            $battle = BattleManagerV2::init($system, $player, $player->battle_id);
        }
        else {
            $battle = BattleManager::init($system, $player, $player->battle_id);
        }

        $battle->checkInputAndRunTurn();

        $battle->renderBattle();

        if($battle->isComplete()) {
            $player->battle_id = 0;
            $result = processBattleFightEnd($battle, $player);

			echo "<table class='table'>
                <tr><th>Battle complete</th></tr>
			    <tr><td style='text-align:center;'>" . str_replace("[br]", "<br />", $result) . "</td></tr>
            </table>";
		}
	}
	else if(isset($_GET['attack'])) {
		try {
			$attack_id = $system->db->clean($_GET['attack']);

			try {
                // get user id off the attack link
                $result = $system->db->query("SELECT `user_id` FROM `users` WHERE `attack_id`='{$attack_id}' LIMIT 1");
                if ($system->db->last_num_rows == 0) {
                    throw new RuntimeException("Invalid user!");
                }

                $attack_link = $system->db->fetch($result);
                $attack_id = $attack_link['user_id'];

			    $user = User::loadFromId($system, $attack_id);
			    $user->loadData(User::UPDATE_NOTHING, true);
            } catch(RuntimeException $e) {
                throw new RuntimeException("Invalid user! " . $e->getMessage());
            }

            // check if the location forbids pvp
            if ($player->current_location->location_id && $player->current_location->pvp_allowed == 0) {
                throw new RuntimeException("You cannot fight at this location!");
            }

			if($user->village->name == $player->village->name) {
				throw new RuntimeException("You cannot attack people from your own village!");
			}

            if($user->rank_num < 3) {
				throw new RuntimeException("You cannot attack people below Chuunin rank!");
			}
			if($player->rank_num < 3) {
				throw new RuntimeException("You cannot attack people Chuunin rank and higher!");
			}

            if($user->rank_num !== $player->rank_num) {
                throw new RuntimeException("You can only attack people of the same rank!");
            }

			if(!$user->location->equals($player->location)) {
				throw new RuntimeException("Target is not at your location!");
			}
			if($user->battle_id) {
				throw new RuntimeException("Target is in battle!");
			}
			if($user->last_active < time() - 120) {
				throw new RuntimeException("Target is inactive/offline!");
			}
			if($player->last_death_ms > System::currentTimeMs() - (60 * 1000)) {
				throw new RuntimeException("You died within the last minute, please wait " .
					ceil((($player->last_death_ms + (60 * 1000)) - System::currentTimeMs()) / 1000) . " more seconds.");
			}
			if($user->last_death_ms > System::currentTimeMs() - (60 * 1000)) {
				throw new RuntimeException("Target has died within the last minute, please wait " .
					ceil((($user->last_death_ms + (60 * 1000)) - System::currentTimeMs()) / 1000) . " more seconds.");
			}

            if($system->USE_NEW_BATTLES) {
                BattleV2::start($system, $player, $user, Battle::TYPE_FIGHT);
            }
            else {
                Battle::start($system, $player, $user, Battle::TYPE_FIGHT);
            }

            $system->message("You have attacked!<br />
				<a class='link' href='$self_link'>To Battle</a>");
			$system->printMessage();
		} catch (Exception $e) {
			$system->message($e->getMessage());
			$system->printMessage();

			NearbyPlayers::renderScoutAreaList($system, $player, $self_link);
		}
	}
	else {
        NearbyPlayers::renderScoutAreaList($system, $player, $self_link);
	}
	return true;
}

/**
 * @throws RuntimeException
 */
function processBattleFightEnd(BattleManager $battle, User $player): string {
    $pvp_yen = $player->rank_num * 50;

    $result = "";

    if($battle->isPlayerWinner()) {
        $player->pvp_wins++;
        $player->monthly_pvp++;
        $player->last_pvp_ms = System::currentTimeMs();
        $village_point_gain = 1;
        $team_point_gain = 1;

        $player->addMoney($pvp_yen, "PVP win");
        $result .= "You win the fight and earn ¥$pvp_yen![br]";

        $player->system->db->query(
            "UPDATE `villages` SET `points`=`points`+'$village_point_gain' WHERE `name`='{$player->village->name}' LIMIT 1"
        );
        $result .= "You have earned $village_point_gain point for your village.[br]";

        // Calculate rep gains
        $rep_gain = $player->reputation->calcPvpRep($player->level, $player->reputation->rank, $battle->opponent->level,
            $battle->opponent->reputation->rank, $battle->opponent->user_id);
        if($rep_gain > 0) {
            $result .= "You have earned $rep_gain village reputation.[br]";
            $player->reputation->addRep($rep_gain, true);
        }

        // Team points
        if($player->team != null) {
            $player->team->addPoints($team_point_gain);

            $result .= "You have earned $team_point_gain point for your team.[br]";
        }
        // Daily Tasks
        foreach ($player->daily_tasks as $task) {
            if ($task->activity == DailyTask::ACTIVITY_PVP && !$task->complete) {
                $task->progress++;
            }
        }
    }
    else if($battle->isOpponentWinner()) {
        $result .= "You lose. You were taken back to your village by some allied ninja.[br]";
        $player->health = 5;
        $player->pvp_losses++;
        $player->last_pvp_ms = System::currentTimeMs();
        $player->last_death_ms = System::currentTimeMs();
        $player->moveToVillage();

        // Calc rep loss (if any)
        // Calculate rep gains
        $rep_loss = $player->reputation->calcPvpRep($player->level, $player->reputation->rank, $battle->opponent->level,
            $battle->opponent->reputation->rank,$battle->opponent->user_id, false);
        if($rep_loss > 0) {
            $result .= "You have lost " . abs($rep_loss) . " village reputation.[br]";
            $player->reputation->subtractRep(abs($rep_loss));
        }

        // If player is killed during a survival mission as a result of PVP, clear the survival mission
        if($player->mission_id != null) {
            check_survival_missions($player->mission_id);
        }

        // Daily Tasks
        foreach ($player->daily_tasks as $task) {
            if ($task->activity == DailyTask::ACTIVITY_PVP && $task->sub_task == DailyTask::SUB_TASK_COMPLETE && !$task->complete) {
                $task->progress++;
            }
        }
    }
    else {
        $result .= "You both knocked each other out. You were taken back to your village by some allied ninja.[br]";
        $player->health = 5;
        $player->moveToVillage();
        $player->last_pvp_ms = System::currentTimeMs();

        // If player is killed during a survival mission as a result of PVP, clear the survival mission
        if($player->mission_id != null) {
            check_survival_missions($player->mission_id);
        }

        // Daily Tasks
        foreach ($player->daily_tasks as $task) {
            if ($task->activity == DailyTask::ACTIVITY_PVP && $task->sub_task == DailyTask::SUB_TASK_COMPLETE && !$task->complete) {
                $task->progress++;
            }
        }
    }

    return $result;
}

/**
 * Note this function is V2 (new battles) only as old battles do not use the API
 *
 * @param System $system
 * @param User   $player
 * @return BattlePageAPIResponse
 */
function battleFightAPI(System $system, User $player): BattlePageAPIResponse {
    if(!$player->battle_id) {
        return new BattlePageAPIResponse(errors: ["Player is not in battle!"]);
    }

    $response = new BattlePageAPIResponse();

    try {
        $battle = BattleManagerV2::init($system, $player, $player->battle_id);
        $battle->checkInputAndRunTurn();

        $response->battle_data = $battle->getApiResponse();

        if($battle->isComplete()) {
            $response->battle_result = processBattleFightEnd($battle, $player);
        }
    }
    catch (Exception $e) {
        $response->errors[] = $e->getMessage();
    }

    return $response;
}

/**
 * @param int $mission_id
 * @return void
 */
function check_survival_missions(Int $mission_id): void
{
    global $system;
    global $player;

    $result = $system->db->query("SELECT `mission_type` FROM `missions` WHERE `mission_id`='$mission_id' LIMIT 1");
    if ($system->db->last_num_rows == 0) {
        return;
    }
    $mission_data = $system->db->fetch($result);

    if ($mission_data['mission_type'] == "5") {
        $mission = new Mission($player->mission_id, $player);
        $mission->nextStage($player->mission_stage['stage_id'] = 4);
    }
}
