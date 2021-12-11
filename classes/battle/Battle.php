<?php

require_once __DIR__ . '/BattleEffectsManager.php';
require_once __DIR__ . '/BattleLog.php';

class Battle {
    const TYPE_AI_ARENA = 1;
    const TYPE_SPAR = 2;
    const TYPE_FIGHT = 3;
    const TYPE_CHALLENGE = 4;
    const TYPE_AI_MISSION = 5;
    const TYPE_AI_RANKUP = 6;

    const TURN_LENGTH = 40;
    const PREP_LENGTH = 20;

    const MAX_PRE_FIGHT_HEAL_PERCENT = 85;

    const TEAM1 = 'T1';
    const TEAM2 = 'T2';
    const DRAW = 'DRAW';

    // Minimum % (of itself) a debuff can be reduced to with debuff resist
    const MIN_DEBUFF_RATIO = 0.1;
    const MAX_DIFFUSE_PERCENT = 0.75;

    const TURN_TYPE_MOVE = 'move';
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

    /** @var BattleLog[] */
    public array $log;

    public array $jutsu_cooldowns;

    /** @var array [user_combat_id][jutsu_combat_id] => [count, jutsu_type] */
    public array $fighter_jutsu_used;

    // transient instance var - more convenient to interface with log this way for the moment
    public string $battle_text;

    /**
     * @param System  $system
     * @param Fighter $player1
     * @param Fighter $player2
     * @param int     $battle_type
     * @return int
     * @throws Exception
     */
    public static function start(
        System $system, Fighter $player1, Fighter $player2, int $battle_type
    ): int {
        $json_empty_array = '[]';

        switch($battle_type) {
            case self::TYPE_AI_ARENA:
            case self::TYPE_SPAR:
            case self::TYPE_FIGHT:
            case self::TYPE_CHALLENGE:
            case self::TYPE_AI_MISSION:
            case self::TYPE_AI_RANKUP:
                break;
            default:
                throw new Exception("Invalid battle type!");
        }

        $player1->combat_id = Battle::combatId(Battle::TEAM1, $player1);
        $player2->combat_id = Battle::combatId(Battle::TEAM2, $player2);

        $fighter_health = [
            $player1->combat_id => $player1->health,
            $player2->combat_id => $player2->health
        ];

        $initial_field = BattleField::getInitialFieldExport($player1, $player2, $battle_type);
        
        $system->query(
            "INSERT INTO `battles` SET 
                `battle_type` = '" . $battle_type . "',
                `start_time` = '" . time() . "',
                `turn_time` = '" . (time() + self::PREP_LENGTH - 5) . "',
                `turn_count` = '" . 0 . "',
                `winner` = '',
                `player1` = '" . $player1->id . "',
                `player2` = '" . $player2->id . "',
                `fighter_health` = '" . json_encode($fighter_health) . "',
                `fighter_actions` = '" . $json_empty_array . "',
                `field` = '" . json_encode($initial_field) . "',
                `active_effects` = '" . $json_empty_array . "',
                `active_genjutsu` = '" . $json_empty_array . "',
                `jutsu_cooldowns` = '" . $json_empty_array . "',
                `fighter_jutsu_used` = '" . $json_empty_array . "'
                "
        );
        $battle_id = $system->db_last_insert_id;

        if($player1 instanceof User) {
            $player1->battle_id = $battle_id;
            $player1->updateData();
        }
        if($player2 instanceof User) {
            $player2->battle_id = $battle_id;
            $player2->updateData();
        }

        return $battle_id;
    }

