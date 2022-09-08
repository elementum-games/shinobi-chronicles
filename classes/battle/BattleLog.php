<?php

class BattleLog {
    private System $system;

    public int $battle_id;
    public int $turn_number;

    /** @var FighterActionLog[] - Map of combat_id => FighterActionLog */
    public array $fighter_action_logs;

    public string $content;

    /**
     * BattleLog constructor.
     * @param int    $battle_id
     * @param int    $turn_number
     * @param string $content
     * @param array  $fighter_action_logs
     */
    public function __construct(
        System $system,
        int $battle_id,
        int $turn_number,
        string $content,
        array $fighter_action_logs
    ) {
        $this->system = $system;
        $this->battle_id = $battle_id;
        $this->turn_number = $turn_number;
        $this->content = $content;
        $this->fighter_action_logs = $fighter_action_logs;
    }

    public function addFighterActionDescription(Fighter $fighter, string $action_description): void {
        $fighter_action_log = $this->getFighterActionLog($fighter->combat_id);
        $fighter_action_log->action_description .= $this->system->clean($action_description);
    }

    public function addFighterAttackHitDescription(Fighter $fighter, string $hit_description): void {
        $fighter_action_log = $this->getFighterActionLog($fighter->combat_id);
        $fighter_action_log->hit_descriptions[] = $this->system->clean($hit_description);
    }

    public function addFighterAppliedEffectDescription(Fighter $fighter, string $effect_description): void {
        $fighter_action_log = $this->getFighterActionLog($fighter->combat_id);
        $fighter_action_log->applied_effect_descriptions[] = $this->system->clean($effect_description);
    }

    protected function getFighterActionLog(string $fighter_id) {
        if(!isset($this->fighter_action_logs[$fighter_id])) {
            $this->fighter_action_logs[$fighter_id] = new FighterActionLog(
                $fighter_id, '', [], [], []
            );
        }

        return $this->fighter_action_logs[$fighter_id];
    }

    /**
     * @param System $system
     * @param int    $battle_id
     * @param int    $turn_count
     * @return BattleLog|null
     */
    public static function getTurn(System $system, int $battle_id, int $turn_count): ?BattleLog {
        $result = $system->query("SELECT * FROM `battle_logs` 
            WHERE `battle_id`='{$battle_id}' AND `turn_number`='{$turn_count}' LIMIT 1");
        if($system->db_last_num_rows > 0) {
            return BattleLog::fromDbArray($system, $system->db_fetch($result));
        }
        else {
            return null;
        }
    }

    /**
     * @param System $system
     * @param int    $battle_id
     * @return BattleLog|null
     */
    public static function getLastTurn(System $system, int $battle_id): ?BattleLog {
        $result = $system->query("SELECT * FROM `battle_logs` 
            WHERE `battle_id`='{$battle_id}' ORDER BY `turn_number` DESC LIMIT 1");
        if($system->db_last_num_rows > 0) {
            return BattleLog::fromDbArray($system, $system->db_fetch($result));
        }
        else {
            return null;
        }
    }

    public static function fromDbArray(System $system, array $raw_data): BattleLog {
        $fighter_action_logs = json_decode($raw_data['fighter_action_logs'], true);

        return new BattleLog(
            system: $system,
            battle_id: $raw_data['battle_id'],
            turn_number: $raw_data['turn_number'],
            content: $raw_data['content'],
            fighter_action_logs: array_map(function ($action_log) {
                return new FighterActionLog(
                    fighter_id: $action_log['fighter_id'],
                    action_description: $action_log['action_description'],
                    hit_descriptions: $action_log['hit_descriptions'],
                    applied_effect_descriptions: $action_log['applied_effect_descriptions'],
                    new_effect_announcements: $action_log['new_effect_announcements']
                );
            }, $fighter_action_logs)
        );
    }

    /**
     * @param System    $system
     * @param BattleLog $log
     */
    public static function addOrUpdateTurnLog(
        System $system, BattleLog $log
    ): void {
        // int $battle_id, int $turn_number, string $content, array $fighter_action_logs
        $clean_content = $system->clean($log->content);

        $fighter_action_logs_json = json_encode($log->fighter_action_logs);

        $system->query("INSERT INTO `battle_logs` 
            SET `battle_id`='{$log->battle_id}',
                `turn_number`='{$log->turn_number}',
                `content`='{$clean_content}',
                `fighter_action_logs`='{$fighter_action_logs_json}'
            ON DUPLICATE KEY UPDATE
                `content`='{$clean_content}',
                `fighter_action_logs`='{$fighter_action_logs_json}'
        ");
    }
}

class FighterActionLog {
    public function __construct(
        public string $fighter_id,
        public string $action_description,
        public array $hit_descriptions,
        public array $applied_effect_descriptions,
        public array $new_effect_announcements
    ) {}
}