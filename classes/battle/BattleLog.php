<?php

class BattleLog {
    public int $battle_id;
    public int $turn_number;
    public string $content;
    public array $fighter_health = [];
    public string $active_effects = '';

    /**
     * BattleLog constructor.
     * @param int    $battle_id
     * @param int    $turn_number
     * @param string $content
     */
    public function __construct(int $battle_id, int $turn_number, string $content, array $fighter_health = [], string $raw_active_effects = '') {
        $this->battle_id = $battle_id;
        $this->turn_number = $turn_number;
        $this->content = $content;
        $this->fighter_health = $fighter_health;
        $this->active_effects = $raw_active_effects;
    }

    /**
     * @param System $system
     * @param int    $battle_id
     * @return BattleLog|null
     */
    public static function getLastTurn(System $system, int $battle_id): ?BattleLog {
        $result = $system->db->query(
            "SELECT * FROM `battle_logs`
                WHERE `battle_id`='{$battle_id}' ORDER BY `turn_number` DESC LIMIT 1"
        );
        if($system->db->last_num_rows > 0) {
            $raw_battle_log = $system->db->fetch($result);
            return new BattleLog(
                $raw_battle_log['battle_id'],
                $raw_battle_log['turn_number'],
                $raw_battle_log['content'],
                !empty($raw_battle_log['fighter_health']) ? json_decode($raw_battle_log['fighter_health'], true) : [],
                !empty($raw_battle_log['active_effects']) ? $raw_battle_log['active_effects'] : '',
            );
        }
        else {
            return null;
        }
    }

    /**
     * @param System $system
     * @param int    $battle_id
     * @param int    $turn_number
     * @param string $content
     */
    public static function addOrUpdateTurnLog(System $system, int $battle_id, int $turn_number, string $content, array $fighter_health = [], string $raw_active_effects = '') {
        $system->db->query(
            "INSERT IGNORE INTO `battle_logs`
                SET `battle_id`='{$battle_id}',
                    `turn_number`='{$turn_number}',
                    `content`='{$content}',
                    `fighter_health` = '" . json_encode($fighter_health) . "',
                    `active_effects` = '{$raw_active_effects}'
        ");
    }
}
