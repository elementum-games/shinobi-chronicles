<?php

function war() {
    global $system;
    global $player;

    if (!$system->war_enabled) {
        $system->message("You do not have access to this page!");
        $system->printMessage();
        return;
    }

    if ($player->battle_id) {
        displayBattle();
    }
}

/**
 * @throws RuntimeException
 */
function displayBattle(): bool
{
    global $system;
    global $player;

    try {
        if ($system->USE_NEW_BATTLES) {
            $battle = BattleManagerV2::init($system, $player, $player->battle_id);
        } else {
            $battle = BattleManager::init($system, $player, $player->battle_id);
        }

        $battle->checkInputAndRunTurn();

        $battle->renderBattle();

        if ($battle->isComplete()) {
            $battle_result = processWarBattleEnd($battle, $player);
            echo "<table class='table'><tr><th>Battle Results</th></tr>
            <tr><td style='text-align: center;'>" . $battle_result . "</td></tr></table>";
        }
    } catch (RuntimeException $e) {
        $system->message($e->getMessage());
        $system->printMessage();
        return false;
    }
    return true;
}

function processWarBattleEnd($battle, User $player): string {
    global $system;

    $double_yen_gain_chance = $player->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_DOUBLE_BATTLE_YEN_CHANCE];
    $double_xp_gain_chance = $player->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_DOUBLE_BATTLE_XP_CHANCE];

    // Base chance at 100, goes down if fight is too short/lower level AI
    $stat_gain_chance = 100;

    $battle_result = "";
    $patrol_id = $battle->getPatrolId();
    $warManager = new WarManager($system, $player);

    if (!$battle->isComplete()) {
        return true;
    } else if ($battle->isPlayerWinner()) {
        $stat_gain_display = false;
        $opponent = $battle->opponent;

        $money_gain = $battle->opponent->getMoney();
        if ($double_yen_gain_chance > 0 && mt_rand(1, 100) <= $double_yen_gain_chance) {
            $money_gain *= 2;
            $money_gain_string .= "Lucky! You gained double the amount of yen (" . $money_gain . ") from this battle!<br />";
        } else {
            $money_gain_string = "You gained " . $money_gain . " yen.<br />";
        }

        if ($player->level > $opponent->level) {
            $level_difference = $player->level - $opponent->level;
            if ($level_difference > 9) {
                $level_difference = 9;
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

            $stat_gain_display = 'During the fight you realized a way to use your ' . System::unSlug($stat_to_gain) . ' a little
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
        if ($player->reputation->canGain(UserReputation::ACTIVITY_TYPE_WAR)) {
            $rep_gain = $player->reputation->addRep(
                $player->reputation->calcArenaReputation($opponent->difficulty_level, $player->rank_num),
                UserReputation::ACTIVITY_TYPE_WAR
            );
            if ($rep_gain > 0) {
                $player->mission_rep_cd = time() + UserReputation::ARENA_MISSION_CD;
                $rep_gain_string = "Defeating enemy war combatants has earned you $rep_gain Reputation.<br />";
            }
            // Daily Task
            if ($player->daily_tasks->hasTaskType(DailyTask::ACTIVITY_DAILY_WAR)) {
                $player->daily_tasks->progressTask(DailyTask::ACTIVITY_DAILY_WAR, $rep_gain);
            }
        }

        $battle_result = "You have defeated your opponent.<br />";
        $battle_result .= $money_gain_string;
        if ($rep_gain_string != "") {
            $battle_result .= $rep_gain_string;
        }
        if ($stat_gain_display) {
            $battle_result .= $stat_gain_display;
        }

        // handle patrol logic
        $battle_result .= $warManager->handleWinAgainstPatrol($patrol_id);

        $player->ai_wins++;
        $player->battle_id = 0;
        $player->last_pvp_ms = System::currentTimeMs();
    } else if ($battle->isOpponentWinner()) {
        $battle_result .= "You have been defeated.";
        $player->health = 5;
        $player->ai_losses++;
        //$player->moveToVillage();
        $player->battle_id = 0;
        $player->last_pvp_ms = System::currentTimeMs();
        $battle_result .= $warManager->handleLossAgainstPatrol($patrol_id);
    } else if ($battle->isDraw()) {
        $battle_result .= "The battle ended in a draw.";
        $player->health = 5;
        $player->moveToVillage();
        $player->battle_id = 0;
        $player->last_pvp_ms = System::currentTimeMs();
    }

    return $battle_result;
}