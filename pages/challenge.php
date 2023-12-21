<?php

function challenge() {
    global $system;
    global $player;

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

    if ($player->battle_id) {
        try {
            if ($system->USE_NEW_BATTLES) {
                $battle = BattleManagerV2::init($system, $player, $player->battle_id);
            } else {
                $battle = BattleManager::init($system, $player, $player->battle_id);
            }

            $battle->checkInputAndRunTurn();

            $battle->renderBattle();

            if ($battle->isComplete()) {
                $battle_result = processChallengeBattleEnd($battle, $player, $system);
                echo "<table class='table'><tr><th>Battle Results</th></tr>
            <tr><td style='text-align: center;'>" . $battle_result . "</td></tr></table>";
            }
        } catch (RuntimeException $e) {
            System::checkAndThrowDeadlockException($e);
            $system->message($e->getMessage());
            $system->printMessage();
            return false;
        }
    }
    return true;
}

/**
 * @throws RuntimeException
 */
function processChallengeBattleEnd(BattleManager $battle, User $player, System $system): string {
    $player->battle_id = 0;
    $result = "";

    if ($battle->isPlayerWinner()) {
        $result = "You win!";
        $rep_gain = $player->reputation->addRep(
            UserReputation::SPAR_REP_WIN * 10,
            UserReputation::ACTIVITY_TYPE_PVE
        );
        $player->mission_rep_cd = time() + UserReputation::ARENA_MISSION_CD;
        $result .= "<br>You have gained $rep_gain village reputation!";
        if ($player->locked_challenge > 0) {
            VillageManager::processChallengeEnd($system, $player->locked_challenge, $player->user_id, $player);
        }
        return $result;
    } else if ($battle->isOpponentWinner()) {
        $player->health = 5;
        $result = "You lose.";
        $rep_gain = $player->reputation->addRep(
            UserReputation::SPAR_REP_LOSS * 10,
            UserReputation::ACTIVITY_TYPE_PVE
        );
        $player->mission_rep_cd = time() + UserReputation::ARENA_MISSION_CD;
        $result .= "<br>You have gained $rep_gain village reputation!";
        if ($player->locked_challenge > 0) {
            VillageManager::processChallengeEnd($system, $player->locked_challenge, $battle->opponent->user_id, $player);
        }
        return $result;
    } else if ($battle->isDraw()) {
        $player->health = 5;
        $result = "You both knocked each other out.";
        $rep_gain = $player->reputation->addRep(
            UserReputation::SPAR_REP_DRAW * 10,
            UserReputation::ACTIVITY_TYPE_PVE
        );
        $player->mission_rep_cd = time() + UserReputation::ARENA_MISSION_CD;
        $result .= "<br>You have gained $rep_gain village reputation!";
        if ($player->locked_challenge > 0) {
            VillageManager::processChallengeEnd($system, $player->locked_challenge, null, $player);
        }
        return $result;
    } else {
        throw new RuntimeException("Invalid battle completion!");
    }

    return $battle_result;
}