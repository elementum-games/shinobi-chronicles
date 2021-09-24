<?php

function viewBattles() {
    global $system;
    global $player;
    global $self_link;

    $battle_types = [Battle::TYPE_SPAR, Battle::TYPE_FIGHT, Battle::TYPE_CHALLENGE];
    $limit = 20;

    if($player->staff_level >= System::SC_HEAD_ADMINISTRATOR) {
        // $battle_types[] = Battle::TYPE_AI_ARENA;
        $limit = 25;
    }

    if(!empty($_GET['battle_id'])) {
        $battle_id = (int)$_GET['battle_id'];
        try {
            $battle = new Battle($system, $player, $battle_id, true);
            if(!in_array($battle->battle_type, $battle_types)) {
                throw new Exception("Invalid battle type!");
            }

            $battle->renderBattle();

            return true;
        } catch(Exception $e) {
            $system->message($e->getMessage());
            $system->printMessage();
        }
    }

    $battles_result = $system->query(
        "SELECT `battle_id`, `player1`, `player2`, `winner` FROM `battles` 
            WHERE `battle_type` IN (" . implode(",", $battle_types) . ")
            ORDER BY `battle_id` DESC LIMIT {$limit}");

    $user_ids = [];
    $raw_battles = [];
    while($row = $system->db_fetch($battles_result)) {
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
        $user_names_result = $system->query("SELECT `user_id`, `user_name` FROM `users` 
            WHERE `user_id` IN(" .  implode(',', $user_ids). ")");

        while($row = $system->db_fetch($user_names_result)) {
            $user_names[$row['user_id']] = $row['user_name'];
        }
    }

    $battles = [];
    foreach($raw_battles as $battle) {
        /** @var EntityId $p1 */
        $p1 = $battle['player1'];
        /** @var EntityId $p2 */
        $p2 = $battle['player2'];

        $p1_name = $user_names[$p1->id] ?? $p1->toString();
        $p2_name = $user_names[$p2->id] ?? $p2->toString();

        $winner = '';
        switch($battle['winner']) {
            case Battle::DRAW:
                $winner = 'Draw';
                break;
            case Battle::TEAM1:
                $winner = $p1_name;
                break;
            case Battle::TEAM2:
                $winner = $p2_name;
                break;
        }

        $battles[] = [
            'id' => $battle['id'],
            'player1' => $p1_name,
            'player2' => $p2_name,
            'winner' => $winner
        ];
    }

    require 'templates/viewBattles.php';
    return true;
}