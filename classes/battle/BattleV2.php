<?php

use JetBrains\PhpStorm\Pure;

require_once __DIR__ . '/BattleEffectsManagerV2.php';
require_once __DIR__ . '/BattleLogV2.php';
require_once __DIR__ . '/../war/Operation.php';

class BattleV2 {
    const TYPE_AI_ARENA = 1;
    const TYPE_SPAR = 2;
    const TYPE_FIGHT = 3;
    const TYPE_CHALLENGE = 4;
    const TYPE_AI_MISSION = 5;
    const TYPE_AI_RANKUP = 6;
    const TYPE_AI_WAR = 7;

    const TURN_LENGTH = 40;
    const PREP_LENGTH = 20;

    const MAX_PRE_FIGHT_HEAL_PERCENT = 100;

    const TEAM1 = 'T1';
    const TEAM2 = 'T2';
    const DRAW = 'DRAW';

    // Minimum % (of itself) a debuff can be reduced to with debuff resist
    const MIN_DEBUFF_RATIO = 0.1;
    const MAX_DIFFUSE_PERCENT = 0.75;

    const TURN_TYPE_MOVEMENT = 'movement';
    const TURN_TYPE_ATTACK = 'attack';

    private System $system;

    public string $raw_active_effects;
    public string $raw_active_genjutsu;

    public string $raw_field;

    // Properties
    public int $battle_id;
    public int $battle_type;

    public int $start_time;
    public int $turn_time;
    public int $turn_count;
    public string $turn_type;

    public string $winner;

    public Fighter $player;

    public string $player1_id;
    public string $player2_id;

    public Fighter $player1;
    public Fighter $player2;

    public array $fighter_health;

    /** @var FighterAction[] */
    public array $fighter_actions;

    /** @var BattleLogV2[] */
    public array $log;
    public BattleLogV2 $current_turn_log;

    public array $jutsu_cooldowns;

    /** @var array [user_combat_id][jutsu_combat_id] => [count, jutsu_type] */
    public array $fighter_jutsu_used;

    // transient instance var - more convenient to interface with log this way for the moment
    public string $battle_text;

    public ?int $patrol_id;

    public int $player1_last_damage_taken;
    public int $player2_last_damage_taken;

    public string $battle_background_link;

    public int $rounds = 1;
    public int $round_count = 1;
    public int $team1_wins;
    public int $team2_wins;

