<?php

class BattleLogV2 {
    private System $system;

    public int $battle_id;
    public int $turn_number;

    /*
     * One of
     *     BattleV2::TURN_TYPE_MOVEMENT
     *     BattleV2::TURN_TYPE_ATTACK
     */
    public string $turn_phase;

    /** @var FighterActionLog[] - Map of combat_id => FighterActionLog */
    public array $fighter_action_logs;

    public string $content;

    /**
     * BattleLogV2 constructor.
     * @param System $system
     * @param int    $battle_id
     * @param int    $turn_number
     * @param string $turn_phase
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

    /**
     * @param Fighter             $fighter
     * @param AttackPathSegment[] $path_segments
     * @return void
     */
    public function setFighterAttackPathSegments(Fighter $fighter, array $path_segments): void {
        $fighter_action_log = $this->getFighterActionLog($fighter->combat_id);
        $fighter_action_log->path_segments = $path_segments;
    }

    public function addFighterAttackHit(
        Fighter $attacker, Fighter $target, string $damage_type, float $damage, int $time_occurred
    ): void {
        $fighter_action_log = $this->getFighterActionLog($attacker->combat_id);
        $fighter_action_log->hits[] = new AttackHitLog(
            attacker_id: $attacker->combat_id,
            attacker_name: $attacker->getName(),
            target_id: $target->combat_id,
            target_name: $target->getName(),
            damage_type: $damage_type,
            damage: $damage,
            time_occurred: $time_occurred
        );
    }

    /**
     * @param Fighter      $target
     * @param EffectHitLog $effect_hit
     * @return void
     */
    public function addFighterEffectHit(
        Fighter $target, EffectHitLog $effect_hit
    ): void {
        $effect_hit->description = $this->system->clean($effect_hit->description);

        $fighter_action_log = $this->getFighterActionLog($target->combat_id);
        $fighter_action_log->effect_hits[] = $effect_hit;
    }

    public function addFighterEffectAnnouncement(Fighter $caster, Fighter $target, string $announcement_text): void {
        $fighter_action_log = $this->getFighterActionLog($caster->combat_id);
        $fighter_action_log->new_effect_announcements[] = $this->system->clean(
            BattleLogV2::parseCombatText(text: $announcement_text, attacker: $caster, target: $target)
        );
    }

    public function addFighterAttackJutsuInfo(Fighter $fighter, Jutsu $jutsu): void {
        $fighter_action_log = $this->getFighterActionLog($fighter->combat_id);
        $fighter_action_log->jutsu_element = $jutsu->element;
        $fighter_action_log->jutsu_type = $jutsu->jutsu_type;
        $fighter_action_log->jutsu_use_type = $jutsu->use_type;
        $fighter_action_log->jutsu_target_type = $jutsu->target_type;
    }

    protected function getFighterActionLog(string $fighter_id) {
        if(!isset($this->fighter_action_logs[$fighter_id])) {
            $this->fighter_action_logs[$fighter_id] = new FighterActionLog(
                fighter_id: $fighter_id,
                action_description: '',
                path_segments: [],
                hits: [],
                effect_hits: [],
                new_effect_announcements: []
            );
        }

        return $this->fighter_action_logs[$fighter_id];
    }