    /**
     * Battle constructor.
     * @param System $system
     * @param User   $player
     * @param int    $battle_id
     * @throws Exception
     */
    public function __construct(
        System $system, User $player, int $battle_id
    ) {
        $this->system = $system;

        $this->battle_id = $battle_id;
        $this->player = $player;

        $result = $this->system->query(
            "SELECT * FROM `battles` WHERE `battle_id`='{$battle_id}' LIMIT 1"
        );
        if($this->system->db_last_num_rows == 0) {
            if($player->battle_id = $battle_id) {
                $player->battle_id = 0;
            }
            throw new Exception("Invalid battle!");
        }

        $battle = $this->system->db_fetch($result);

        $this->raw_active_effects = $battle['active_effects'];
        $this->raw_active_genjutsu = $battle['active_genjutsu'];
        $this->raw_field = $battle['field'];

        $this->battle_type = $battle['battle_type'];

        $this->start_time = $battle['start_time'];
        $this->turn_time = $battle['turn_time'];
        $this->turn_count = $battle['turn_count'];
        $this->turn_type = $battle['turn_type'];

        $this->winner = $battle['winner'];

        $this->player1_id = $battle['player1'];
        $this->player2_id = $battle['player2'];

        $this->fighter_health = json_decode($battle['fighter_health'], true);
        $this->fighter_actions = array_map(function($action_data) {
            return FighterAction::fromDb($action_data);
        }, json_decode($battle['fighter_actions'], true));

        $this->jutsu_cooldowns = json_decode($battle['jutsu_cooldowns'] ?? "[]", true);

        $this->fighter_jutsu_used = json_decode($battle['fighter_jutsu_used'], true);

        // lo9g
        $last_turn_log = BattleLog::getLastTurn($this->system, $this->battle_id);
        if($last_turn_log != null) {
            $this->log[$last_turn_log->turn_number] = $last_turn_log;
            $this->battle_text = $last_turn_log->content;
        }
        else {
            $this->battle_text = '';
        }
    }

    /**
     * @throws Exception
     */
    public function loadFighters() {
        if($this->player1_id != $this->player->id) {
            $this->player1 = $this->loadFighterFromEntityId($this->player1_id);
        }
        if($this->player2_id != $this->player->id) {
            $this->player2 = $this->loadFighterFromEntityId($this->player2_id);
        }

        $this->player1->combat_id = Battle::combatId(Battle::TEAM1, $this->player1);
        $this->player2->combat_id = Battle::combatId(Battle::TEAM2, $this->player2);

        if($this->player1 instanceof AI) {
            $this->player1->loadData();
            $this->player1->health = $this->fighter_health[$this->player1->combat_id];
        }
        if($this->player2 instanceof AI) {
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
     * @throws Exception
     */
    protected function loadFighterFromEntityId(string $entity_id): Fighter {
    switch(Battle::getFighterEntityType($entity_id)) {
        case User::ENTITY_TYPE:
            return User::fromEntityId($entity_id);
        case AI::ID_PREFIX:
            return AI::fromEntityId($this->system, $entity_id);
        default:
            throw new Exception("Invalid entity type! " . Battle::getFighterEntityType($entity_id));
    }
}

    public function getFighter(string $combat_id): ?Fighter {
        if($this->player1->combat_id === $combat_id) {
            return $this->player1;
        }
        if($this->player2->combat_id === $combat_id) {
            return $this->player2;
        }

        return null;
    }

    /**
     * @param string $entity_id
     * @return string
     * @throws Exception
     */
    protected static function getFighterEntityType(string $entity_id): string {
        $entity_id = System::parseEntityId($entity_id);
        return $entity_id->entity_type;
    }

    public function isComplete(): bool {
        return $this->winner;
    }

    public function isPreparationPhase(): bool {
        return $this->prepTimeRemaining() > 0 && in_array($this->battle_type, [Battle::TYPE_FIGHT, Battle::TYPE_CHALLENGE]);
    }

    /**
     * @throws Exception
     */

    public function timeRemaining(): int {
        return Battle::TURN_LENGTH - (time() - $this->turn_time);
    }

    public function prepTimeRemaining(): int {
        return Battle::PREP_LENGTH - (time() - $this->start_time);
    }

    public function updateData() {
        $this->system->query("START TRANSACTION;");

        $this->system->query("UPDATE `battles` SET
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
            `fighter_jutsu_used` = '" . json_encode($this->fighter_jutsu_used) . "'
        WHERE `battle_id` = '{$this->battle_id}' LIMIT 1");
        
        BattleLog::addOrUpdateTurnLog($this->system, $this->battle_id, $this->turn_count, $this->battle_text);

        $this->system->query("COMMIT;");
    }

    public static function combatId(string $team, Fighter $fighter): string {
        return $team . ':' . $fighter->id;
    }
}
