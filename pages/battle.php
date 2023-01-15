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
 * @throws Exception
 */
function battle(): bool {
	global $system;
	global $player;
	global $self_link;

	if($player->battle_id) {
        $battle = new BattleManager($system, $player, $player->battle_id);

		$pvp_yen = $player->rank * 50;

        $battle->checkTurn();

        $battle->renderBattle();

        if($battle->isComplete()) {
			echo "<table class='table'><tr><th>Battle complete</th></tr>
			<tr><td style='text-align:center;'>";
			if($battle->isPlayerWinner()) {
				$player->pvp_wins++;
				$player->monthly_pvp++;
				$player->last_pvp = time();
				$village_point_gain = 1;
				$team_point_gain = 1;
				$player->addMoney($pvp_yen, "PVP win");
				echo "You win the fight and earn ¥$pvp_yen!<br />";
				// Village points
				$system->query("UPDATE `villages` SET `points`=`points`+'$village_point_gain' WHERE `name`='$player->village' LIMIT 1");
				echo "You have earned $village_point_gain point for your village.<br />";
				// Team points
				if($player->team != null) {
				    $player->team->addPoints($team_point_gain);

					echo "You have earned $team_point_gain point for your team.<br />";
				}
				// Daily Tasks
				foreach ($player->daily_tasks as $task) {
					if ($task->activity == DailyTask::ACTIVITY_PVP && !$task->complete) {
						$task->progress++;
					}
				}
			}
			else if($battle->isOpponentWinner()) {
				echo "You lose. You were taken back to your village by some allied ninja.<br />";
				$player->health = 5;
				$player->pvp_losses++;
				$player->last_pvp = time();
				$player->last_death = time();
				$player->moveToVillage();

                // If player is killed during a survival mission as a result of PVP, clear the survival mission
                if($player->mission_id != null)
                {
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
				echo "You both knocked each other out. You were taken back to your village by some allied ninja.<br />";
				$player->health = 5;
				$player->moveToVillage();
				$player->last_pvp = time();

                // If player is killed during a survival mission as a result of PVP, clear the survival mission
                if($player->mission_id != null)
                {
                    check_survival_missions($player->mission_id);
                }
			}
			echo "</td></tr></table>";
			$player->battle_id = 0;
		}
	}
	else if($_GET['attack']) {
		try {
			$attack_id = (int)$system->clean($_GET['attack']);

			try {
			    $user = new User($attack_id);
			    $user->loadData(User::UPDATE_NOTHING, true);
            } catch(Exception $e) {
                throw new Exception("Invalid user! " . $e->getMessage());
            }

			if($user->village == $player->village) {
				throw new Exception("You cannot attack people from your own village!");
			}

            if($user->rank < 3) {
				throw new Exception("You cannot attack people below Chuunin rank!");
			}
			if($player->rank < 3) {
				throw new Exception("You cannot attack people Chuunin rank and higher!");
			}

            if($user->rank !== $player->rank) {
                throw new Exception("You can only attack people of the same rank!");
            }

			if($user->location !== $player->location) {
				throw new Exception("Target is not at your location!");
			}
			if($user->battle_id) {
				throw new Exception("Target is in battle!");
			}
			if($user->last_active < time() - 120) {
				throw new Exception("Target is inactive/offline!");
			}
			if($player->last_death > time() - 60) {
				throw new Exception("You died within the last minute, please wait " .
					(($player->last_death + 60) - time()) . " more seconds.");
			}
			if($user->last_death > time() - 60) {
				throw new Exception("Target has died within the last minute, please wait " .
					(($user->last_death + 60) - time()) . " more seconds.");
			}

			Battle::start($system, $player, $user, Battle::TYPE_FIGHT);
			$system->message("You have attacked!<br />
				<a class='link' href='$self_link'>To Battle</a>");
			$system->printMessage();
		} catch (Exception $e) {
			$system->message($e->getMessage());
			$system->printMessage();
			require("scoutArea.php");
			scoutArea();
		}
	}
	else {
		require("scoutArea.php");
		scoutArea();
	}
	return true;
}

/**
 * @param int $mission_id
 * @return void
 */
function check_survival_missions(Int $mission_id): void
{
    global $system;
    global $player;

    $result = $system->query("SELECT `mission_type` FROM `missions` WHERE `mission_id`='$mission_id' LIMIT 1");
    if ($system->db_last_num_rows == 0) {
        return;
    }
    $mission_data = $system->db_fetch($result);

    if ($mission_data['mission_type'] == "5") {
        $mission = new Mission($player->mission_id, $player);
        $mission->nextStage($player->mission_stage['stage_id'] = 4);
    }
}
