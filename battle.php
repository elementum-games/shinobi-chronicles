<?php
/* 
File: 		battle.php
Coder:		Levi Meahan
Created:	12/18/2013
Revised:	05/02/2014 by Levi Meahan
Purpose:	Functions for initiating combat and distributing post-combat rewards
Algorithm:	See master_plan.html
*/
function battle() {
	global $system;
	global $player;
	global $self_link;

	if($player->battle_id) {
		require("battleCore.php");
		$result = $system->query("SELECT * FROM `battles` WHERE `battle_id`='$player->battle_id' LIMIT 1");
		if($system->db_num_rows == 0) {
			$system->message("Invalid battle! - fetch");
			$system->printMessage();
			$player->battle_id = 0;
			return false;
		}
		$battle = $system->db_fetch($result);
		if($player->user_id == $battle['player1']) {
			$opponent = new User($battle['player2']);
			$battle['player_side'] = 'player1';
			$battle['opponent_side'] = 'player2';
		}
		else if($player->user_id == $battle['player2']) {
			$opponent = new User($battle['player1']);
			$battle['player_side'] = 'player2';
			$battle['opponent_side'] = 'player1';
		}
		else {
			$system->message("Invalid battle! - p1/p2 check");
			$system->printMessage();
			$player->battle_id = 0;
			return false;
		}
		$opponent->loadData(1);
		$winner = battlePvP($player, $opponent, $battle);
		if($winner !== false) {
			echo "<table class='table'><tr><th>Battle complete</th></tr>
			<tr><td style='text-align:center;'>";
			if($winner == $player->user_id) {
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
			}
			else if($winner == $opponent->user_id) {
				echo "You lose. You were taken back to your village by some allied ninja.<br />";
				$player->health = 5;
				$player->pvp_losses++;
				$player->last_pvp = time();
				$player->last_death = time();
				$player->location = $player->village_location;
				$location = explode('.', $player->location);
				$player->x = $location[0];
				$player->y = $location[1];
			}
			else {
				echo "You both knocked each other out. You were taken back to your village by some allied ninja.<br />";
				$player->health = 5;
				$player->location = $player->village_location;
				$location = explode('.', $player->location);
				$player->x = $location[0];
				$player->y = $location[1];
				$player->last_pvp = time();
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