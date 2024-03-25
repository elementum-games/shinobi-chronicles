<?php
/* arena.php
*/

use JetBrains\PhpStorm\ArrayShape;

function arena(): bool {
	global $system;
	global $player;
	global $self_link;

    $max_last_ai_ms = System::currentTimeMs() - System::ARENA_COOLDOWN;
    // 1.5 second cooldown on entering arena fight after a PvP
    $max_last_pvp_ms = System::currentTimeMs() - 1500;

    $arena_background = 'images/battle_backgrounds/FightingGrounds.jpg';

	if($player->exam_stage > 0) {
		$system->message("You cannot access this page during the exam!");
		$system->printMessage();
		return false;
	}
    if ($player->war_action_id > 0) {
        $system->message("You cannot access this page while taking a war action!");
        $system->printMessage();
        return false;
    }
    if ($player->special_mission_id > 0) {
        $system->message("You cannot access this page while in a special mission!");
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
                WHERE `rank` = {$ai_rank} AND `arena_enabled` = 1 ORDER BY `level` ASC"
        );
		if($system->db->last_num_rows == 0) {
			$system->message("No NPC opponents found!");
			$system->printMessage();
			return false;
		}

		$ai_opponents = array();

        if(!empty($_GET['difficulty'])) {
            // check if the current location disallows ai fights
            if ($player->rank_num > 2 && $player->current_location->location_id && $player->current_location->ai_allowed == 0) {
                $system->message('You cannot fight at this location');
            }
            else if ($player->last_ai_ms > $max_last_ai_ms) {
                $system->message("Please wait " . number_format(($player->last_ai_ms - $max_last_ai_ms) / 1000, 1) . " more seconds!");
            }
            else if ($player->last_pvp_ms > $max_last_pvp_ms) {
                $system->message("You just finished a PvP fight, please wait " . number_format(($player->last_pvp_ms - $max_last_pvp_ms) / 1000, 1) . " more seconds!");
            }
            else {
                try {
                    $ai_difficulty = $_GET['difficulty'];
                    $ai = getArenaOpponent($ai_difficulty, $player, $system);
                    $ai->loadData($player);
                    $ai->health = $ai->max_health;

                    $player->last_ai_ms = System::currentTimeMs();
                    if ($system->USE_NEW_BATTLES) {
                        BattleV2::start($system, $player, $ai, BattleV2::TYPE_AI_ARENA, battle_background_link: $arena_background);
                    } else {
                        Battle::start($system, $player, $ai, Battle::TYPE_AI_ARENA, battle_background_link: $arena_background);
                    }
                    $player->ai_cooldowns[$ai_difficulty] = NPC::AI_COOLDOWNS[$ai_difficulty] + time();

                    arena();
                    $player->log(User::LOG_ARENA, "Opponent {$ai->id} ({$ai->getName()})");
                    return true;
                } catch (RuntimeException $e) {
                    $system->message($e->getMessage());
                    $system->printMessage();
                }
            }
		}

        require 'templates/arena.php';
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
    $double_yen_gain_chance = $player->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_DOUBLE_BATTLE_YEN_CHANCE];
    $double_xp_gain_chance = $player->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_DOUBLE_BATTLE_XP_CHANCE];

    // Base chance at 100, goes down if fight is too short/lower level AI
    $stat_gain_chance = 100;

    $battle_result = "";

    if(!$battle->isComplete()) {
        return true;
    }
    else if($battle->isPlayerWinner()) {
        $stat_gain_display = false;
        $opponent = $battle->opponent;

        $money_gain = $battle->opponent->getMoney();

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
            more effectively.<br />';
            $stat_gain = TrainingManager::getAIStatGain($opponent->difficulty_level, $player->rank_num);
            if ($double_xp_gain_chance > 0 && mt_rand(1, 100) <= $double_xp_gain_chance) {
                $stat_gain *= 2;
                $stat_gain_display .= "Lucky! You gained double the amount of " . System::unSlug($stat_to_gain) . " experience (" . $stat_gain . ") from this battle!<br />";
            }
            $stat_gain_display .= $player->addStatGain($stat_to_gain, $stat_gain) . '.';
        }

        // Village Rep Gains
        $rep_gain_string = "";
        $rep_gain = $player->reputation->calcArenaReputation($opponent->difficulty_level, $player->rank_num);
        if($player->reputation->canGain(UserReputation::ACTIVITY_TYPE_PVE)) {
            $rep_gain = $player->reputation->addRep(
                amount: $rep_gain,
                activity_type: UserReputation::ACTIVITY_TYPE_PVE
            );
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
                $lucky_yen = false;
                if ($double_yen_gain_chance > 0 && mt_rand(1, 100) <= $double_yen_gain_chance) {
                    $money_gain *= 2;
                    $lucky_yen = true;
                }
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
        if ($money_gain > 0) {
            if ($lucky_yen) {
                $battle_result .= "Lucky! You gained double the amount of yen (" . $money_gain . ") from this battle!<br />";
            } else {
                $battle_result .= "You have claimed your prize of &yen;$money_gain.<br />";
            }
        }
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
        if ($player->daily_tasks->hasTaskType(DailyTask::ACTIVITY_ARENA)) {
            $player->daily_tasks->progressTask(DailyTask::ACTIVITY_ARENA, 1);
        }
        if($player->daily_tasks->hasTaskType(DailyTask::ACTIVITY_DAILY_PVE)) {
            $player->daily_tasks->progressTask(DailyTask::ACTIVITY_DAILY_PVE, $rep_gain);
        }
    }
    else if($battle->isOpponentWinner()) {
        $battle_result .= "You have been defeated.";

        $player->health = 5;
        $player->ai_losses++;
        //$player->moveToVillage();
        $player->battle_id = 0;
        $player->last_pvp_ms = System::currentTimeMs();
    }
    else if($battle->isDraw()) {
        $battle_result .= "The battle ended in a draw.";

        $stat_gain_display = false;
        $opponent = $battle->opponent;

        $money_gain = floor($battle->opponent->getMoney() / 2);

        if ($player->level > $opponent->level) {
            $level_difference = $player->level - $opponent->level;
            if ($level_difference > 9) {
                $level_difference = 9;
            }
            $money_gain = round($money_gain * (1 - $level_difference * 0.1));
            if ($money_gain < 5) {
                $money_gain = 5;
            }
        }

        // 5 levels below = -75% chance
        if ($opponent->level < $player->level) {
            $stat_gain_chance -= ($player->level - $opponent->level) * 15;
        }

        if (
            $player->total_stats < $player->rank->stat_cap
            && $stat_gain_chance >= mt_rand(1, 100)
            && $player->getTrainingStatForArena() != null
        ) {
            $stat_to_gain = $player->getTrainingStatForArena();

            $stat_gain_display = '<br />During the fight you realized a way to use your ' . System::unSlug($stat_to_gain) . ' a little
            more effectively.<br />';
            $stat_gain = TrainingManager::getAIStatGain($opponent->difficulty_level, $player->rank_num);
            $stat_gain = floor($stat_gain / 2);
            $stat_gain_display .= $player->addStatGain($stat_to_gain, $stat_gain) . '.';
        }

        // Village Rep Gains
        $rep_gain_string = "";
        $rep_gain = floor($player->reputation->calcArenaReputation($opponent->difficulty_level, $player->rank_num) / 2);
        if ($player->reputation->canGain(UserReputation::ACTIVITY_TYPE_PVE)) {
            $rep_gain = $player->reputation->addRep(
                amount: $rep_gain,
                activity_type: UserReputation::ACTIVITY_TYPE_PVE
            );
            if ($rep_gain > 0) {
                $player->mission_rep_cd = time() + UserReputation::ARENA_MISSION_CD;
                $rep_gain_string = "Fellow " . $player->village->name . " Shinobi learned from your battle, earning you $rep_gain Reputation.<br />";
            }
        }

        // TEAM BOOST NPC GAINS
        if ($player->team != null) {
            $boost_percent = $player->team->getAIMoneyBoostAmount();
            if ($boost_percent != null) {
                $boost_amount = ceil($boost_percent * $money_gain);
                $money_gain += $boost_amount;
            }
        }

        $extra_yen = 0;
        $append_message = "";
        if ($player->special_items) {
            foreach ($player->special_items as $item) {
                if ($item->effect == 'yen_boost') {
                    $amount = ceil($money_gain * ($item->effect_amount / 100));
                    $extra_yen += $amount;
                    $append_message .= "Your $item->name has provided you with an extra &yen;$amount.<br />";
                }
            }
        }

        $battle_result = "This battle ended in a draw.<br />";
        if ($rep_gain_string != "") {
            $battle_result .= $rep_gain_string;
        }
        $battle_result .= "You split the prize of &yen;$money_gain with your opponent.<br />";
        if ($append_message != "") {
            $battle_result .= $append_message;
        }
        if ($stat_gain_display) {
            $battle_result .= $stat_gain_display;
        }

        $player->addMoney(($money_gain + $extra_yen), 'arena');

        $player->health = 5;
        //$player->moveToVillage();
        $player->battle_id = 0;
        $player->last_pvp_ms = System::currentTimeMs();

        // Daily Tasks
        if ($player->daily_tasks->hasTaskType(DailyTask::ACTIVITY_DAILY_PVE)) {
            $player->daily_tasks->progressTask(DailyTask::ACTIVITY_DAILY_PVE, $rep_gain);
        }
    }

    return $battle_result;
}

