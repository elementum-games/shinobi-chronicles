<?php /** @noinspection DuplicatedCode */

require_once __DIR__ . '/BattleEffectsManager.php';

class Battle {
    const TYPE_AI_ARENA = 1;
    const TYPE_SPAR = 2;
    const TYPE_FIGHT = 3;
    const TYPE_CHALLENGE = 4;
    const TYPE_AI_MISSION = 5;
    const TYPE_AI_RANKUP = 6;

    const TURN_LENGTH = 40;
    const PREP_LENGTH = 15;

    const MAX_PRE_FIGHT_HEAL_PERCENT = 85;

    const TEAM1 = 'T1';
    const TEAM2 = 'T2';
    const DRAW = 'DRAW';

    // Minimum % (of itself) a debuff can be reduced to with debuff resist
    const MIN_DEBUFF_RATIO = 0.1;
    const MAX_DIFFUSE_PERCENT = 0.75;

    public static $pve_battle_types = [
        self::TYPE_AI_ARENA,
        self::TYPE_AI_MISSION,
        self::TYPE_AI_RANKUP,
    ];

    private System $system;

    public string $raw_active_effects;
    public string $raw_active_genjutsu;

    // Properties
    public int $battle_id;
    public int $battle_type;

    public Fighter $player;
    public Fighter $opponent;

    public string $player1_id;
    public string $player2_id;

    public Fighter $player1;
    public Fighter $player2;

    public float $player1_health;
    public float $player2_health;

    public $player1_action;
    public $player2_action;

    public $player1_attack_type;
    public $player2_attack_type;

    public int $player1_jutsu_id;
    public int $player2_jutsu_id;

    public $player1_weapon_id;
    public $player2_weapon_id;

    public ?string $player1_battle_text;
    public ?string $player2_battle_text;

    public string $battle_text;

    public $jutsu_cooldowns;

    public array $player1_jutsu_used;
    public array $player2_jutsu_used;

    public int $turn_time;
    public int $start_time;
    public $winner;

    /**
     * @param System  $system
     * @param Fighter $player1
     * @param Fighter $player2
     * @param int     $battle_type
     * @return mixed
     * @throws Exception
     */
    public static function start(
        System $system, Fighter $player1, Fighter $player2, int $battle_type
    ) {
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

        $system->query(
            "INSERT INTO `battles`
                (
                 `battle_type`,
                 `player1`,
                 `player2`,
                 `turn_time`,
                 `start_time`,
                 player1_health,
                 player2_health,
                 player1_weapon_id,
                 player2_weapon_id,
                 player1_attack_type,
                 player2_attack_type,
                 player1_jutsu_used,
                 player2_jutsu_used,
                 active_effects,
                 battle_text,
                 active_genjutsu,
                 jutsu_cooldowns,
                 winner
               ) VALUES
               (
                {$battle_type},
                '$player1->id',
                '$player2->id',
                " . (time() + self::PREP_LENGTH) . ",
                " . time() . ",
                {$player1->health},
                {$player2->health},
                0,
                0,
                '',
                '',
                '{$json_empty_array}',
                '{$json_empty_array}',
                '{$json_empty_array}',
                '',
                '{$json_empty_array}',
                '{$json_empty_array}',
                ''
                )"
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

        $this->battle_type = $battle['battle_type'];

        $this->player1_id = $battle['player1'];
        $this->player2_id = $battle['player2'];

        $this->player1_health = $battle['player1_health'];
        $this->player2_health = $battle['player2_health'];

        $this->player1_action = $battle['player1_action'];
        $this->player2_action = $battle['player2_action'];

        $this->player1_attack_type = $battle['player1_attack_type'];
        $this->player2_attack_type = $battle['player2_attack_type'];

        $this->player1_jutsu_id = (int)$battle['player1_jutsu_id'];
        $this->player2_jutsu_id = (int)$battle['player2_jutsu_id'];

        $this->player1_weapon_id = $battle['player1_weapon_id'];
        $this->player2_weapon_id = $battle['player2_weapon_id'];

        $this->player1_battle_text = $battle['player1_battle_text'];
        $this->player2_battle_text = $battle['player2_battle_text'];

        $this->battle_text = $battle['battle_text'];

        $this->jutsu_cooldowns = json_decode($battle['jutsu_cooldowns'] ?? "[]", true);

        $this->player1_jutsu_used = json_decode($battle['player1_jutsu_used'], true);
        $this->player2_jutsu_used = json_decode($battle['player2_jutsu_used'], true);

        $this->turn_time = $battle['turn_time'];
        $this->start_time = $battle['start_time'];

        $this->winner = $battle['winner'];
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

        $this->player1->combat_id = Battle::TEAM1 . ':' . $this->player1->id;
        $this->player2->combat_id = Battle::TEAM2 . ':' . $this->player2->id;

        if($this->player1 instanceof AI) {
            $this->player1->loadData();
            $this->player1->health = $this->player1_health;
        }
        if($this->player2 instanceof AI) {
            $this->player2->loadData();
            $this->player2->health = $this->player2_health;
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
        $this->system->query("UPDATE `battles` SET
            `player1_action` = '{$this->player1_action}',
            `player2_action` = '{$this->player2_action}',

            `player1_health` = {$this->player1_health},
            `player2_health` = {$this->player2_health},

            `player1_attack_type` = '{$this->player1_attack_type}',
            `player2_attack_type` = '{$this->player2_attack_type}',

            `player1_jutsu_id` = {$this->player1_jutsu_id},
            `player2_jutsu_id` = {$this->player2_jutsu_id},

            `player1_weapon_id` = {$this->player1_weapon_id},
            `player2_weapon_id` = {$this->player2_weapon_id},

            `player1_battle_text` = '{$this->player1_battle_text}',
            `player2_battle_text` = '{$this->player2_battle_text}',

            `battle_text` = '{$this->battle_text}',

            `active_effects` = '" . $this->raw_active_effects . "',
            `active_genjutsu` = '" . $this->raw_active_genjutsu . "',

            `jutsu_cooldowns` = '" . json_encode($this->jutsu_cooldowns) . "',

            `player1_jutsu_used` = '" . json_encode($this->player1_jutsu_used) . "',
            `player2_jutsu_used` = '" . json_encode($this->player2_jutsu_used) . "',

            `turn_time` = {$this->turn_time},
            `winner` = '{$this->winner}'

        WHERE `battle_id` = '{$this->battle_id}' LIMIT 1");
    }

}
