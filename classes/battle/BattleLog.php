<?php

class BattleLog {
    private System $system;

    public int $battle_id;
    public int $turn_number;

    /*
     * One of
     *     Battle::TURN_TYPE_MOVEMENT
     *     Battle::TURN_TYPE_ATTACK
     */
    public string $turn_phase;

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
        string $turn_phase,
        string $content,
        array $fighter_action_logs
    ) {
        $this->system = $system;
        $this->battle_id = $battle_id;
        $this->turn_number = $turn_number;
        $this->turn_phase = $turn_phase;
        $this->content = $content;
        $this->fighter_action_logs = $fighter_action_logs;
    }

    public function addFighterActionDescription(Fighter $fighter, string $action_description): void {
        $fighter_action_log = $this->getFighterActionLog($fighter->combat_id);
        $fighter_action_log->action_description .= $this->system->clean($action_description);
    }

    public function addFighterAttackHit(
        Fighter $attacker, Fighter $target, string $damage_type, float $damage
    ): void {
        $fighter_action_log = $this->getFighterActionLog($attacker->combat_id);
        $fighter_action_log->hits[] = new AttackHitLog(
            attacker_id: $attacker->combat_id,
            attacker_name: $attacker->getName(),
            target_id: $target->combat_id,
            target_name: $target->getName(),
            damage_type: $damage_type,
            damage: $damage,
        );
    }

    public function addFighterAppliedEffectDescription(Fighter $fighter, string $effect_description): void {
        $fighter_action_log = $this->getFighterActionLog($fighter->combat_id);
        $fighter_action_log->applied_effect_descriptions[] = $this->system->clean($effect_description);
    }

    public function addFighterEffectAnnouncement(Fighter $fighter, string $announcement_text): void {
        $fighter_action_log = $this->getFighterActionLog($fighter->combat_id);
        $fighter_action_log->applied_effect_descriptions[] = $this->system->clean($announcement_text);
    }

    protected function getFighterActionLog(string $fighter_id) {
        if(!isset($this->fighter_action_logs[$fighter_id])) {
            $this->fighter_action_logs[$fighter_id] = new FighterActionLog(
                fighter_id: $fighter_id,
                action_description: '',
                hits: [],
                applied_effect_descriptions: [],
                new_effect_announcements: []
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
            turn_phase: $raw_data['turn_phase'],
            content: $raw_data['content'],
            fighter_action_logs: array_map(function ($action_log) {
                return new FighterActionLog(
                    fighter_id: $action_log['fighter_id'],
                    action_description: $action_log['action_description'],
                    hits: array_map(function($hit) {
                        return AttackHitLog::fromArray($hit);
                    }, $action_log['hits']),
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
        $clean_content = $system->clean($log->content);

        $fighter_action_logs_json = json_encode($log->fighter_action_logs);

        $system->query("INSERT INTO `battle_logs` 
            SET `battle_id`='{$log->battle_id}',
                `turn_number`='{$log->turn_number}',
                `turn_phase`='{$log->turn_phase}',
                `content`='{$clean_content}',
                `fighter_action_logs`='{$fighter_action_logs_json}'
            ON DUPLICATE KEY UPDATE
                `content`='{$clean_content}',
                `fighter_action_logs`='{$fighter_action_logs_json}'
        ", true);
    }
}

class FighterActionLog {
    /**
     * @param string         $fighter_id
     * @param string         $action_description
     * @param AttackHitLog[] $hits
     * @param array          $applied_effect_descriptions
     * @param array          $new_effect_announcements
     */
    public function __construct(
        public string $fighter_id,
        public string $action_description,
        public array $hits,
        public array $applied_effect_descriptions,
        public array $new_effect_announcements
    ) {}
}

class AttackHitLog {
    public function __construct(
        public string $attacker_id,
        public string $attacker_name,
        public string $target_id,
        public string $target_name,
        public string $damage_type,
        public float $damage,
    ) {}

    public static function fromArray($array): AttackHitLog {
        return new AttackHitLog(
            attacker_id: $array['attacker_id'],
            attacker_name: $array['attacker_name'],
            target_id: $array['target_id'],
            target_name: $array['target_name'],
            damage_type: $array['damage_type'],
            damage: $array['damage'],
        );
    }
}