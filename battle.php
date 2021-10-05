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
        $battle = new Battle($system, $player, $player->battle_id);

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
				echo "You win!<br />";
				// Village points
				$system->query("UPDATE `villages` SET `points`=`points`+'$village_point_gain' WHERE `name`='$player->village' LIMIT 1");
				echo "You have earned $village_point_gain point for your village.<br />";
				// Team points
				if($player->team) {
					$system->query("UPDATE `teams` SET `points`=`points`+'$team_point_gain', `monthly_points`=`monthly_points`+'$team_point_gain'  
						WHERE `team_id`={$player->team['id']} LIMIT 1");
					echo "You have earned $team_point_gain point for your team.<br />";
				}
				// Daily Tasks
				$dt = [];
				foreach ($player->daily_tasks as $task) {
					if ($task['Task'] == 'PVP Battles' && $task['Complete'] != 1) {
						$task['Progress']++;
					}
					array_push($dt, $task);
				}
				$player->daily_tasks = $dt;
			}
			else if($battle->isOpponentWinner()) {
				echo "You lose. You were taken back to your village by some allied ninja.<br />";
				$player->health = 5;
				$player->pvp_losses++;
				$player->last_pvp = time();
				$player->last_death = time();
				$player->location = $player->village_location;
				$location = explode('.', $player->location);
				$player->x = $location[0];
				$player->y = $location[1];
				// Daily Tasks
				$dt = [];
				foreach ($player->daily_tasks as $task) {
					if ($task['Task'] == 'PVP Battles' && $task['SubTask'] == 'Complete' && $task['Complete'] != 1) {
						$task['Progress']++;
					}
					array_push($dt, $task);
				}
				$player->daily_tasks = $dt;
			}
			else {
				echo "You both knocked each other out. You were taken back to your village by some allied ninja.<br />";
				$player->health = 5;
				$player->location = $player->village_location;
				$location = explode('.', $player->location);
				$player->x = $location[0];
				$player->y = $location[1];
				$player->last_pvp = time();
				// Daily Tasks
				$dt = [];
				foreach ($player->daily_tasks as $task) {
					if ($task['Task'] == 'PVP Battles' && $task['SubTask'] == 'Complete' && $task['Complete'] != 1) {
						$task['Progress']++;
					}
					array_push($dt, $task);
				}
				$player->daily_tasks = $dt;
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
			    $user->loadData(1, true);
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
			if($user->location != $player->location) {
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