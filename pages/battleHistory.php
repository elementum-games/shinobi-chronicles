<?php

function battleHistory() {
    global $system;
	global $player;

    if(!$player->rank_num > 1 || $player->forbidden_seal->max_battle_history_view <= 0) {
		$system->message("You are not qualified to view this page!");
		$system->printMessage();
		return false;
	}

    require_once "profile.php";
    renderProfileSubmenu();

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
    $battleIds = [];
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
            case Battle::TEAM1:
                $winner = $p1_name;
                break;
            case Battle::TEAM2:
                $winner = $p2_name;
                break;
        }

        $battleIds[] = $battleManager['id'];
        $battles[] = [
            'id' => $battleManager['id'],
            'player1' => $p1_name,
            'player2' => $p2_name,
            'winner' => $winner
        ];
    }

    $logs_result;
    $battle_logs = [];
    if (isset($_GET['view_log'])) {
        try {
            $battle_id = (int)$_GET['view_log'];
            if (in_array($battle_id, $battleIds)) {
                $logs_result = $system->db->query(
                    "SELECT `turn_number`, `content` FROM `battle_logs`
                    WHERE `battle_id` = '{$battle_id}'
                    AND `turn_number` != '0'
                    ORDER BY `turn_number` ASC"
                );

                while($row = $system->db->fetch($logs_result)) {
                    $battle_text = $system->html_parse(stripslashes($row['content']));
                    $battle_text = str_replace(array('[br]', '[hr]'), array('', '<hr />'), $battle_text);
                    $battle_logs[] = [
                        'turn' => $row['turn_number'],
                        'content' => $battle_text
                    ];
                }
            }
            else {
                throw new Exception("Invalid battle!");
            }
        }
        catch (Exception $e) {
			$system->message($e->getMessage());
        }
    }
    $system->printMessage();

    require_once('templates/battleHistory.php');
}