    /**
     * @param System   $system
     * @param Fighter  $player1
     * @param Fighter  $player2
     * @param int      $battle_type
     * @param int|null $patrol_id
     * @return int
     * @throws DatabaseDeadlockException
     */
    public static function start(
        System $system, Fighter $player1, Fighter $player2, int $battle_type, ?int $patrol_id = null, string $battle_background_link = '', int $rounds = 1
    ): int {
        $json_empty_array = '[]';

        switch($battle_type) {
            case self::TYPE_AI_ARENA:
            case self::TYPE_SPAR:
            case self::TYPE_FIGHT:
            case self::TYPE_CHALLENGE:
            case self::TYPE_AI_MISSION:
            case self::TYPE_AI_RANKUP:
            case self::TYPE_AI_WAR:
                break;
            default:
                throw new RuntimeException("Invalid battle type!");
        }

        $player1->combat_id = BattleV2::combatId(BattleV2::TEAM1, $player1);
        $player2->combat_id = BattleV2::combatId(BattleV2::TEAM2, $player2);

        $fighter_health = [
            $player1->combat_id => $player1->health,
            $player2->combat_id => $player2->health
        ];

        $initial_field = BattleField::getInitialFieldExport($player1, $player2, $battle_type);

        $system->db->query(
            "INSERT INTO `battles` SET
                `battle_type` = '" . $battle_type . "',
                `start_time` = '" . time() . "',
                `turn_time` = '" . (time() + self::PREP_LENGTH - 5) . "',
                `turn_count` = '" . 0 . "',
                `turn_type` = '" . BattleV2::TURN_TYPE_MOVEMENT . "',
                `winner` = '',
                `player1` = '" . $player1->id . "',
                `player2` = '" . $player2->id . "',
                `fighter_health` = '" . json_encode($fighter_health) . "',
                `fighter_actions` = '" . $json_empty_array . "',
                `field` = '" . json_encode($initial_field) . "',
                `active_effects` = '" . $json_empty_array . "',
                `active_genjutsu` = '" . $json_empty_array . "',
                `jutsu_cooldowns` = '" . $json_empty_array . "',
                `fighter_jutsu_used` = '" . $json_empty_array . "',
                `is_retreat` = '" . (int) false . "',
                `patrol_id` = " . (!empty($patrol_id) ? $patrol_id : "NULL") . ",
                `battle_background_link` = '{$battle_background_link}',
                `rounds` = '{$rounds}'
        ");
        $battle_id = $system->db->last_insert_id;

        if($player1 instanceof User) {
            $player1->battle_id = $battle_id;
            if ($battle_type == self::TYPE_FIGHT) {
                $player1->pvp_immunity_ms = 0; // if attacking lose immunity
                $system->db->query("UPDATE `loot` SET `battle_id` = {$battle_id} WHERE `user_id` = {$player1->user_id}");
            }
            if ($player1->operation > 0) {
                Operation::cancelOperation($system, $player1);
            }
            $player1->updateData();
        }
        if($player2 instanceof User) {
            $player2->battle_id = $battle_id;
            if ($battle_type == self::TYPE_FIGHT) {
                $system->db->query("UPDATE `loot` SET `battle_id` = {$battle_id} WHERE `user_id` = {$player2->user_id}");
            }
            if ($player2->operation > 0) {
                Operation::cancelOperation($system, $player2);
            }
            $player2->updateData();
        }

        return $battle_id;
    }

    /**
     * @throws RuntimeException
     */
    public static function loadFromId(System $system, User $player, int $battle_id): BattleV2 {
        $battle = new BattleV2($system, $player, $battle_id);

        $result = $system->db->query(
            "SELECT * FROM `battles` WHERE `battle_id`='{$battle_id}' LIMIT 1"
        );
        if($battle->system->db->last_num_rows == 0) {
            if($player->battle_id = $battle_id) {
                $player->battle_id = 0;
            }
            throw new RuntimeException("Invalid battle!");
        }

        $battle_data = $system->db->fetch($result);

        $battle->raw_active_effects = $battle_data['active_effects'];
        $battle->raw_active_genjutsu = $battle_data['active_genjutsu'];
        $battle->raw_field = $battle_data['field'];

        $battle->battle_type = $battle_data['battle_type'];

        $battle->start_time = $battle_data['start_time'];
        $battle->turn_time = $battle_data['turn_time'];
        $battle->turn_count = $battle_data['turn_count'];
        $battle->turn_type = $battle_data['turn_type'];

        $battle->winner = $battle_data['winner'];

        $battle->player1_id = $battle_data['player1'];
        $battle->player2_id = $battle_data['player2'];

        $battle->fighter_health = json_decode($battle_data['fighter_health'], true);
        $battle->fighter_actions = array_map(function($action_data) {
            return FighterAction::fromDb($action_data);
        }, json_decode($battle_data['fighter_actions'], true));

        $battle->jutsu_cooldowns = json_decode($battle_data['jutsu_cooldowns'] ?? "[]", true);

        $battle->fighter_jutsu_used = json_decode($battle_data['fighter_jutsu_used'], true);

        $battle->patrol_id = $battle_data['patrol_id'];

        // log
        $previous_turn_log = BattleLogV2::getTurn($battle->system, $battle->battle_id, $battle->turn_count - 1);
        if($previous_turn_log != null) {
            $battle->log[$previous_turn_log->turn_number] = $previous_turn_log;
        }

        $current_turn_log = BattleLogV2::getTurn($battle->system, $battle->battle_id, $battle->turn_count);
        if($current_turn_log == null) {
            $current_turn_log = new BattleLogV2(
                system: $battle->system,
                battle_id: $battle->battle_id,
                turn_number: $battle->turn_count,
                turn_phase: $battle->turn_type,
                content: $battle_id,
                fighter_action_logs: [],
                fighter_health: $battle->fighter_health,
                raw_active_effects: $battle->raw_active_effects,
            );
        }

        $battle->current_turn_log = $current_turn_log;
        $battle->log[$battle->turn_count] = $battle->current_turn_log;

        $battle->battle_background_link = empty($battle_data['battle_background_link']) ? '' : $battle_data['battle_background_link'];

        $battle->rounds = $battle_data['rounds'];
        $battle->round_count = $battle_data['round_count'];
        $battle->team1_wins = $battle_data['team1_wins'];
        $battle->team2_wins = $battle_data['team2_wins'];

        return $battle;
    }

    /**
     * BattleV2 constructor.
     * @param System $system
     * @param User   $player
     * @param int    $battle_id
     * @throws RuntimeException
     */
    public function __construct(
        System $system, User $player, int $battle_id
    ) {
        $this->system = $system;

        $this->battle_id = $battle_id;
        $this->player = $player;
    }

    /**
     * @throws RuntimeException
     */
    public function loadFighters(): void {
        if($this->player1_id != $this->player->id) {
            $this->player1 = $this->loadFighterFromEntityId($this->player1_id);
        }
        if($this->player2_id != $this->player->id) {
            $this->player2 = $this->loadFighterFromEntityId($this->player2_id);
        }

        $this->player1->combat_id = BattleV2::combatId(BattleV2::TEAM1, $this->player1);
        $this->player2->combat_id = BattleV2::combatId(BattleV2::TEAM2, $this->player2);

        if($this->player1 instanceof NPC) {
            $this->player1->loadData();
            $this->player1->health = $this->fighter_health[$this->player1->combat_id];
        }
        if($this->player2 instanceof NPC) {
            $this->player2->loadData();
            $this->player2->health = $this->fighter_health[$this->player2->combat_id];
        }

        if($this->player1 instanceof User && $this->player1->id != $this->player->id) {
            $this->player1->loadData(User::UPDATE_NOTHING, true);
        }
        if($this->player2 instanceof User && $this->player2->id != $this->player->id) {
            $this->player2->loadData(User::UPDATE_NOTHING, true);
        }

        $this->player1->getInventory();
        $this->player2->getInventory();

        $this->player1->applyBloodlineBoosts();
        $this->player2->applyBloodlineBoosts();
    }

    /**
     * @param string $entity_id
     * @return Fighter
     * @throws RuntimeException
     */
    protected function loadFighterFromEntityId(string $entity_id): Fighter {
    switch(BattleV2::getFighterEntityType($entity_id)) {
        case User::ENTITY_TYPE:
            return User::fromEntityId($this->system, $entity_id);
        case NPC::ID_PREFIX:
            return NPC::fromEntityId($this->system, $entity_id);
        default:
            throw new RuntimeException("Invalid entity type! " . BattleV2::getFighterEntityType($entity_id));
    }
}

    public function getFighter(string $combat_id): ?Fighter {
        if($this->player1->combat_id === $combat_id) {
            return $this->player1;
        }
        if($this->player2->combat_id === $combat_id) {
            return $this->player2;
        }

        echo "P1: {$this->player1->combat_id} / P2: {$this->player2->combat_id} / CID: $combat_id\r\n";

        return null;
    }

    /**
     * @param string $entity_id
     * @return string
     * @throws RuntimeException
     */
    protected static function getFighterEntityType(string $entity_id): string {
        $entity_id = System::parseEntityId($entity_id);
        return $entity_id->entity_type;
    }

    public function isComplete(): bool {
        return $this->winner;
    }

    public function isPreparationPhase(): bool {
        return $this->prepTimeRemaining() > 0 && in_array($this->battle_type, [BattleV2::TYPE_FIGHT, BattleV2::TYPE_CHALLENGE]);
    }

    public function timeRemaining(): int {
        if($this->isPreparationPhase()) {
            return $this->prepTimeRemaining();
        }

        $time_remaining = BattleV2::TURN_LENGTH - (time() - $this->turn_time);
        return max($time_remaining, 0);
    }

    protected function prepTimeRemaining(): int {
        return BattleV2::PREP_LENGTH - (time() - $this->start_time);
    }

    public function updateData(): void {
        $this->system->db->query("START TRANSACTION;");

        $this->system->db->query(
            "UPDATE `battles` SET
                `turn_time` = {$this->turn_time},
                `turn_count` = {$this->turn_count},
                `turn_type` = '{$this->turn_type}',
                `winner` = '{$this->winner}',

                `fighter_health` = '" . json_encode($this->fighter_health) . "',
                `fighter_actions` = '" . json_encode($this->fighter_actions) . "',

                `field` = '" . $this->raw_field . "',

                `active_effects` = '" . $this->raw_active_effects . "',
                `active_genjutsu` = '" . $this->raw_active_genjutsu . "',

                `jutsu_cooldowns` = '" . json_encode($this->jutsu_cooldowns) . "',
                `fighter_jutsu_used` = '" . json_encode($this->fighter_jutsu_used) . "',

                `round_count` = {$this->round_count},
                `team1_wins` = {$this->team1_wins},
                `team2_wins` = {$this->team2_wins}
            WHERE `battle_id` = '{$this->battle_id}' LIMIT 1"
        );

        BattleLogV2::addOrUpdateTurnLog(
            $this->system,
            $this->current_turn_log,
            $this->fighter_health,
            $this->raw_active_effects
        );

        $this->system->db->query("COMMIT;");
    }

    public static function combatId(string $team, Fighter $fighter): string {
        return $team . ':' . $fighter->id;
    }

    /**
     * @param Fighter $fighter
     * @return string
     * @throws RuntimeException
     */
    public static function fighterTeam(Fighter $fighter): string {
        $combat_id_parts = explode(':', $fighter->combat_id);
        switch($combat_id_parts[0]) {
            case self::TEAM1:
            case self::TEAM2:
                return $combat_id_parts[0];
            default:
                throw new RuntimeException("Invalid team in combat id! ({$fighter->combat_id})");
        }
    }

    public function isAttackPhase(): bool {
        return $this->turn_type === BattleV2::TURN_TYPE_ATTACK;
    }

    public function isMovementPhase(): bool {
        return $this->turn_type === BattleV2::TURN_TYPE_MOVEMENT;
    }

    public function isFighterActionSubmitted(Fighter $fighter): bool {
        return isset($this->fighter_actions[$fighter->combat_id]);
    }

    #[Pure]
    public function getCurrentPhaseLabel(): string {
        if($this->isMovementPhase()) {
            return "Movement";
        }
        else if($this->isAttackPhase()) {
            return "Attack";
        }
        else {
            return "[Invalid]";
        }
    }

    public function getLastTurnLog(): ?BattleLogV2 {
        return $this->log[$this->turn_count - 1] ?? null;
    }
}