    /**
     * @param System $system
     * @param int    $battle_id
     * @param int    $turn_count
     * @return BattleLogV2|null
     * @throws Exception
     */
    public static function getTurn(System $system, int $battle_id, int $turn_count): ?BattleLogV2 {
        $result = $system->query("SELECT * FROM `battle_logs` 
            WHERE `battle_id`='{$battle_id}' AND `turn_number`='{$turn_count}' LIMIT 1");
        if($system->db_last_num_rows > 0) {
            return BattleLogV2::fromDbArray($system, $system->db_fetch($result));
        }
        else {
            return null;
        }
    }

    /**
     * @throws Exception
     */
    public static function fromDbArray(System $system, array $raw_data): BattleLogV2 {
        $fighter_action_logs = json_decode($raw_data['fighter_action_logs'], true);

        return new BattleLogV2(
            system: $system,
            battle_id: $raw_data['battle_id'],
            turn_number: $raw_data['turn_number'],
            turn_phase: $raw_data['turn_phase'],
            content: $raw_data['content'],
            fighter_action_logs: array_map(function ($action_log) {
                return new FighterActionLog(
                    fighter_id: $action_log['fighter_id'],
                    action_description: $action_log['action_description'],
                    path_segments: array_map(function ($segment) {
                        return AttackPathSegment::fromArray($segment);
                    }, $action_log['path_segments']),
                    hits: array_map(function($hit) {
                        return AttackHitLog::fromArray($hit);
                    }, $action_log['hits']),
                    effect_hits: array_map(function($effect_hit) {
                        return EffectHitLog::fromArray($effect_hit);
                    }, $action_log['effect_hits']),
                    new_effect_announcements: $action_log['new_effect_announcements']
                );
            }, $fighter_action_logs)
        );
    }

    /**
     * @param System      $system
     * @param BattleLogV2 $log
     */
    public static function addOrUpdateTurnLog(
        System $system, BattleLogV2 $log
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
        ");
    }

    public static function parseCombatText(string $text, Fighter $attacker, Fighter $target): string {
        return str_replace(
            [
                '[player]',
                '[opponent]',
                '[target]',
                '[gender]',
                '[gender2]',
            ],
            [
                $attacker->getName(),
                $target->getName(),
                $target->getName(),
                $attacker->getSingularPronoun(),
                $attacker->getPossessivePronoun(),
            ],
            $text
        );
    }
}

class FighterActionLog {
    /**
     * @param string              $fighter_id
     * @param string              $action_description
     * @param AttackPathSegment[] $path_segments
     * @param AttackHitLog[]      $hits
     * @param array               $effect_hits
     * @param array               $new_effect_announcements
     * @param string|null         $jutsu_element
     * @param string|null         $jutsu_type
     * @param string|null         $jutsu_use_type
     * @param string|null         $jutsu_target_type
     */
    public function __construct(
        public string $fighter_id,
        public string $action_description,
        public array $path_segments,
        public array $hits,
        public array $effect_hits,
        public array $new_effect_announcements,
        public ?string $jutsu_element = null,
        public ?string $jutsu_type = null,
        public ?string $jutsu_use_type = null,
        public ?string $jutsu_target_type = null,
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
        public int $time_occurred
    ) {}

    public static function fromArray($array): AttackHitLog {
        return new AttackHitLog(
            attacker_id: $array['attacker_id'],
            attacker_name: $array['attacker_name'],
            target_id: $array['target_id'],
            target_name: $array['target_name'],
            damage_type: $array['damage_type'],
            damage: $array['damage'],
            time_occurred: $array['time_occurred'] ?? 0
        );
    }
}

class EffectHitLog {
    const TYPE_HEAL = 'heal';
    const TYPE_BREAK_GENJUTSU = 'break_genjutsu';
    const TYPE_NINJUTSU_DAMAGE = 'ninjutsu_damage';
    const TYPE_TAIJUTSU_DAMAGE = 'taijutsu_damage';
    const TYPE_GENJUTSU_DAMAGE = 'genjutsu_damage';

    /**
     * @throws Exception
     */
    public function __construct(
        public string $caster_id,
        public string $target_id,
        public string $type,
        public string $description
    ) {
        switch($this->type) {
            case EffectHitLog::TYPE_HEAL:
            case EffectHitLog::TYPE_NINJUTSU_DAMAGE:
            case EffectHitLog::TYPE_TAIJUTSU_DAMAGE:
            case EffectHitLog::TYPE_GENJUTSU_DAMAGE:
                break;
            default:
                throw new Exception("Invalid effect tick type! {$this->type}");
        }
    }

    /**
     * @throws Exception
     */
    public static function fromArray($array): EffectHitLog {
        return new EffectHitLog(
            caster_id: $array['caster_id'],
            target_id: $array['target_id'],
            type: $array['type'],
            description: $array['description']
        );
    }

    /**
     * @throws Exception
     */
    public static function getTypeFromDamageType(string $damage_type): string {
        switch($damage_type) {
            case Jutsu::TYPE_NINJUTSU:
                return EffectHitLog::TYPE_NINJUTSU_DAMAGE;
            case Jutsu::TYPE_GENJUTSU:
                return EffectHitLog::TYPE_GENJUTSU_DAMAGE;
            case Jutsu::TYPE_TAIJUTSU:
                return EffectHitLog::TYPE_TAIJUTSU_DAMAGE;
            default:
                throw new Exception("Invalid damage type!");
        }

    }
}