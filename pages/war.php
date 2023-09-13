<?php

// WIP PLACEHOLDER
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

    function processWarBattleEnd($battle, $player): string {
        return '';
    }

    return;
}