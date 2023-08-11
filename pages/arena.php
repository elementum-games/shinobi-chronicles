<?php
/* arena.php
*/

use JetBrains\PhpStorm\ArrayShape;

function arena(): bool {
	global $system;
	global $player;
	global $self_link;

    $fight_timer = System::ARENA_COOLDOWN;

	if($player->exam_stage > 0) {
		$system->message("You cannot access this page during the exam!");
		$system->printMessage();
		return false;
	}

	if($player->battle_id) {
        arenaFight();
	}
	else {
        $ai_rank = min($player->rank_num, System::SC_MAX_RANK);
        $result = $system->db->query(
            "SELECT `ai_id`, `name`, `level` FROM `ai_opponents`
                WHERE `rank` = {$ai_rank} AND `money` > 0 ORDER BY `level` ASC"
        );
		if($system->db->last_num_rows == 0) {
			$system->message("No NPC opponents found!");
			$system->printMessage();
			return false;
		}

		$ai_opponents = array();
		while($row = $system->db->fetch($result)) {
			$ai_opponents[$row['ai_id']] = $row;
		}

		if(!empty($_GET['fight'])) {
            $max_last_ai_ms = System::currentTimeMs() - $fight_timer;

            // check if the current location disallows ai fights
            if ($player->rank_num > 2 && $player->current_location->location_id && $player->current_location->ai_allowed == 0) {
                $system->message('You cannot fight at this location');
            }
            else if($player->last_ai_ms > $max_last_ai_ms) {
				$system->message("Please wait " . ceil(($player->last_ai_ms - $max_last_ai_ms) / 1000) . " more seconds!");
			}
			else if(isset($ai_opponents[$_GET['fight']])) {
                try {
                    $ai_id = $_GET['fight'];
                    $ai = new NPC($system, $ai_id);
                    $ai->loadData();
                    $ai->health = $ai->max_health;

                    $player->last_ai_ms = System::currentTimeMs();
                    if($system->USE_NEW_BATTLES) {
                        BattleV2::start($system, $player, $ai, BattleV2::TYPE_AI_ARENA);
                    }
                    else {
                        Battle::start($system, $player, $ai, Battle::TYPE_AI_ARENA);
                    }

                    arena();
                    $player->log(User::LOG_ARENA, "Opponent {$ai->id} ({$ai->getName()})");
                    return true;
                } catch(RuntimeException $e) {
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
        Welcome to the Arena. Here you can fight against various opponents for cash prizes. Please select your opponent below:";
	if(!$player->reputation->canGain(true)) {
	$remaining = $player->mission_rep_cd - time();
	echo "<br /><br />You can gain village reputation in: <div id='rep_cd' style='display: inline-block'>"
	    . System::timeRemaining($remaining) . "</div>
		<script type='text/javascript'>countdownTimer($remaining, 'rep_cd', false);</script>";
    	}
        echo "</td></tr>
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

/**
 * @throws RuntimeException
 */
function arenaFight(): bool {
    global $system;
    global $player;

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
            $battle_result = processArenaBattleEnd($battle, $player);
            echo "<table class='table'><tr><th>Battle Results</th></tr>
            <tr><td style='text-align: center;'>" . $battle_result . "</td></tr></table>";
        }
    } catch(RuntimeException $e) {
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
        $battle = BattleManagerV2::init(system: $system, player: $player, battle_id: $player->battle_id);
        $battle->checkInputAndRunTurn();

        $response->battle_data = $battle->getApiResponse();
        if($system->message) {
            $response->errors[] = $system->message;
        }

        if($battle->isComplete()) {
            $response->battle_result = processArenaBattleEnd($battle, $player);
        }
    } catch(RuntimeException $e) {
        $response->errors[] = $e->getMessage();
    }

    return $response;
}

/**
 * @throws RuntimeException
 */
function processArenaBattleEnd(BattleManager|BattleManagerV2 $battle, User $player): string {
    // Base chance at 100, goes down if fight is too short/lower level AI
    $stat_gain_chance = 100;

    $battle_result = "";

    if(!$battle->isComplete()) {
        return true;
    }
    else if($battle->isPlayerWinner()) {
        $stat_gain_display = false;
        $opponent = $battle->opponent;

        $multiplier = $battle->opponent->getMoney();
        $money_gain = $player->calcPlayerMoneyGain($multiplier);

        // Reduce money gains based on level
        if($player->level > $opponent->level) {
            $level_difference = $player->level - $opponent->level;
            if($level_difference > 9) {
                $level_difference = 9;
            }
            $money_gain = round($money_gain * (1-$level_difference*0.1));
            if($money_gain < 5) {
                $money_gain = 5;
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

            $stat_gain_display = '<br />During the fight you realized a way to use your ' . System::unSlug($stat_to_gain) . ' a little
            more effectively.';
            $stat_gain_display .= $player->addStatGain($stat_to_gain, 1) . '.';
        }

        // Village Rep Gains
        $rep_gain_string = "";
        if($player->reputation->canGain(true)) {
            $rep_gain = $player->reputation->addRep($player->reputation->calcArenaReputation($player->level, $opponent->level));
            if($rep_gain > 0) {
                $player->mission_rep_cd = time() + UserReputation::ARENA_MISSION_CD;
                $rep_gain_string = "Fellow " . $player->village->name . " Shinobi learned from your battle, earning you $rep_gain Reputation.<br />";
            }
        }

        // TEAM BOOST NPC GAINS
        if($player->team != null) {
            $boost_percent = $player->team->getAIMoneyBoostAmount();
            if($boost_percent != null) {
                $boost_amount = ceil($boost_percent * $money_gain);
                $money_gain += $boost_amount;
            }
        }

        $extra_yen = 0;
        $append_message = "";
        if($player->special_items) {
            foreach($player->special_items as $item) {
                if($item->effect == 'yen_boost') {
                    $amount = ceil($money_gain * ($item->effect_amount/100));
                    $extra_yen += $amount;
                    $append_message .= "Your $item->name has provided you with an extra &yen;$amount.<br />";
                }
            }
        }

        $battle_result = "You have defeated your arena opponent.<br />";
        if($rep_gain_string != "") {
            $battle_result .= $rep_gain_string;
        }
		$battle_result .= "You have claimed your prize of &yen;$money_gain.<br />";
        if($append_message != "") {
            $battle_result .= $append_message;
        }
        if($stat_gain_display) {
            $battle_result .=  $stat_gain_display;
        }

        $player->addMoney(($money_gain + $extra_yen), 'arena');
        $player->ai_wins++;
        $player->battle_id = 0;
        $player->last_pvp_ms = System::currentTimeMs();

        // Daily Tasks
        if($player->daily_tasks->hasTaskType(DailyTask::ACTIVITY_ARENA)) {
            $player->daily_tasks->progressTask(DailyTask::ACTIVITY_ARENA, 1);
        }
    }
    else if($battle->isOpponentWinner()) {
        $battle_result .= "You have been defeated.";

        $player->health = 5;
        $player->ai_losses++;
        $player->moveToVillage();
        $player->battle_id = 0;
        $player->last_pvp_ms = System::currentTimeMs();
    }
    else if($battle->isDraw()) {
        $battle_result .= "The battle ended in a draw. You receive no reward.";

        $player->health = 5;
        $player->moveToVillage();
        $player->battle_id = 0;
        $player->last_pvp_ms = System::currentTimeMs();
    }

    return $battle_result;
}
