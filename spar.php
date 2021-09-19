<?php
/* 
File: 		spar.php
Coder:		Levi Meahan
Created:	05/02/2014
Revised:	05/02/2014 by Levi Meahan
Purpose:	Functions for initiating spars and distributing post-spar rewards
Algorithm:	See master_plan.html
*/

function spar() {
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
				echo "You win!<br />";
			}
			else if($winner == $opponent->user_id) {
				echo "You lose.<br />";
				$player->health = 5;
			}
			else {
				echo "You both knocked each other out.<br />";
				$player->health = 5;
			}
			echo "</td></tr></table>";
			
			$player->battle_id = 0;
		}
	}
	else if($_GET['challenge']) {
		try {
			$challenge = (int)$system->clean($_GET['challenge']);
			$result = $system->query("SELECT `user_id`, `user_name`, `village`, `location`, `challenge`, `battle_id`, `last_active`
				FROM `users` WHERE `user_id`='$challenge' LIMIT 1");
			if($system->db_num_rows == 0) {
				throw new Exception("Invalid user!");
			}
			$user = $system->db_fetch($result);
			
			/*
			if($user['village'] != $player->village) {
				throw new Exception("You cannot spar ninja from enemy villages!");
			}
			*/
			
			if($user['location'] != $player->location) {
				throw new Exception("Target is not at your location!");
			}
			
			if($user['challenge']) {
				throw new Exception("Target has already been challenged!");
			}
				
			if($user['battle_id']) {
				throw new Exception("Target is in battle!");
			}
			
			if($user['last_active'] < time() - 120) {
				throw new Exception("Target is inactive/offline!");
			}
			
			$system->query("UPDATE `users` SET `challenge`='$player->user_id' WHERE `user_id`='$challenge' LIMIT 1");
			$system->message("Challenge sent!");
			$system->printMessage();
		} catch (Exception $e) {
			$system->message($e->getMessage());
			$system->printMessage();
			require("scoutArea.php");
			scoutArea();
		}
	}
	else if($_GET['accept_challenge']) {
		try {
			$challenge = (int)$system->clean($_GET['accept_challenge']);
			
			if($challenge != $player->challenge) {
				throw new Exception("Invalid challenge!");
			}

            try {
                $user = new User($challenge);
                $user->loadData(1, true);
            } catch(Exception $e) {
                throw new Exception("Invalid user! " . $e->getMessage());
            }
			
			if($user->location != $player->location) {
				throw new Exception("Target is not at your location!");
			}
			
			if($user->battle_id) {
				throw new Exception("User is in battle!");
			}
			
			if($user->last_active < time() - 120) {
				throw new Exception("Target is inactive/offline!");
			}

            $player->challenge = 0;
            Battle::start($system, $player, $user, Battle::TYPE_SPAR);

			$system->message("You have accepted the challenge!<br />
				<a class='link' href='$self_link'>To Battle</a>");
			$system->printMessage();
		} catch (Exception $e) {
			$player->challenge = 0;
			
			$system->message($e->getMessage());
			$system->printMessage();
			require("scoutArea.php");
			scoutArea();
		}
	}
	else if($_GET['decline_challenge']) {
		$player->challenge = 0;
		$system->message("Challenge declined.");
		$system->printMessage();
		
		require("scoutArea.php");
		scoutArea();
	}
	else if($_GET['cancel_challenge']) {
		$challenge = $system->clean($_GET['cancel_challenge']);
		// Load user challenges sent
		$result = $system->query("UPDATE `users` SET `challenge`=0 WHERE `user_id`='$challenge' AND `challenge`='$player->user_id' LIMIT 1");
		$system->message("Challenge cancelled!");
		$system->printMessage();
			
		require("scoutArea.php");
		scoutArea();
	}
	else {
		// Load user challenges sent
		$result = $system->query("SELECT `user_id`, `user_name` FROM `users` WHERE `challenge`='$player->user_id'");
		if($system->db_num_rows > 0) {
			$user_challenges = array();
			while($row = $system->db_fetch($result)) {
				$user_challenges[$row['user_id']] = $row['user_name'];
			}
		}
		
		if($player->challenge or isset($user_challenges)) {
			echo "<table class='table'><tr><th>Challenges</th></tr>";
				
			// Challenge received
			if($player->challenge) {
				$result = $system->query("SELECT `user_name` FROM `users` WHERE `user_id`='$player->challenge' LIMIT 1");
				if($system->db_num_rows == 0) {
					$player->challenge = 0;
				}
				else {
					$challenger_data = $system->db_fetch($result);
					
					echo "<tr><td>
					<p style='display:inline-block;margin:0px;margin-left:20px;'>
						Challenged by <span style='font-weight:bold;'>" . $challenger_data['user_name'] . "</span></p>
					<p style='display:inline-block;margin:0px;margin-right:40px;float:right;'>
						<a href='$self_link&accept_challenge=$player->challenge'>Accept</a> | 
						<a href='$self_link&decline_challenge=$player->challenge'>Decline</a>
					</p></td></tr>";

				}
			}
			if($user_challenges) {
				foreach($user_challenges as $id=>$name) {
					echo "<tr><td>
					<p style='display:inline-block;margin:0px;margin-left:20px;'>
						Challenge sent to <span style='font-weight:bold;'>" . $name . "</span></p>
					<p style='display:inline-block;margin:0px;margin-right:40px;float:right;'>
						<a href='$self_link&cancel_challenge=$id'>Cancel</a></p>
					</td></tr>";
				}
			}
			
			echo "</table>";
		}
		
		require("scoutArea.php");
		scoutArea();
	}
	
	return true;
}