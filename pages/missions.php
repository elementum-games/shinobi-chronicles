<?php

require_once __DIR__ . "/../classes/achievements/AchievementsManager.php";
require_once __DIR__ . '/../classes/notification/NotificationManager.php';
require_once __DIR__ . '/../classes/RankManager.php';

/**
 * @throws RuntimeException
 */
function missions(): bool {
	global $system;
	global $player;
	global $self_link;

    $RANK_NAMES = RankManager::fetchNames($system);

	if($player->mission_id) {
        runActiveMission();
        return true;
	}

	$max_mission_rank = Mission::maxMissionRank($player->rank_num);

	$result = $system->db->query(
        "SELECT `mission_id`, `name`, `rank` FROM `missions` WHERE `mission_type`=1 OR `mission_type`=5 AND `rank` <= $max_mission_rank"
    );
	if($system->db->last_num_rows == 0) {
		$system->message("No missions available!");
		$system->printMessage();
		return false;
	}

	$missions = array();
	while($row = $system->db->fetch($result)) {
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
        $mission_rank = $_GET['rank'];
		try {
            // TEMP Event Missions
            if (isset($_GET['mission_type'])) {
                if ($_GET['mission_type'] == "event") {
                    if ($system->event == null) {
                        throw new RuntimeException("Event not active!");
                    }

                    $result = $system->db->query(
                        "SELECT `mission_id`, `name` FROM `missions` WHERE `mission_type` = " . Mission::TYPE_EVENT
                    );
                    if ($system->db->last_num_rows == 0) {
                        $system->message("No missions available!");
                        $system->printMessage();
                        return false;
                    }
                    $event_missions = array();
                    while ($row = $system->db->fetch($result)) {
                        $event_missions[$row['mission_id']] = $row;
                    }
                    Mission::start($player, $mission_id);
                    $player->log(User::LOG_MISSION, "Mission ID #{$mission_id}");

                    // Create notification
                    if ($player->mission_stage['action_type'] == 'travel') {
                        $mission_location = TravelCoords::fromDbString($player->mission_stage['action_data']);
                        $new_notification = new MissionNotificationDto(
                            type: "mission",
                            message: $event_missions[$mission_id]['name'] . ": Travel to " . $mission_location->x . ":" . $mission_location->y,
                            user_id: $player->user_id,
                            created: time(),
                            mission_rank: "E",
                            alert: false,
                        );
                        NotificationManager::createNotification($new_notification, $system, NotificationManager::UPDATE_REPLACE);
                    } else {
                        $new_notification = new MissionNotificationDto(
                            type: "mission",
                            message: $event_missions[$mission_id]['name'] . " in progress",
                            user_id: $player->user_id,
                            created: time(),
                            mission_rank: "E",
                            alert: false,
                        );
                        NotificationManager::createNotification($new_notification, $system, NotificationManager::UPDATE_REPLACE);
                    }

                    missions();
                    return true;
                }
                if ($_GET['mission_type'] == "faction") {
                    $result = $system->db->query(
                        "SELECT `mission_id`, `name` FROM `missions` WHERE `mission_type` = " . Mission::TYPE_FACTION
                    );
                    if ($system->db->last_num_rows == 0) {
                        $system->message("No missions available!");
                        $system->printMessage();
                        return false;
                    }
                    $faction_missions = array();
                    while ($row = $system->db->fetch($result)) {
                        $faction_missions[$row['mission_id']] = $row;
                    }
                    Mission::start($player, $mission_id);
                    $player->log(User::LOG_MISSION, "Mission ID #{$mission_id}");

                    // Create notification
                    if ($player->mission_stage['action_type'] == 'travel') {
                        $mission_location = TravelCoords::fromDbString($player->mission_stage['action_data']);
                        $new_notification = new MissionNotificationDto(
                            type: "mission",
                            message: $faction_missions[$mission_id]['name'] . ": Travel to " . $mission_location->x . ":" . $mission_location->y,
                            user_id: $player->user_id,
                            created: time(),
                            mission_rank: "F",
                            alert: false,
                        );
                        NotificationManager::createNotification($new_notification, $system, NotificationManager::UPDATE_REPLACE);
                    } else {
                        $new_notification = new MissionNotificationDto(
                            type: "mission",
                            message: $faction_missions[$mission_id]['name'] . " in progress",
                            user_id: $player->user_id,
                            created: time(),
                            mission_rank: "F",
                            alert: false,
                        );
                        NotificationManager::createNotification($new_notification, $system, NotificationManager::UPDATE_REPLACE);
                    }

                    missions();
                    return true;
                }
            } else {
                // random mission logic
                if (!isset($mission_rank)) {
                    throw new RuntimeException("Invalid mission!");
                }
                $filtered_missions = array_filter($missions, function ($mission) use ($mission_rank) {
                    return $mission['rank'] === $mission_rank;
                });
                $filtered_missions = array_values($filtered_missions);
                $random_index = array_rand($filtered_missions);
                $mission_id = $filtered_missions[$random_index]['mission_id'];

                Mission::start($player, $mission_id);
                $player->log(User::LOG_MISSION, "Mission ID #{$mission_id}");

                // Create notification
                require_once __DIR__ . '/../classes/notification/NotificationManager.php';
                if ($player->mission_stage['action_type'] == 'travel') {
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
                } else {
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
            }
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
    if(!$player->reputation->canGain(true)) {
        $remaining = $player->mission_rep_cd - time();
        echo "<br /><br />You can gain village reputation in: <div id='rep_cd' style='display: inline-block'>"
            . System::timeRemaining($remaining) . "</div>
        <script type='text/javascript'>countdownTimer($remaining, 'rep_cd', false);</script>";
    }
    echo "</td></tr>
	<tr><td style='text-align:center;'>";
    echo "<a href='$self_link&start_mission=1&rank=$view'><p class='button' style='margin:5px;'>" . Mission::$rank_names[$view] . "</p></a><br />";
	echo "</td></tr></table>";

    return true;
}

/**
 * @throws RuntimeException
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
                    throw new RuntimeException("Couldn't load opponent for mission!");
                }
                $opponent->loadData();

                // display mission details
                echo "<table class='table' style='width: 90%'><tr><th>Current Mission</th></tr>
			    <tr><td style='text-align:center;'><span style='font-weight:bold;'>" .
                    ($mission->mission_type == 3 ? '[' . $player->team->name . '] ' : '') . "$mission->name</span><br />" .
                    $player->mission_stage['description'] . "</td></tr></table>";

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
                        echo "<table class='table' style='text-align:center'>
                            <tr><th>Battle Results</th></tr>
                            <tr><td>{$result}</td></tr>
                        </table>";
                    }
                }
                else {
                    return true;
                }
            } catch(RuntimeException $e) {
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
                // Rewards
                echo Mission::processRewards($mission, $player, $system);
                $player->addMoney($mission->money, "Team mission");
                $player->clearMission();

                $team_points = 2;
                // Process team rewards if this is the first completing player, then unset the mission ID
                if($player->team->mission_id) {
                    $system->db->query(
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
                // Rewards
                echo Mission::processRewards($mission, $player, $system);
                $player->addMoney($mission->money, "Clan mission");
                $player->clearMission();
                $player->last_ai_ms = System::currentTimeMs();

                $point_gain = 1;
                $system->db->query(
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
            // Faction mission
            else if ($mission->mission_type == Mission::TYPE_FACTION) {
                echo "<table class='table'><tr><th>Current Mission</th></tr>
			    <tr><td style='text-align:center;'><span style='font-weight:bold;'>$mission->name Complete</span><br />
			    You have completed your mission.<br />";

                // Rewards
                echo Mission::processRewards($mission, $player, $system);
                $player->clearMission();
                $player->last_ai_ms = System::currentTimeMs();

                echo "<a href='$self_link'>Continue</a>
				</td></tr></table>";
            }
            // Default
            else {
                echo "<table class='table'><tr><th>Current Mission</th></tr>
			<tr><td style='text-align:center;'><span style='font-weight:bold;'>$mission->name Complete</span><br />
			You have completed your mission.<br />";
                if ($mission->mission_type == 5) {
                    $mission->money = $player->mission_stage['mission_money'];
                    echo sprintf(
                        "For your effort in defeating %d enemies, you have received &yen;%d.<br />",
                        $player->mission_stage['ai_defeated'], $mission->money
                    );
                } else {
                    echo "You have been paid &yen;$mission->money.<br />";
                }

                // Village reputation
                if ($player->reputation->canGain(true)) {
                    $rep_gain = $player->reputation->addRep(UserReputation::MISSION_GAINS[$mission->rank]);
                    if ($rep_gain > 0) {
                        $player->mission_rep_cd = time() + UserReputation::ARENA_MISSION_CD;
                        echo "You have gained $rep_gain village reputation!<br />";
                    }
                }

                // check what mission rank for daily Task
                $all_mission_ranks = [0, 1, 2, 3, 4];
                $mission_rank = $all_mission_ranks[$mission->rank];
                if($player->daily_tasks->hasTaskType(DailyTask::ACTIVITY_MISSIONS)) {
                    $player->daily_tasks->progressTask(DailyTask::ACTIVITY_MISSIONS, 1, $mission_rank);
                }

                if (isset($player->missions_completed[$mission->rank])) {
                    $player->missions_completed[$mission->rank] += 1;
                } else {
                    $player->missions_completed[$mission->rank] = 1;
                }

                // Rewards
                echo Mission::processRewards($mission, $player, $system);
                $player->addMoney($mission->money, "Village mission complete");
                $player->clearMission();
                $player->last_ai_ms = System::currentTimeMs();

                echo "<a href='$self_link'>Continue</a>
				</td></tr></table>";

                AchievementsManager::handleMissionCompleted($system, $player, $mission);
                $player->updateData();
            }
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
                    try {
                        // monster id
                        $opponent = new NPC($system, $player->mission_stage['action_data']);
                        if (!$opponent) {
                            throw new RuntimeException("Couldn't load opponent for mission!");
                        }
                        $opponent->loadData();

                        // Initialize start of battle stuff
                        if (!$player->battle_id) {
                            if ($system->USE_NEW_BATTLES) {
                                BattleV2::start($system, $player, $opponent, Battle::TYPE_AI_MISSION);
                            } else {
                                Battle::start($system, $player, $opponent, Battle::TYPE_AI_MISSION);
                            }
                        }

                        if ($system->USE_NEW_BATTLES) {
                            $battle = BattleManagerV2::init($system, $player, $player->battle_id);
                        } else {
                            $battle = BattleManager::init($system, $player, $player->battle_id);
                        }
                    } catch (RuntimeException $e) {
                        error_log($e->getMessage());

                        $player->clearMission();

                        $system->message(
                            "There was an error with the mission - Your mission has been cancelled. <a href='$self_link'>Continue</a>"
                        );
                        $system->printMessage();
                        return true;
                    }
                }
            }

            echo "<br />
			</td></tr></table>";
        }

        return true;
    }
}

function processMissionBattleStart(): bool
{
}

/**
 * @param BattleManager $battle
 * @param Mission       $mission
 * @param User          $player
 * @return bool|void
 * @throws RuntimeException
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


    else if($battle->isPlayerWinner()) {
        $player->mission_stage['stage_id'] += 1;
        $result_text .= "You have defeated your opponent!";
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
    } catch(RuntimeException $e) {
        $response->errors[] = $e->getMessage();
    }

    return $response;
}