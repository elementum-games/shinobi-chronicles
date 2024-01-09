<?php

function viewBattles() {
    global $system;
    global $player;
    global $self_link;

    $battle_types = [Battle::TYPE_SPAR, Battle::TYPE_FIGHT, Battle::TYPE_CHALLENGE];
    $limit = 20;

    if($player->isHeadAdmin()) {
        // $battle_types[] = Battle::TYPE_AI_ARENA;
        $limit = 25;
    }

    $view = "view_battles";
    if (!empty($_GET['view'])) {
        $view = $_GET['view'];
    }

    if(!empty($_GET['battle_id'])) {
        $battle_id = (int)$_GET['battle_id'];
        try {
            if($system->USE_NEW_BATTLES) {
                $battleManager = BattleManagerV2::init($system, $player, $battle_id, true);
            }
            else {
                $battleManager = BattleManager::init($system, $player, $battle_id, true);
            }

            if(!in_array($battleManager->getBattleType(), $battle_types)) {
                throw new RuntimeException("Invalid battle type!");
            }

            $battleManager->renderBattle();

            return true;
        } catch(RuntimeException $e) {
            $system->message($e->getMessage());
            $system->printMessage();
        }
    }

    /* Begin Scheduled Battles */

    $scheduled_battles = [];
    $scheduled_battles_result = $system->db->query("SELECT * FROM `challenge_requests` WHERE `end_time` IS NULL AND `start_time` IS NOT NULL");
    $scheduled_battles_result = $system->db->fetch_all($scheduled_battles_result);
    foreach ($scheduled_battles_result as $challenge) {
        $challenger_result = $system->db->query("SELECT `user_name` FROM `users` WHERE `user_id` = {$challenge['challenger_id']}");
        $challenger_result = $system->db->fetch($challenger_result);
        $seat_holder_result = $system->db->query("SELECT `user_name` FROM `users` WHERE `user_id` = {$challenge['seat_holder_id']}");
        $seat_holder_result = $system->db->fetch($seat_holder_result);
        $scheduled_battles[] = [
            'challenger_name' => $challenger_result['user_name'],
            'seat_holder_name' => $seat_holder_result['user_name'],
            'time' => $challenge['start_time'],
            'battle_id' => $challenge['battle_id'],
        ];
    }

    /* End Scheduled Battles */

    $winner_stop = Battle::STOP;
    $battles_result = $system->db->query(
        "SELECT `battle_id`, `player1`, `player2`, `winner` FROM `battles`
            WHERE `battle_type` IN (" . implode(",", $battle_types) . ")
            ORDER BY `battle_id` DESC LIMIT {$limit}"
    );

    $user_ids = [];
    $raw_battles = [];
    while($row = $system->db->fetch($battles_result)) {
        $p1 = EntityId::fromString($row['player1']);
        $p2 = EntityId::fromString($row['player2']);
        if($p1->entity_type == User::ENTITY_TYPE) {
            $user_ids[] = $p1->id;
        }
        if($p2->entity_type == User::ENTITY_TYPE) {
            $user_ids[] = $p2->id;
        }

        $raw_battles[] = [
            'id' => $row['battle_id'],
            'player1' => $p1,
            'player2' => $p2,
            'winner' => $row['winner'],
        ];
    }

    $user_names = [];
    if(count($user_ids) > 0) {
        $user_names_result = $system->db->query(
            "SELECT `user_id`, `user_name` FROM `users`
                WHERE `user_id` IN(" .  implode(',', $user_ids). ")"
        );

        while($row = $system->db->fetch($user_names_result)) {
            $user_names[$row['user_id']] = $row['user_name'];
        }
    }

    $battles = [];
    foreach($raw_battles as $battleManager) {
        /** @var EntityId $p1 */
        $p1 = $battleManager['player1'];
        /** @var EntityId $p2 */
        $p2 = $battleManager['player2'];

        $p1_name = $user_names[$p1->id] ?? $p1->toString();
        $p2_name = $user_names[$p2->id] ?? $p2->toString();

        $winner = '';
        switch($battleManager['winner']) {
            case Battle::DRAW:
                $winner = 'Draw';
                break;
            case Battle::STOP:
                $winner = 'Stopped';
                break;
            case Battle::TEAM1:
                $winner = $p1_name;
                break;
            case Battle::TEAM2:
                $winner = $p2_name;
                break;
        }

        $battles[] = [
            'id' => $battleManager['id'],
            'player1' => $p1_name,
            'player2' => $p2_name,
            'winner' => $winner
        ];
    }

    /* Begin User Battle History */
    if ($view == "battle_history" && !($player->forbidden_seal->max_battle_history_view > 0)) {
        $system->message("Visit the Ancient Market and imbue a Forbidden Seal to view past battles.");
        $system->printMessage();
    }

    if ($player->rank_num > 1 && $player->forbidden_seal->max_battle_history_view > 0) {

        /* PvP Battles */
        $battle_types = [Battle::TYPE_SPAR, Battle::TYPE_FIGHT, Battle::TYPE_CHALLENGE];
        $limit = $player->forbidden_seal->max_battle_history_view;

        $battles_result = $system->db->query(
            "SELECT `battle_id`, `player1`, `player2`, `winner` FROM `battles`
            WHERE `battle_type` IN (" . implode(",", $battle_types) . ")
            AND (player1 = '{$player->id}' OR player2 = '{$player->id}')
            ORDER BY `battle_id` DESC LIMIT {$limit}"
        );

        $user_ids = [];
        $raw_battles = [];
        while ($row = $system->db->fetch($battles_result)) {
            $p1 = EntityId::fromString($row['player1']);
            $p2 = EntityId::fromString($row['player2']);
            if ($p1->entity_type == User::ENTITY_TYPE) {
                $user_ids[] = $p1->id;
            }
            if ($p2->entity_type == User::ENTITY_TYPE) {
                $user_ids[] = $p2->id;
            }

            $raw_battles[] = [
                'id' => $row['battle_id'],
                'player1' => $p1,
                'player2' => $p2,
                'winner' => $row['winner'],
            ];
        }

        $user_names = [];
        if (count($user_ids) > 0) {
            $user_names_result = $system->db->query(
                "SELECT `user_id`, `user_name` FROM `users`
                WHERE `user_id` IN(" . implode(',', $user_ids) . ")"
            );

            while ($row = $system->db->fetch($user_names_result)) {
                $user_names[$row['user_id']] = $row['user_name'];
            }
        }

        $user_battles = [];
        $battleIds = [];
        foreach ($raw_battles as $battleManager) {
            /** @var EntityId $p1 */
            $p1 = $battleManager['player1'];
            /** @var EntityId $p2 */
            $p2 = $battleManager['player2'];

            $p1_name = $user_names[$p1->id] ?? $p1->toString();
            $p2_name = $user_names[$p2->id] ?? $p2->toString();

            $winner = '';
            switch ($battleManager['winner']) {
                case Battle::DRAW:
                    $winner = 'Draw';
                    break;
                case Battle::STOP:
                    $winner = 'Stopped';
                    break;
                case Battle::TEAM1:
                    $winner = $p1_name;
                    break;
                case Battle::TEAM2:
                    $winner = $p2_name;
                    break;
            }

            $battleIds[] = $battleManager['id'];
            $user_battles[] = [
                'id' => $battleManager['id'],
                'player1' => $p1_name,
                'player2' => $p2_name,
                'winner' => $winner
            ];
        }

        /* PvE Battles */
        $battle_types = [Battle::TYPE_AI_ARENA, Battle::TYPE_AI_MISSION, Battle::TYPE_AI_RANKUP, Battle::TYPE_AI_WAR];
        $limit = $player->forbidden_seal->max_battle_history_view;

        $battles_result = $system->db->query(
            "SELECT `battle_id`, `player1`, `player2`, `winner` FROM `battles`
            WHERE `battle_type` IN (" . implode(",", $battle_types) . ")
            AND player1 = '{$player->id}'
            ORDER BY `battle_id` DESC LIMIT {$limit}"
        );

        $ai_ids = [];
        $raw_battles = [];;
        while ($row = $system->db->fetch($battles_result)) {
            $p1 = EntityId::fromString($row['player1']);
            $p2 = EntityId::fromString($row['player2']);
            $ai_ids[] = $p2->id;

            $raw_battles[] = [
                'id' => $row['battle_id'],
                'player1' => $p1,
                'player2' => $p2,
                'winner' => $row['winner'],
            ];
        }

        $ai_names = [];
        $ai_names_result = $system->db->query(
            "SELECT `ai_id`, `name` FROM `ai_opponents`
            WHERE `ai_id` IN(" . implode(',', $ai_ids) . ")
        ");
        $ai_names_result = $system->db->fetch_all($ai_names_result);
        foreach ($ai_names_result as $ai) {
            $ai_names[$ai['ai_id']] = $ai['name'];
        }

        $ai_battles = [];
        $battleIds = [];
        foreach ($raw_battles as $battle) {
            /** @var EntityId $p1 */
            $p1 = $battle['player1'];
            /** @var EntityId $p2 */
            $p2 = $battle['player2'];

            $p1_name = $player->user_name;
            $p2_name = $ai_names[$p2->id];

            $winner = '';
            switch ($battle['winner']) {
                case Battle::DRAW:
                    $winner = 'Draw';
                    break;
                case Battle::STOP:
                    $winner = 'Stopped';
                    break;
                case Battle::TEAM1:
                    $winner = $p1_name;
                    break;
                case Battle::TEAM2:
                    $winner = $p2_name;
                    break;
            }

            $battleIds[] = $battle['id'];
            $ai_battles[] = [
                'id' => $battle['id'],
                'player1' => $p1_name,
                'player2' => $p2_name,
                'winner' => $winner
            ];
        }


        /* View Log */
        $logs_result;
        $battle_logs = [];
        if (isset($_GET['view_log'])) {
            $view = "battle_history";
            try {
                $battle_id = (int) $_GET['view_log'];
                if (!($player->forbidden_seal->max_battle_history_view > 0)) {
                    throw new RuntimeException("Visit the Ancient Market and imbue a Forbidden Seal to view past battles.");
                }
                // get battle
                $battle_result = $system->db->query("
                    SELECT * FROM `battles` WHERE `battle_id` = {$battle_id}
                ");
                $battle_result = $system->db->fetch($battle_result);
                if ($system->db->last_num_rows > 0) {
                    // only allow viewing own AI battles
                    if (strpos($battle_result['player2'], "NPC") !== false) {
                        $own_battle = false;
                        if ($battle_result['player1'] == $player->id || $battle_result['player2'] == $player->id) {
                            $own_battle = true;
                        }
                        if (!$own_battle) {
                            throw new RuntimeException("Battle not found!");
                        }
                    }
                } else {
                    throw new RuntimeException("Battle not found!");
                }
                $logs_result = $system->db->query(
                    "SELECT `turn_number`, `content` FROM `battle_logs`
                    WHERE `battle_id` = '{$battle_id}'
                    AND `turn_number` != '0'
                    ORDER BY `turn_number` ASC"
                );
                if ($system->db->last_num_rows > 0) {
                    while ($row = $system->db->fetch($logs_result)) {
                        $battle_text = $system->html_parse(stripslashes($row['content']));
                        $battle_text = str_replace(array('[br]', '[hr]'), array('', '<hr />'), $battle_text);
                        $battle_logs[] = [
                            'turn' => $row['turn_number'],
                            'content' => $battle_text
                        ];
                    }
                } else {
                    throw new RuntimeException("No logs available for this battle.");
                }
            } catch (RuntimeException $e) {
                $system->message($e->getMessage());
            }
        }
        $system->printMessage();

    }
    require_once('templates/view_battles/view_battles_header.php');
    return true;
}