/**
 * @throws RuntimeException
 */
function getArenaOpponent(string $difficulty_level, User $player, System $system): NPC {
    if (!empty($player->ai_cooldowns[$difficulty_level]) && time() < $player->ai_cooldowns[$difficulty_level]) {
        throw new RuntimeException("Please wait " . System::timeFormat($player->ai_cooldowns[$difficulty_level] - time()) . "s to start another battle of this difficulty.");
    }
    switch ($difficulty_level) {
        case NPC::DIFFICULTY_EASY:
            // random easy AI - for now just training dummy
            $ai_result = $system->db->query("
                SELECT `ai_id` FROM `ai_opponents`
                WHERE `rank` = {$player->rank_num}
                AND `arena_enabled` = 1
                AND `difficulty_level` = '{$difficulty_level}'
                ORDER BY RAND()
                LIMIT 1
            ");
            $ai_result = $system->db->fetch($ai_result);
            if (empty($ai_result['ai_id'])) {
                throw new RuntimeException("No AI opponents available.");
            }
            $ai_opponent = new NPC($system, $ai_result['ai_id']);
            return $ai_opponent;
        case NPC::DIFFICULTY_NORMAL:
            $ai_opponents = [];
            // get nearest non-scaling Normal AI of player rank within 5 levels
            $ai_result = $system->db->query("SELECT `ai_id` FROM `ai_opponents`
                WHERE `rank` = {$player->rank_num}
                AND `arena_enabled` = 1
                AND `difficulty_level` = '{$difficulty_level}'
                AND `scaling` = 0
                AND `level` BETWEEN {$player->level} - 5 AND {$player->level}
                ORDER BY ABS({$player->level} - `level`)
                LIMIT 1
            ");
            $ai_result = $system->db->fetch($ai_result);
            if (isset($ai_result['ai_id'])) {
                $ai_opponents[] = $ai_result['ai_id'];
            }
            // get scaling Normal AI of player rank
            $ai_result = $system->db->query("SELECT `ai_id` FROM `ai_opponents`
                WHERE `rank` = {$player->rank_num}
                AND `arena_enabled` = 1
                AND `difficulty_level` = '{$difficulty_level}'
                AND `scaling` = 1
                AND `level` <= {$player->level}
            ");
            $ai_result = $system->db->fetch_all($ai_result);
            foreach ($ai_result as $ai) {
                $ai_opponents[] = $ai['ai_id'];
            }
            // select random AI
            if (empty($ai_opponents)) {
                throw new RuntimeException("No AI opponents available.");
            }
            $ai_opponent = new NPC($system, $ai_opponents[array_rand($ai_opponents)]);
            return $ai_opponent;
        case NPC::DIFFICULTY_HARD:
            // random Hard AI at player rank
            $ai_result = $system->db->query("SELECT `ai_id` FROM `ai_opponents`
                WHERE `rank` = {$player->rank_num}
                AND `arena_enabled` = 1
                AND `difficulty_level` = '{$difficulty_level}'
                ORDER BY RAND() LIMIT 1
            ");
            $ai_result = $system->db->fetch($ai_result);
            if (empty($ai_result['ai_id'])) {
                throw new RuntimeException("No AI opponents available.");
            }
            $ai_opponent = new NPC($system, $ai_result['ai_id']);
            return $ai_opponent;
        default:
            throw new RuntimeException("Invalid difficulty selection.");
    }
}