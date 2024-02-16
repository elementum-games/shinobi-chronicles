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
		try {
            if($system->USE_NEW_BATTLES) {
                $battle = BattleManagerV2::init($system, $player, $player->battle_id);
            }
            else {
                $battle = BattleManager::init($system, $player, $player->battle_id);
            }

            $battle->checkInputAndRunTurn();

            $battle->renderBattle();

            if($battle->isComplete()) {
                $result = processSparFightEnd($battle, $player, $system);

                echo "<table class='table'>
                    <tr><th>Battle complete</th></tr>
			        <tr><td style='text-align:center;'>{$result}</td></tr>
                </table>";
            }
        }
        catch (RuntimeException $e) {
            error_log($e->getMessage());
            $system->message($e->getMessage());
            $system->printMessage();
            return false;
        }
    }
	else if(isset($_GET['challenge'])) {
		try {
			$challenge = (int)$system->db->clean($_GET['challenge']);
			$result = $system->db->query(
                "SELECT `user_id`, `user_name`, `village`, `location`, `challenge`, `battle_id`, `last_active`
                    FROM `users` WHERE `user_id`='$challenge' LIMIT 1"
            );
			if($system->db->last_num_rows == 0) {
				throw new RuntimeException("Invalid user!");
			}
			$user = $system->db->fetch($result);

			/*
			if($user['village'] != $player->village->name) {
				throw new RuntimeException("You cannot spar ninja from enemy villages!");
			}
			*/

			if(!$player->location->equals(TravelCoords::fromDbString($user['location']))) {
				throw new RuntimeException("Target is not at your location!");
			}

			if($user['challenge']) {
				throw new RuntimeException("Target has already been challenged!");
			}

			if($user['battle_id']) {
				throw new RuntimeException("Target is in battle!");
			}

			if($user['last_active'] < time() - 120) {
				throw new RuntimeException("Target is inactive/offline!");
			}

			$system->db->query("UPDATE `users` SET `challenge`='$player->user_id' WHERE `user_id`='$challenge' LIMIT 1");
			$system->message("Challenge sent!");
            $system->printMessage();
		} catch (RuntimeException $e) {
			$system->message($e->getMessage());
			$system->printMessage();

            NearbyPlayers::renderScoutAreaList($system, $player, $self_link);
		}
	}
	else if(isset($_GET['accept_challenge'])) {
		try {
			$challenge = (int)$system->db->clean($_GET['accept_challenge']);

			if($challenge != $player->challenge) {
				throw new RuntimeException("Invalid challenge!");
			}

            try {
                $user = User::loadFromId($system, $challenge, true);
                $user->loadData(User::UPDATE_NOTHING, true);
            } catch(RuntimeException $e) {
                throw new RuntimeException("Invalid user! " . $e->getMessage());
            }

			if(!$user->location->equals($player->location)) {
				throw new RuntimeException("Target is not at your location!");
			}

			if($user->battle_id) {
				throw new RuntimeException("User is in battle!");
			}

			if($user->last_active < time() - 120) {
				throw new RuntimeException("Target is inactive/offline!");
			}

            $player->challenge = 0;
            $battle_background = TravelManager::getLocationBattleBackgroundLink($system, $player->location);
            if (TravelManager::locationIsInVillage($system, $player->location)) {
                $battle_background = 'images/battle_backgrounds/Spar.jpg';
            }
            if (empty($battle_background)) {
                $battle_background = $player->region->battle_background_link;
            }
            if ($system->USE_NEW_BATTLES) {
                BattleV2::start($system, $player, $user, Battle::TYPE_SPAR, battle_background_link: $battle_background);
            }
            else {
                Battle::start($system, $player, $user, Battle::TYPE_SPAR, battle_background_link: $battle_background);
            }

			$system->message("You have accepted the challenge!<br />
				<a class='link' href='$self_link'>To Battle</a>");
			$system->printMessage();
		} catch (RuntimeException $e) {
			$player->challenge = 0;

			$system->message($e->getMessage());
			$system->printMessage();

            NearbyPlayers::renderScoutAreaList($system, $player, $self_link);
		}
	}
	else if(isset($_GET['decline_challenge'])) {
		$player->challenge = 0;
		$system->message("Challenge declined.");
		$system->printMessage();

        NearbyPlayers::renderScoutAreaList($system, $player, $self_link);
	}
	else if(isset($_GET['cancel_challenge'])) {
		$challenge = $system->db->clean($_GET['cancel_challenge']);
		// Load user challenges sent
		$result = $system->db->query(
            "UPDATE `users` SET `challenge`=0 WHERE `user_id`='$challenge' AND `challenge`='$player->user_id' LIMIT 1"
        );
		$system->message("Challenge cancelled!");
		$system->printMessage();

        NearbyPlayers::renderScoutAreaList($system, $player, $self_link);
	}
	else {
        $user_challenges = [];

		// Load user challenges sent
		$result = $system->db->query("SELECT `user_id`, `user_name` FROM `users` WHERE `challenge`='$player->user_id'");
		if($system->db->last_num_rows > 0) {
			while($row = $system->db->fetch($result)) {
				$user_challenges[$row['user_id']] = $row['user_name'];
			}
		}

		if($player->challenge or count($user_challenges) > 0) {
			echo "<table class='table'><tr><th>Challenges</th></tr>";

			// Challenge received
			if($player->challenge) {
				$result = $system->db->query(
                    "SELECT `user_name` FROM `users` WHERE `user_id`='$player->challenge' LIMIT 1"
                );
				if($system->db->last_num_rows == 0) {
					$player->challenge = 0;
				}
				else {
					$challenger_data = $system->db->fetch($result);

					echo "<tr><td>
					<p style='display:inline-block;margin:0;margin-left:20px;'>
						Challenged by <span style='font-weight:bold;'>" . $challenger_data['user_name'] . "</span></p>
					<p style='display:inline-block;margin:0;margin-right:40px;float:right;'>
						<a href='$self_link&accept_challenge=$player->challenge'>Accept</a> |
						<a href='$self_link&decline_challenge=$player->challenge'>Decline</a>
					</p></td></tr>";

				}
			}
			if(count($user_challenges) > 0) {
				foreach($user_challenges as $id=>$name) {
					echo "<tr><td>
					<p style='display:inline-block;margin:0;margin-left:20px;'>
						Challenge sent to <span style='font-weight:bold;'>" . $name . "</span></p>
					<p style='display:inline-block;margin:0;margin-right:40px;float:right;'>
						<a href='$self_link&cancel_challenge=$id'>Cancel</a></p>
					</td></tr>";
				}
			}

			echo "</table>";
		}

        NearbyPlayers::renderScoutAreaList($system, $player, $self_link);
	}

	return true;
}

