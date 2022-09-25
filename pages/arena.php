<?php
/* arena.php
*/

use JetBrains\PhpStorm\ArrayShape;

function arena(): bool {
	global $system;
	global $player;
	global $self_link;

  $fight_timer = 4 * 1000;

	if($player->exam_stage > 0) {
		$system->message("You cannot access this page during the exam!");
		$system->printMessage();
		return false;
	}

	if($player->battle_id) {
        arenaFight();
	}
	else {
		$result = $system->query("SELECT `ai_id`, `name`, `level` FROM `ai_opponents`
			WHERE `rank` ='$player->rank_num' ORDER BY `level` ASC");

		// Addition by Kengetsu - Get access to NPC if rank is higher than public max.
		if($player->rank_num > System::SC_MAX_RANK) {
			$result = $system->query("SELECT `ai_id`, `name`, `level` FROM `ai_opponents`
			WHERE `rank` ='" . System::SC_MAX_RANK . "' ORDER BY `level` ASC");
		}
		//End
		if($system->db_last_num_rows == 0) {
			$system->message("No NPC opponents found!");
			$system->printMessage();
			return false;
		}
		$ai_opponents = array();
		while($row = $system->db_fetch($result)) {
			$ai_opponents[$row['ai_id']] = $row;
		}
		$fight_start = false;
		if(!empty($_GET['fight'])) {

            // check if the current location disallows ai fights
            if ($player->current_location->location_id && $player->current_location->ai_allowed == 0) {
                $system->message('You cannot fight at this location');
            }
            else if($player->last_ai_ms > System::currentTimeMs() - $fight_timer) {
				$system->message("Please wait " . ($player->last_ai_ms - (System::currentTimeMs() - $fight_timer) / 1000) . " more seconds!");
			}
			else if(isset($ai_opponents[$_GET['fight']])) {

                try {
                    $ai_id = $_GET['fight'];
                    $ai = new NPC($system, $ai_id);
                    $ai->loadData();
                    $ai->health = $ai->max_health;

                    $player->last_ai_ms = System::currentTimeMs();
                    Battle::start($system, $player, $ai, Battle::TYPE_AI_ARENA);

                    arena();
                    $player->log(User::LOG_ARENA, "Opponent {$ai->id} ({$ai->getName()})");
                    return true;
                } catch(Exception $e) {
                    $system->message("Invalid opponent!");
                    $system->printMessage();
                }
			}
			else {
				$system->message("Invalid opponent!");
				$system->printMessage();
			}
		}

        $system->printMessage();
        echo "<table class='table'><tr><th>Choose Opponent</th></tr>
        <tr><td style='text-align: center;'>
        Welcome to the Arena. Here you can fight against various opponents for cash prizes. Please select your opponent below:
        </td></tr>
        <tr><td style='text-align: center;'>";
        foreach($ai_opponents as $ai) {
            echo "<a href='$self_link&fight={$ai['ai_id']}'>
                 <p class='button' style='margin-top:5px;'>" . $ai['name'] .
                    " <span style='font-weight:normal;'>(Level {$ai['level']})</span></p></a><br />";
        }
        echo "</td></tr></table>";
	}

    return true;
}

function arenaFight(): bool {
    global $system;
    global $player;

    // Base chance at 100, goes down if fight is too short/lower level AI
    $stat_gain_chance = 100;

    try {
        $battle = BattleManager::init($system, $player, $player->battle_id);
        $battle->checkInputAndRunTurn();

        $battle->renderBattle();

        if($battle->isComplete()) {
            $battle_result = processArenaBattleEnd($battle, $player);
            echo "<table class='table'><tr><th>Battle Results</th></tr>
            <tr><td>" . $battle_result . "</td></tr></table>";
        }
    } catch(Exception $e) {
        $system->message($e->getMessage());
        $system->printMessage();
        return false;
    }
    return true;
}

#[ArrayShape(['errors' => "array", 'battle_result' => "string"])]
function arenaFightAPI(System $system, User $player): BattlePageAPIResponse {
    $response = new BattlePageAPIResponse();

    try {
        $battle = BattleManager::init(system: $system, player: $player, battle_id: $player->battle_id, spectate: true);
        $battle->checkInputAndRunTurn();

        $response->battle_data = $battle->getApiResponse();
        if($system->message) {
            $response->errors[] = $system->message;
        }

        if($battle->isComplete()) {
            $response->battle_result = processArenaBattleEnd($battle, $player);
        }
    } catch(Exception $e) {
        $response->errors[] = $e->getMessage();
    }

    return $response;
}

/**
 * @throws Exception
 */
function processArenaBattleEnd(BattleManager $battle, User $player): string {
    $stat_gain_chance = 26;
    $battle_result = "";

    if(!$battle->isComplete()) {
        return true;
    }
    else if($battle->isPlayerWinner()) {
        $stat_gain_display = false;
        $opponent = $battle->opponent;

        $money_gain = $battle->opponent->money;

            if($player->level > $opponent->level) {
                $level_difference = $player->level - $opponent->level;
                if($level_difference > 9) {
                    $level_difference = 9;
                }
                $money_gain = round($money_gain * (1 - $level_difference * 0.1));
                if($money_gain < 5) {
                    $money_gain = 5;
                }
            }
            // Stat gain
            $counts = array(
                'bloodline' => 0,
                'ninjutsu' => 0,
                'taijutsu' => 0,
                'genjutsu' => 0
            );
            $total_count = 0;
            if(is_array($battle->player_jutsu_used)) {
                foreach(($battle->player_jutsu_used) as $id => $jutsu) {
                    if(strpos($id, 'BL_J') !== false) {
                        $counts['bloodline'] += $jutsu['count'];
                    }
                    else if($jutsu['jutsu_type'] == 'ninjutsu') {
                        $counts['ninjutsu'] += $jutsu['count'];
                    }
                    else if($jutsu['jutsu_type'] == 'taijutsu') {
                        $counts['taijutsu'] += $jutsu['count'];
                    }
                    else if($jutsu['jutsu_type'] == 'genjutsu') {
                        $counts['genjutsu'] += $jutsu['count'];
                    }
                    $total_count += $jutsu['count'];
                }
            }

            // 5 levels below = -75% chance
            if($opponent->level < $player->level) {
                $stat_gain_chance -= ($player->level - $opponent->level) * 15;
            }

            if($player->total_stats < $player->rank->stat_cap
                && $stat_gain_chance >= mt_rand(1, 100)
                && $player->getTrainingStatForArena() != null
            ) {
                $stat_to_gain = $player->getTrainingStatForArena();

                $player->{$stat_to_gain} += 1;
                $player->exp += 10;

                $stat_gain_display = '<br />During the fight you realized a way to use your ' . System::unSlug($stat_to_gain) . ' a little
                more effectively.
                <br />You have gained 1 ' . System::unSlug($stat_to_gain) . '.';
            }

        // TEAM BOOST NPC GAINS
        if($player->team != null) {
            $boost_percent = $player->team->getAIMoneyBoostAmount();
            if($boost_percent != null) {
                $boost_amount = ceil($boost_percent * $money_gain);
                $money_gain += $boost_amount;
            }
        }

            $player->addMoney($money_gain, 'arena');

        $battle_result = "You have defeated your arena opponent.<br />
			You have claimed your prize of &yen;$money_gain.";
        if($stat_gain_display) {
            $battle_result .=  $stat_gain_display;
        }
        $player->ai_wins++;
        $player->battle_id = 0;
        $player->last_pvp = time();

        // Daily Tasks
        foreach ($player->daily_tasks as $task) {
            if (!$task->complete && $task->activity == DailyTask::ACTIVITY_ARENA && $task->sub_task == DailyTask::SUB_TASK_WIN_FIGHT) {
                $task->progress++;
            }
        }
    }
    else if($battle->isOpponentWinner()) {
        $battle_result .= "You have been defeated.";

        $player->health = 5;
        $player->ai_losses++;
        $player->moveToVillage();
        $player->battle_id = 0;
        $player->last_pvp_ms = System::currentTimeMs();

        // Daily Tasks
        foreach ($player->daily_tasks as &$task) {
            if (!$task->complete && $task->activity == DailyTask::ACTIVITY_ARENA && $task->sub_task == DailyTask::SUB_TASK_COMPLETE) {
                $task->progress++;
            }
        }
    }
    else if($battle->isDraw()) {
        $battle_result .= "The battle ended in a draw. You receive no reward.";

        $player->health = 5;
        $player->moveToVillage();
        $player->battle_id = 0;
        $player->last_pvp_ms = System::currentTimeMs();

        // Daily Tasks
        foreach ($player->daily_tasks as $task) {
            if (!$task->complete && $task->activity == DailyTask::ACTIVITY_ARENA && $task->sub_task == DailyTask::SUB_TASK_COMPLETE) {
                $task->progress++;
            }
        }
    }

    return $battle_result;
}
