<?php
/*
File: 		missions.php
Coder:		Levi Meahan
Created:	05/04/2014
Revised:	05/04/2014 by Levi Meahan
Purpose:	Functions for missions
Algorithm:	See master_plan.html
*/

/**
 * @throws Exception
 */
function missions(): bool {
	global $system;

	global $player;

	global $self_link;
	global $RANK_NAMES;

	if($player->mission_id) {
        runActiveMission();
        return true;
	}

	$max_mission_rank = Mission::maxMissionRank($player->rank_num);

	$result = $system->query("SELECT `mission_id`, `name`, `rank` FROM `missions` WHERE `mission_type`=1 OR `mission_type`=5 AND `rank` <= $max_mission_rank");
	if($system->db_last_num_rows == 0) {
		$system->message("No missions available!");
		$system->printMessage();
		return false;
	}

	$missions = array();
	while($row = $system->db_fetch($result)) {
		$missions[$row['mission_id']] = $row;
	}


	// Sub-menu
	echo "<div class='submenu'>
	<ul class='submenu'>";
	$width = 100 / $max_mission_rank;
	$width = round($width - 0.6, 2);
	for($i = 1; $i <= $max_mission_rank; $i++) {
		echo "<li style='width:$width%;'><a href='{$self_link}&view_rank=$i'>" . Mission::$rank_names[$i] . "</a></li> ";
	}
	echo "</ul>
	</div>
	<div class='submenuMargin'></div>";

	// Start mission
	if(!empty($_GET['start_mission'])) {
		$mission_id = $_GET['start_mission'];
		try {
            if(!isset($missions[$mission_id])) {
                throw new Exception("Invalid mission!");
            }
            Mission::start($player, $mission_id);
            $player->log(User::LOG_MISSION, "Mission ID #{$mission_id}");

            // Create notification
            require_once __DIR__ . '/../classes/notification/NotificationManager.php';
            if ($player->mission_stage['action_type'] == 'travel' or $player->mission_stage['action_type'] == 'search') {
                $mission_location = TravelCoords::fromDbString($player->mission_stage['action_data']);
                $new_notification = new MissionNotificationDto(
                    type: "mission",
                    message: $missions[$mission_id]['name'] . ": Travel to " . $mission_location->x . ":" . $mission_location->y,
                    user_id: $player->user_id,
                    created: time(),
                    mission_rank: Mission::$rank_names[$missions[$mission_id]['rank']],
                    alert: false,
                );
                NotificationManager::createNotification($new_notification, $system, NotificationManager::UPDATE_REPLACE);
            }
            else {
                require_once __DIR__ . '/../classes/notification/NotificationManager.php';
                $new_notification = new MissionNotificationDto(
                    type: "mission",
                    message: $missions[$mission_id]['name'] . " in progress",
                    user_id: $player->user_id,
                    created: time(),
                    mission_rank: Mission::$rank_names[$missions[$mission_id]['rank']],
                    alert: false,
                );
                NotificationManager::createNotification($new_notification, $system, NotificationManager::UPDATE_REPLACE);
            }

            missions();
			return true;
		} catch (Exception $e) {
			$system->message($e->getMessage());
		}

	}


	// Display missions
	$system->printMessage();
	$view = $max_mission_rank;
	if(isset($_GET['view_rank'])) {
		$view = (int)$_GET['view_rank'];
		if($view < 1 or $view > $max_mission_rank) {
			$view = $max_mission_rank;
		}
	}

	echo "<table class='table'><tr><th>" . Mission::$rank_names[$view] . " Missions</th></tr>
	<tr><td style='text-align:center;'>You can go on village missions here. As a " . $RANK_NAMES[$player->rank_num] . " you
	can take on up to " . Mission::$rank_names[$max_mission_rank] . " missions.";
    if($player->mission_rep_cd - time() > 0) {
        $remaining = $player->mission_rep_cd - time();
        echo "<br /><br />You can gain village reputation in: <div id='rep_cd' style='display: inline-block'>"
            . System::timeRemaining($remaining) . "</div>
        <script type='text/javascript'>countdownTimer($remaining, 'rep_cd', false);</script>";
    }
    echo "</td></tr>
	<tr><td style='text-align:center;'>";
	foreach($missions as $id => $mission) {
		if($mission['rank'] != $view) {
			continue;
		}
		echo "<a href='$self_link&start_mission=$id'><p class='button' style='margin:5px;'>" . $mission['name'] . "</p></a><br />";
	}
	echo "</td></tr></table>";

    return true;
}