/**
 * @throws RuntimeException
 */
function processSparFightEnd(BattleManager $battle, User $player, System $system): string {
    $player->battle_id = 0;
	$result = "";

    $reputation_eligible = isReputationEligible($battle, $player, $system);

    if ($battle->isPlayerWinner()) {
        $result = "You win!";
        if ($reputation_eligible) {
            $rep_gain = $player->reputation->handleSpar($player, $battle->opponent, UserReputation::SPAR_REP_WIN);
            if ($rep_gain > 0) {
                $result .= "<br>Fellow Shinobi learned from your battle, gaining you $rep_gain village reputation.";
            }
            // Daily Task
            if ($player->daily_tasks->hasTaskType(DailyTask::ACTIVITY_DAILY_PVP)) {
                $player->daily_tasks->progressTask(DailyTask::ACTIVITY_DAILY_PVP, UserReputation::SPAR_REP_WIN);
            }
        }
        return $result;
    }
    else if($battle->isOpponentWinner()) {
        $player->health = 5;
        $result = "You lose.";
        return $result;
    }
    else if($battle->isDraw()) {
        $player->health = 5;
        $result = "You both knocked each other out.";
        if ($reputation_eligible) {
            $rep_gain = $player->reputation->handleSpar($player, $battle->opponent, UserReputation::SPAR_REP_DRAW);
            if ($rep_gain > 0) {
                $result .= "<br>Fellow Shinobi learned from your battle, gaining you $rep_gain village reputation.";
            }
            // Daily Task
            if ($player->daily_tasks->hasTaskType(DailyTask::ACTIVITY_DAILY_PVP)) {
                $player->daily_tasks->progressTask(DailyTask::ACTIVITY_DAILY_PVP, UserReputation::SPAR_REP_DRAW);
            }
        }
        return $result;
    }
    else {
        throw new RuntimeException("Invalid battle completion!");
    }
}

function sparFightAPI(System $system, User $player): BattlePageAPIResponse {
    if(!$player->battle_id) {
        return new BattlePageAPIResponse(errors: ["Player is not in battle!"]);
    }

    $response = new BattlePageAPIResponse();

    try {
        if($system->USE_NEW_BATTLES) {
            $battle = BattleManagerV2::init($system, $player, $player->battle_id);
        }
        else {
            $battle = BattleManager::init($system, $player, $player->battle_id);
        }
        $battle->checkInputAndRunTurn();

        $response->battle_data = $battle->getApiResponse();

        if($battle->isComplete()) {
           $response->battle_result = processSparFightEnd($battle, $player, $system);
        }
    }
    catch (RuntimeException $e) {
        $response->errors[] = $e->getMessage();
    }

    return $response;
}

function isReputationEligible(BattleManager $battle, User $player, System $system): bool {
	// if at Underground Colosseum or Village
    $travelManager = new TravelManager($system, $player);
    $arena_coords = $travelManager->getColosseumCoords();
    if (!$player->location->equals($arena_coords) && !$player->location->equals($player->village_location)) {
        return false;
    }

	// if players within 5 levels
    if (abs($player->level - $battle->opponent->level) > 5) {
        return false;
    }

	// if battle at least 3 turns long
    if ($battle->turn_count < 3) {
        return false;
    }

    return true;
}