/**
 * @throws Exception
 */
function runActiveMission(): bool {
    global $system;
    global $player;
    global $self_link;

    if(!empty($_GET['cancel_mission'])) {
        if($player->mission_id == RankManager::JONIN_MISSION_ID) {
            $system->message("You must visit the <a href='{$system->router->links['rankup']}'>{$player->village->kage_name}'s
            Office</a> to end this mission.");
            $system->printMessage();
            return true;
        }
        $player->clearMission();
        $system->message("You have abandoned your mission. <a href='$self_link'>Continue</a>");
        $system->printMessage();
        return true;
    }
    // Call mission (Pass team if player has one, class will determine if current mission is team or not)
    if($player->team) {
        $mission = new Mission($player->mission_id, $player, $player->team);
    }
    else {
        $mission = new Mission($player->mission_id, $player);
    }

    // Check status/stage
    $mission_status = Mission::STATUS_IN_PROGRESS;
    $current_mission_stage_id = $player->mission_stage['stage_id'];

    //Survival Mission State Controls
    if($mission->mission_type === Mission::TYPE_SURVIVAL) {
        if(!empty($_GET['retreat'])) {
            $player->battle_id = 0;
            $mission->nextStage($player->mission_stage['stage_id'] = 4);
        }

        $continue_mission = false;
        if(!empty($_GET['continue'])) {
            $continue_mission = boolval($_GET['continue']);
        }
        if($player->mission_stage['round_complete'] && $continue_mission) {
            $player->mission_stage['round_complete'] = false;
            $player->battle_id = 0;
            $mission_status = $mission->nextStage($player->mission_stage['stage_id']);
        }
    }

    if($mission_status < Mission::STATUS_COMPLETE) {
        if($player->mission_stage['action_type'] == 'travel' or $player->mission_stage['action_type'] == 'search') {
            if($player->location->equals(TravelCoords::fromDbString($player->mission_stage['action_data']))) {
                // Team or solo
                if($mission->mission_type == Mission::TYPE_TEAM) {
                    $player->mission_stage['stage_id'] += 1;
                }
                else {
                    $player->mission_stage['stage_id'] += 1;
                }
            }
        }
        else if($player->mission_stage['action_type'] == 'combat') {
            try {
                // monster id
                $opponent = new NPC($system, $player->mission_stage['action_data']);
                if(!$opponent) {
                    throw new Exception("Couldn't load opponent for mission!");
                }
                $opponent->loadData();

                // Initialize start of battle stuff
                if(!$player->battle_id) {
                    if($system->USE_NEW_BATTLES) {
                        BattleV2::start($system, $player, $opponent, Battle::TYPE_AI_MISSION);
                    }
                    else {
                        Battle::start($system, $player, $opponent, Battle::TYPE_AI_MISSION);
                    }
                }

                if($system->USE_NEW_BATTLES) {
                    $battle = BattleManagerV2::init($system, $player, $player->battle_id);
                }
                else {
                    $battle = BattleManager::init($system, $player, $player->battle_id);
                }
                $battle->checkInputAndRunTurn();

                $battle->renderBattle();

                if($battle->isComplete()) {
                    $result = processMissionBattleEnd($battle, $mission, $player);
                    if(strlen($result) > 0) {
                        echo "<table class='table'>
                        <tr><th>Battle Results</th></tr>
                        <tr><td>{$result}</td></tr>
                    </table>";
                    }
                }
                else {
                    return true;
                }
            } catch(Exception $e) {
                error_log($e->getMessage());

                $player->clearMission();

                $system->message(
                    "There was an error with the mission - Your mission has been cancelled. <a href='$self_link'>Continue</a>"
                );
                $system->printMessage();
                return true;
            }
        }

        if($player->mission_stage['stage_id'] > $current_mission_stage_id) {
            if($mission->mission_type == Mission::TYPE_TEAM) {
                $mission_status = $mission->nextTeamStage($player->mission_stage['stage_id']);
            }
            else {
                $mission_status = $mission->nextStage($player->mission_stage['stage_id']);
            }
        }

        // Complete mission
        if($mission_status == Mission::STATUS_COMPLETE) {
            // Special mission
            if($mission->mission_type == Mission::TYPE_SPECIAL) {
                $player->clearMission();

                // Jonin exam
                if($mission->mission_id == 10) {
                    $player->exam_stage = 2;
                    $self_link = $system->router->base_url . '?id=1';

                    require_once("levelUp.php");
                    rankUp();
                    return true;
                }
            }
            // Team mission
            else if($mission->mission_type == Mission::TYPE_TEAM) {
                $player->addMoney($mission->money, "Team mission");
                $player->clearMission();

                $team_points = 2;
                // Process team rewards if this is the first completing player, then unset the mission ID
                if($player->team->mission_id) {
                    $system->query(
                        "UPDATE `teams` SET
						`points`=`points` + $team_points, `monthly_points`=`monthly_points` + $team_points,`mission_id`=0
						WHERE `team_id`={$player->team->id}"
                    );
                }

                echo "<table class='table'><tr><th>Current Mission</th></tr>
				<tr><td style='text-align:center;'><span style='font-weight:bold;'>$mission->name Complete</span><br />
				Your team has completed the mission.<br />
				You have been paid &yen;$mission->money.<br />
				Your team has received $team_points points.<br />
				<a href='$self_link'>Continue</a>
				</td></tr></table>";
            }
            // Clan mission
            else if($mission->mission_type == Mission::TYPE_CLAN) {
                $player->addMoney($mission->money, "Clan mission");
                $player->clearMission();
                $player->last_ai_ms = System::currentTimeMs();

                $point_gain = 1;
                $system->query(
                    "UPDATE `clans` SET `points`=`points`+$point_gain WHERE `clan_id`={$player->clan->id} LIMIT 1"
                );

                echo "<table class='table'><tr><th>Current Mission</th></tr>
				<tr><td style='text-align:center;'><span style='font-weight:bold;'>$mission->name Complete</span><br />
				You have completed your mission for clan {$player->clan->name}.<br />
				You have been paid &yen;$mission->money.<br />
				You have earned $point_gain reputation for your clan.<br />
				<a href='$self_link'>Continue</a>
				</td></tr></table>";
            }
            // Village/Survival mission
            else {
                echo "<table class='table'><tr><th>Current Mission</th></tr>
				<tr><td style='text-align:center;'><span style='font-weight:bold;'>$mission->name Complete</span><br />
				You have completed your mission.<br />";
                if($mission->mission_type == 5) {
                    $mission->money = $player->mission_stage['mission_money'];
                    echo sprintf(
                        "For your effort in defeating %d enemies, you have received &yen;%d.<br />",
                        $player->mission_stage['ai_defeated'], $mission->money
                    );
                }
                else {
                    echo "You have been paid &yen;$mission->money.<br />";
                }

                // Village reputation
                if($player->mission_rep_cd - time() <= 0) {
                    $rep_gain = $player->calMaxRepGain(Village::MISSION_GAINS[$mission->rank]);

                    if ($rep_gain > 0) {
                        $player->addRep($rep_gain);
                        $player->mission_rep_cd = time() + Village::ARENA_MISSION_CD;
                        echo "You have gained $rep_gain village reputation!<br />";
                    }
                }

                // check what mission rank for daily Task
                $all_mission_ranks = [0, 1, 2, 3, 4];
                $mission_rank = $all_mission_ranks[$mission->rank];
                foreach($player->daily_tasks as $task) {
                    if($task->activity == DailyTask::ACTIVITY_MISSIONS && $task->mission_rank == $mission_rank && !$task->complete) {
                        $task->progress++;
                    }
                }

                if(isset($player->missions_completed[$mission->rank])) {
                    $player->missions_completed[$mission->rank] += 1;
                }
                else {
                    $player->missions_completed[$mission->rank] = 1;
                }

                $player->addMoney($mission->money, "Village mission complete");
                $player->clearMission();
                $player->last_ai_ms = System::currentTimeMs();

                echo "<a href='$self_link'>Continue</a>
					</td></tr></table>";
            }
            $player->updateData();
        }
        // Display mission details
        else if($player->mission_id) {
            echo "<table class='table'><tr><th>Current Mission";
            if($player->mission_id != RankManager::JONIN_MISSION_ID) {
                echo " (<a href='$self_link&cancel_mission=1'>Abandon Mission</a>)";
            }
            echo "</th></tr>
			<tr><td style='text-align:center;'><span style='font-weight:bold;'>" .
                ($mission->mission_type == 3 ? '[' . $player->team->name . '] ' : '') . "$mission->name</span><br />" .
                $player->mission_stage['description'];

            // Display counts of team/solo mission
            if($mission->mission_type == 3 && $mission->team->mission_stage['count_needed']) {
                echo ' (' . $mission->team->mission_stage['count'] . '/' . $mission->team->mission_stage['count_needed'] . ' complete)';
            }
            else if(!empty($player->mission_stage['count_needed'])) {
                echo ' (' . $player->mission_stage['count'] . '/' . $player->mission_stage['count_needed'] . ' complete)';
            }

            // Display to battle link
            if($player->mission_stage['action_type'] == 'combat') {

                if($mission->mission_type == 5 && $player->mission_stage['action_type'] == 'combat') {
                    echo "<br /><a href='$self_link&continue=1'>Enter Combat</a> | <a href='$self_link&retreat=1'>Retreat</a>";
                }
                else {
                    echo "<br /><a href='$self_link'>Enter Combat</a>";
                }
            }

            echo "<br />
			</td></tr></table>";
        }

        return true;
    }
}

/**
 * @param BattleManager $battle
 * @param Mission       $mission
 * @param User          $player
 * @return bool|void
 * @throws Exception
 */
function processMissionBattleEnd(BattleManager|BattleManagerV2 $battle, Mission $mission, User $player): string {
    if(!$battle->isComplete()) {
        return true;
    }
    $opponent = $battle->opponent;

    $result_text = "";

    $player->battle_id = 0;

    $continue_mission = false;
    if (!empty($_GET['continue'])) {
        $continue_mission = boolval($_GET['continue']);
    }

    if($mission->mission_type == Mission::TYPE_SURVIVAL) {
        if ($player->mission_stage['round_complete'] && !$continue_mission) {
            echo "<table class='table'>
                <tr><th>Battle Results</th></tr>
                <tr><td>You have defeated your enemy. Either turn back now or push on.</td></tr>
            </table>";
        }
        else if($battle->isPlayerWinner() && !$player->mission_stage['round_complete']) {
            $money_gain = $mission->money;
            $level_difference = $player->level - $opponent->level;
            if($level_difference > 9) {
                $level_difference = 9;
            }
            $money_gain = round($money_gain * (1 - $level_difference * 0.05));
            if($money_gain < 5) {
                $money_gain = 5;
            }

            if($player->mission_stage['ai_defeated'] > 1 && $player->mission_stage['ai_defeated'] % 5 == 0){
                $player->mission_stage['stage_id'] += 1;
            }
            else if($player->mission_stage['stage_id'] > 2){
                $player->mission_stage['stage_id'] -= 1;
            }

            if ($player->location == $player->village_location) {
                $player->mission_stage['stage_id'] = 4;
            }

            $player->mission_stage['ai_defeated']++;
            $player->mission_stage['mission_money'] += $money_gain;
            $player->mission_stage['round_complete'] = true;
        }
        else if($battle->isOpponentWinner() || $battle->isDraw()) {
            $player->mission_stage['mission_money'] = round($player->mission_stage['mission_money'] * 0.75);
            $player->mission_stage['stage_id'] = 4;
            $player->moveToVillage();

            $result_text .= "You have been defeated.";
        }
        else {
            $player->clearMission();
            return $result_text;
        }
    }
    else if($battle->isPlayerWinner()) {
        $player->mission_stage['stage_id'] += 1;
    }
    else if($battle->isOpponentWinner()) {
        $result_text .= "You have been defeated. You have failed your mission.";

        $player->clearMission();

        $player->ai_losses++;
        $player->moveToVillage();
    }
    else if($battle->isDraw()) {
        $result_text .= "The battle ended in a draw. You have failed your mission.";

        $player->clearMission();
        $player->moveToVillage();
    }

    return $result_text;
}

function missionFightAPI(System $system, User $player): BattlePageAPIResponse {
    if(!$player->mission_id) {
        return new BattlePageAPIResponse(errors: [ "Player is not on a mission!"]);
    }
    if(!$player->battle_id) {
        return new BattlePageAPIResponse(errors: ["Player is not in battle!"]);
    }

    if($player->team) {
        $mission = new Mission($player->mission_id, $player, $player->team);
    }
    else {
        $mission = new Mission($player->mission_id, $player);
    }

    $response = new BattlePageAPIResponse();

    try {
        $battle = BattleManagerV2::init($system, $player, $player->battle_id);
        $battle->checkInputAndRunTurn();

        $response->battle_data = $battle->getApiResponse();

        if($battle->isComplete()) {
            $response->battle_result = processMissionBattleEnd($battle, $mission, $player);
        }
    } catch(Exception $e) {
        $response->errors[] = $e->getMessage();
    }

    return $response;
}
