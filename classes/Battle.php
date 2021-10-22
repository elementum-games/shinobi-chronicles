<?php /** @noinspection DuplicatedCode */

/*
five chakra natures
Jutsu clash – elemental factors
DATA STRUCTURE
    player1
    player2
    player1_action (bool)
    player2_action (bool)
    player1_active_element
    player2_active_element
    player1_raw_damage
    player2_raw_damage
    player1_battle_text
    player2_battle_text
    turn_time
    winner (0 or player ID, -1 for tie)
Two players
Keep turn timer
Both users must submit move by end of turn
Moves happen same time
Moves clash - damage comparison with advantage slanted towards elemental advantages
First person to load page calculates damages dealt
if both users have submitted move(player1_action and player2_action)
    run damage calcs, jutsu clash, blah blah blah
else
if both users have not submitted move (check player1_action and player2_action)
-prompt user for turn or send message ("Please wait for other user")
if player has not submitted move
    prompt for it
*/

/*
 *
 * - what turn is it
 * - collect actions
 *      - movements
 *      - attacks (ID/seals, target ID(single) or target tile or target direction(AoE))
 * - end when actions are collected or time elapsed
 *
 *
 * BATTLEFIELD
 * - what tiles are shown based on position
 * - at least 6 tiles
 * - show at least 2 tiles behind each user, to a max of 5(6?) tiles from the opponent
 * - handle:
 *   - movement
 *     - does the movement trigger an effect (tile status effect, or backstab bonus?)
 *     - if players move past each other, stop movement
 *   - player positions
 *   - do attacks hit
 *   - do attacks interact with anything while flying
 *
 * MOVEMENT PHASE
 * - allow same tile
 * - max 2 people from team per tile
 * - opportunity attacks to disincentivize running through opponent
 *
 *
 * ATTACK PHASE
 * - activate conditions (genjutsu)
 * - cast time (nin/gen)
 *   - is interrupted?
 *
 *
 *
 *
 */


/* Types of ninjutsu
- melee
- projectile (single target)
- projectile AoE
- 360 defense
- buff (cloak)
*/

require_once __DIR__ . '/BattleEffectsManager.php';

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

    public static $pve_battle_types = [
        self::TYPE_AI_ARENA,
        self::TYPE_AI_MISSION,
        self::TYPE_AI_RANKUP,
    ];

    private System $system;

    // Components
    private BattleEffectsManager $effects;

    // Properties
    public int $battle_id;
    public int $battle_type;

    public Fighter $player;
    public Fighter $opponent;

    public string $player_side;
    public string $opponent_side;

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

    public array $player_jutsu_used = [];
    public array $player1_jutsu_used;
    public array $player2_jutsu_used;

    public int $turn_time;
    public int $start_time;
    public $winner;

    public bool $spectate = false;

    // Transient vars

    /** @var Jutsu[] */
    public array $default_attacks;

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
     * @param bool   $spectate
     * @param bool   $load_fighters
     * @throws Exception
     */
    public function __construct(
        System $system, User $player, int $battle_id, bool $spectate = false, bool $load_fighters = true
    ) {
        $this->system = $system;

        $this->battle_id = $battle_id;
        $this->player = $player;
        $this->spectate = $spectate;

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

        $this->effects = new BattleEffectsManager(
            $system,
            json_decode($battle['active_effects'], true),
            json_decode($battle['active_genjutsu'], true)
        );

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

        if($player->id == $this->player1_id) {
            $this->player_side = Battle::TEAM1;
            $this->opponent_side = Battle::TEAM2;

            $this->player1 = $player;
            $this->player_jutsu_used =& $this->player1_jutsu_used;
        }
        else if($player->id == $this->player2_id) {
            $this->player_side = Battle::TEAM2;
            $this->opponent_side = Battle::TEAM1;

            $this->player2 = $player;
            $this->player_jutsu_used =& $this->player2_jutsu_used;
        }
        else {
            $this->player_side = Battle::TEAM1;
            $this->opponent_side = Battle::TEAM2;
        }

        $this->default_attacks = $this->getDefaultAttacks();

        if($load_fighters) {
            $this->loadFighters();
        }
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public function checkTurn(): ?string {
        // If someone is not in battle, this will be set
        if($this->winner) {
            return $this->winner;
        }

        if($this->isPreparationPhase()) {
            try {
                if (isset($_POST['attack'])) {
                    $item_id = $_POST['item_id'] ?? null;
                    if ($item_id && $this->player->hasItem($item_id)) {
                        $item = $this->player->items[$item_id];

                        $max_health = $this->player->max_health * (Battle::MAX_PRE_FIGHT_HEAL_PERCENT / 100);

                        if ($this->player->health >= $max_health) {
                            throw new Exception("You can't heal any further!");
                        }
                        if ($item['effect'] === 'heal') {
                            if (--$this->player->items[$item_id]['quantity'] === 0) {
                                unset($this->player->items[$item_id]);
                            }

                            $this->player->health += $item['effect_amount'];
                            if ($this->player->health >= $max_health) {
                                $this->player->health = $max_health;
                            }

                            $this->player->updateData();
                            $this->player->updateInventory();
                            $this->battle_text .= sprintf("%s used a %s and healed for %.2f[br]", $this->player->user_name, $item['name'], $item['effect_amount']);
                            $this->updateData();
                        }
                    }
                }
            }
            catch(Exception $e) {
                $this->system->message($e->getMessage());
            }
            return false;
        }
        // If turn is still active and user hasn't submitted their move, check for action
        if($this->timeRemaining() > 0 && !$this->playerActionSubmitted()) {
            if(!empty($_POST['attack'])) {
                // Run player attack
                try {
                    $jutsu_type = $_POST['jutsu_type'];

                    // Check for handseals if ninjutsu/genjutsu
                    if($jutsu_type == Jutsu::TYPE_NINJUTSU or $jutsu_type == Jutsu::TYPE_GENJUTSU) {
                        if(!$_POST['hand_seals']) {
                            throw new Exception("Please enter hand seals!");
                        }

                        $player_jutsu = $this->getJutsuFromHandSeals($this->player, $_POST['hand_seals']);

                        // Layered genjutsu check
                        if($player_jutsu && $player_jutsu->jutsu_type == Jutsu::TYPE_GENJUTSU && !empty($player_jutsu->parent_jutsu)) {
                            $this->effects->assertParentGenjutsuActive($this->player, $player_jutsu);
                        }
                    }

                    // Check jutsu ID if taijutsu
                    else if($jutsu_type == Jutsu::TYPE_TAIJUTSU) {
                        $jutsu_id = (int)$_POST['jutsu_id'];

                        $player_jutsu = $this->getJutsuFromId($this->player, $jutsu_id);
                    }
                    // Check BL jutsu ID if bloodline jutsu
                    else if($jutsu_type == 'bloodline_jutsu' && $this->player->bloodline_id) {
                        $jutsu_id = (int)$_POST['jutsu_id'];

                        $player_jutsu = null;
                        if(isset($this->player->bloodline->jutsu[$jutsu_id])) {
                            $player_jutsu = $this->player->bloodline->jutsu[$jutsu_id];
                            $player_jutsu->setCombatId($this->player->combat_id);
                        }
                    }
                    else {
                        throw new Exception("Invalid jutsu selection!");
                    }

                    // Check jutsu cooldown
                    if(!$player_jutsu) {
                        throw new Exception("Invalid jutsu!");
                    }
                    if(isset($this->jutsu_cooldowns[$player_jutsu->combat_id])) {
                        throw new Exception("Cannot use that jutsu, it is on cooldown for " . $this->jutsu_cooldowns[$player_jutsu->combat_id] . " more turns!");
                    }

                    if(!$this->player->useJutsu($player_jutsu)) {
                        throw new Exception($this->system->message);
                    }

                    // Check for weapon if non-BL taijutsu
                    $weapon_id = 0;
                    if($jutsu_type == Jutsu::TYPE_TAIJUTSU && !empty($_POST['weapon_id'])) {
                        $weapon_id = (int)$this->system->clean($_POST['weapon_id']);
                        if($weapon_id && $this->player->hasItem($weapon_id)) {
                            if(array_search($weapon_id, $this->player->equipped_weapons) === false) {
                                $weapon_id = 0;
                            }
                        }
                        else {
                            $weapon_id = 0;
                        }
                    }

                    // Log jutsu used
                    $this->setPlayerAction($player_jutsu, $weapon_id);

                    if($this->opponent instanceof AI) {
                        $this->chooseAndSetAIAction($this->opponent);
                    }
                } catch (Exception $e) {
                    $this->system->message($e->getMessage());
                }
            }
        }

        // If time is up or both people have submitted moves, RUN TURN
        if($this->timeRemaining() <= 0 || ($this->player1_action && $this->player2_action)) {
            if($this->player1_action or $this->player2_action) {
                $this->runActions();
            }
            // If neither player moved, update turn timer only
            else {
                $this->turn_time = time();
            }
        }

        $this->checkForWinner();
        $this->updateData();

        return $this->winner;
    }

    private function getJutsuFromHandSeals(Fighter $fighter, string $hand_seals): ?Jutsu {
        /*if(is_array($_POST['hand_seals'])) {
            $seals = array();
            foreach($_POST['hand_seals'] as $seal) {
                if(!is_numeric($seal)) {
                    break;
                }
                $seals[] = $seal;
            }
            $seal_string = implode('-', $seals);
        }*/

        $raw_seals = explode('-', $hand_seals);
        $seals = array();
        foreach($raw_seals as $seal) {
            if(!is_numeric($seal)) {
                break;
            }
            $seals[] = $seal;
        }
        $seal_string = implode('-', $seals);

        $fighter_jutsu = null;
        foreach($this->default_attacks as $id => $attack) {
            if($attack->hand_seals == $seal_string) {
                $fighter_jutsu = $attack;
                break;
            }
        }
        foreach($fighter->jutsu as $id => $jutsu) {
            if($jutsu->hand_seals == $seal_string) {
                $fighter_jutsu = $jutsu;
                break;
            }
        }
        if($fighter_jutsu) {
            $fighter_jutsu->setCombatId($fighter->combat_id);
        }

        return $fighter_jutsu;
    }

    private function getJutsuFromId(Fighter $fighter, int $jutsu_id): ?Jutsu {
        $fighter_jutsu = null;
        if(isset($this->default_attacks[$jutsu_id]) && $this->default_attacks[$jutsu_id]->jutsu_type == Jutsu::TYPE_TAIJUTSU) {
            $fighter_jutsu = $this->default_attacks[$jutsu_id];
        }
        if($fighter->hasEquippedJutsu($jutsu_id) && $fighter->jutsu[$jutsu_id]->jutsu_type == Jutsu::TYPE_TAIJUTSU) {
            $fighter_jutsu = $fighter->jutsu[$jutsu_id];
        }

        if($fighter_jutsu) {
            $fighter_jutsu->setCombatId($fighter->combat_id);
        }

        return $fighter_jutsu;
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

        if($this->player_side == Battle::TEAM1) {
            $this->opponent =& $this->player2;
        }
        else {
            $this->opponent =& $this->player1;
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
            if(!$this->spectate && !$this->isComplete() && $this->player1->battle_id != $this->battle_id) {
                $this->stopBattle();
                return;
            }
        }
        if($this->player2 instanceof User && $this->player2->id != $this->player->id) {
            $this->player2->loadData(User::UPDATE_NOTHING, true);
            if(!$this->spectate && !$this->isComplete() && $this->player2->battle_id != $this->battle_id) {
                $this->stopBattle();
                return;
            }
        }

        $this->player1->getInventory();
        $this->player2->getInventory();

        $this->player1->applyBloodlineBoosts();
        $this->player2->applyBloodlineBoosts();

        $this->effects->applyPassiveEffects($this->player1, $this->player2);
    }

    /**
     * @throws Exception
     */
    public function runActions() {
        $effect_win = false;

        // Run turn effects
        $this->effects->applyActiveEffects(
            $this->player1, $this->player2,
            $effect_win
        );

        // Decrement cooldowns
        if(!empty($this->jutsu_cooldowns)) {
            foreach($this->jutsu_cooldowns as $id=>$cooldown) {
                $this->jutsu_cooldowns[$id]--;
                if($this->jutsu_cooldowns[$id] == 0) {
                    unset($this->jutsu_cooldowns[$id]);
                }
            }
        }

        // Check for each player's action
        $this->battle_text = '';

        // Calculate damage
        $player1_damage = 0;
        $player2_damage = 0;

        /** @var ?Jutsu $player1_jutsu */
        $player1_jutsu = null;
        /** @var ?Jutsu $player2_jutsu */
        $player2_jutsu = null;

        if($this->player1_action) {
            if($this->player1_attack_type == Jutsu::PURCHASE_TYPE_DEFAULT) {
                $player1_jutsu = $this->default_attacks[$this->player1_jutsu_id];
                $player1_damage = $this->player1->calcDamage($player1_jutsu);
                $player1_jutsu->setCombatId($this->player1->combat_id);
            }
            else if($this->player1_attack_type == Jutsu::PURCHASE_TYPE_PURCHASEABLE) {
                $player1_jutsu = $this->player1->jutsu[$this->player1_jutsu_id];
                $player1_damage = $this->player1->calcDamage($player1_jutsu);
                $player1_jutsu->setCombatId($this->player1->combat_id);
            }
            else if($this->player1_attack_type == Jutsu::PURCHASE_TYPE_BLOODLINE) {
                $player1_jutsu = $this->player1->bloodline->jutsu[$this->player1_jutsu_id];
                $player1_damage = $this->player1->calcDamage($this->player1->bloodline->jutsu[$this->player1_jutsu_id]);
                $player1_jutsu->setCombatId($this->player1->combat_id);
            }
            else {
                throw new Exception("Invalid p1 attack type! {$this->player1_attack_type}");
            }

            // Set weapon data into jutsu
            if(($this->player1_attack_type == Jutsu::PURCHASE_TYPE_DEFAULT or $this->player1_attack_type == Jutsu::PURCHASE_TYPE_PURCHASEABLE)
                && $player1_jutsu->jutsu_type == Jutsu::TYPE_TAIJUTSU && $this->player1_weapon_id) {
                // Apply element to jutsu
                if($this->player1->items[$this->player1_weapon_id]['effect'] == 'element') {
                    $player1_jutsu->element = $this->player1->elements['first'];
                    $player1_damage *= 1 + ($this->player1->items[$this->player1_weapon_id]['effect_amount'] / 100);
                }
                // Set effect in jutsu
                else {
                    $player1_jutsu->setWeapon(
                        $this->player1_weapon_id,
                        $this->player1->items[$this->player1_weapon_id]['effect'],
                        $this->player1->items[$this->player1_weapon_id]['effect_amount']
                    );
                }
            }
        }
        if($this->player2_action) {
            if($this->player2_attack_type == Jutsu::PURCHASE_TYPE_DEFAULT) {
                $player2_jutsu = $this->default_attacks[$this->player2_jutsu_id];
                $player2_damage = $this->player2->calcDamage($player2_jutsu);
                $player2_jutsu->setCombatId($this->player2->combat_id);
            }
            else if($this->player2_attack_type == Jutsu::PURCHASE_TYPE_PURCHASEABLE) {
                $player2_jutsu = $this->player2->jutsu[$this->player2_jutsu_id];
                $player2_damage = $this->player2->calcDamage($player2_jutsu);
                $player2_jutsu->setCombatId($this->player2->combat_id);
            }
            else if($this->player2_attack_type == Jutsu::PURCHASE_TYPE_BLOODLINE) {
                $player2_jutsu = $this->player2->bloodline->jutsu[$this->player2_jutsu_id];
                $player2_damage = $this->player2->calcDamage($this->player2->bloodline->jutsu[$this->player2_jutsu_id]);
                $player2_jutsu->setCombatId($this->player2->combat_id);
            }
            else {
                throw new Exception("Invalid player 2 attack type {$this->player2_attack_type}");
            }

            // Set weapon data into jutsu
            if(($this->player2_attack_type == Jutsu::PURCHASE_TYPE_DEFAULT or $this->player2_attack_type == Jutsu::PURCHASE_TYPE_PURCHASEABLE)
                && $player2_jutsu->jutsu_type == Jutsu::TYPE_TAIJUTSU && $this->player2_weapon_id) {
                // Apply element to jutsu
                if($this->player2->items[$this->player2_weapon_id]['effect'] == 'element') {
                    $player2_jutsu->element = $this->player2->elements['first'];
                    $player2_damage *= 1 + ($this->player2->items[$this->player2_weapon_id]['effect_amount'] / 100);
                }
                // Set effect in jutsu
                else {
                    $player2_jutsu->setWeapon(
                        $this->player2_weapon_id,
                        $this->player2->items[$this->player2_weapon_id]['effect'],
                        $this->player2->items[$this->player2_weapon_id]['effect_amount'],
                    );
                }
            }
        }

        if($player1_jutsu && $player1_jutsu->isAllyTargetType()) {
            $player1_jutsu->weapon_id = 0;
            $player1_jutsu->effect_only = true;
        }
        if($player2_jutsu && $player2_jutsu->isAllyTargetType()) {
            $player2_jutsu->weapon_id = 0;
            $player2_jutsu->effect_only = true;
        }

        if($this->system->debug['battle']) {
            echo 'P1: ' . $player1_damage . ' / P2: ' . $player2_damage . '<br />';
        }

        // Collision
        $collision_text = null;
        if($this->player1_action > 0 && $this->player2_action > 0) {
            $collision_text = $this->jutsuCollision($this->player1, $this->player2, $player1_damage, $player2_damage, $player1_jutsu, $player2_jutsu);
        }

        // Apply remaining barrier
        if($player1_jutsu) {
            $this->effects->setBarrier($this->player1, $player1_jutsu);
        }
        if($player2_jutsu) {
            $this->effects->setBarrier($this->player2, $player2_jutsu);
        }

        // Apply damage/effects and set display
        if($this->player1_action) {
            $player1_raw_damage = $player1_damage;
            if($player1_jutsu->jutsu_type != Jutsu::TYPE_GENJUTSU && empty($player1_jutsu->effect_only)) {
                $player1_damage = $this->player2->calcDamageTaken($player1_damage, $player1_jutsu->jutsu_type);
                $this->player2->health -= $player1_damage;
                if($this->player2->health < 0) {
                    $this->player2->health = 0;
                }
            }

            // Weapon effect for taijutsu (IN PROGRESS)
            if($player1_jutsu->weapon_id) {
                if($this->player1->items[$this->player1_weapon_id]['effect'] != 'diffuse') {
                    $this->effects->setEffect($this->player1, $this->player2->combat_id, $player1_jutsu->weapon_effect,
                        $player1_raw_damage
                    );
                }
            }

            // Set cooldowns
            if($player1_jutsu->cooldown > 0) {
                $this->jutsu_cooldowns[$player1_jutsu->combat_id] = $player1_jutsu->cooldown;
            }

            // Effects
            if($player1_jutsu->hasEffect()) {
                if($player1_jutsu->use_type == Jutsu::USE_TYPE_BUFF || in_array($player1_jutsu->effect, BattleEffect::$buff_effects)) {
                    $target_id = $this->player1->combat_id;
                }
                else {
                    $target_id = $this->player2->combat_id;
                }

                $this->effects->setEffect(
                    $this->player1,
                    $target_id,
                    $player1_jutsu,
                    $player1_raw_damage
                );
            }

            $text = $player1_jutsu->battle_text;
            $player1_jutsu_color = Battle::getJutsuTextColor($player1_jutsu->jutsu_type);

            if($player1_jutsu->jutsu_type != Jutsu::TYPE_GENJUTSU && empty($player1_jutsu->effect_only)) {
                $text .= "<p style=\"font-weight:bold;\">
                            {$this->player1->getName()} deals
                                <span style=\"color:{$player1_jutsu_color}\">
                                    " . sprintf('%.2f', $player1_damage) . " damage
                                </span>
                            to {$this->player2->getName()}.
                        </p>";
            }
            if($this->effects->hasDisplays($this->player1)) {
                $text .= '<p>' . $this->effects->getDisplayText($this->player1) . '</p>';
            }

            if($player1_jutsu->hasEffect()){
                $text .= "<p style=\"font-style:italic;margin-top:3px;\">" .
                    $this->system->clean($this->effects->getAnnouncementText($player1_jutsu->effect)) .
                    "</p>";
            }

            if($player1_jutsu->weapon_id) {
                $text .= "<p style=\"font-style:italic;margin-top:3px;\">" .
                    $this->system->clean($this->effects->getAnnouncementText($player1_jutsu->weapon_effect->effect)) .
                    "</p>";
            }

            $this->battle_text .= $this->parseCombatText($text, $this->player1, $this->player2);
        }
        else {
            $this->battle_text .= $this->player1->getName() . ' stood still and did nothing.';
            if($this->effects->hasDisplays($this->player1)) {
                $this->battle_text .= '<p>' .
                    $this->parseCombatText(
                        $this->effects->getDisplayText($this->player1),
                        $this->player1,
                        $this->player2
                    ) .
                '</p>';
            }
        }

        if($collision_text) {
            $collision_text = $this->parseCombatText($collision_text, $this->player1, $this->player2);
            $this->battle_text .= '[br][hr]' . $this->system->clean($collision_text);
        }
        $this->battle_text .= '[br][hr]';

        // Apply damage/effects and set display
        if($this->player2_action) {
            $player2_raw_damage = $player2_damage;
            if($player2_jutsu->jutsu_type != Jutsu::TYPE_GENJUTSU && empty($player2_jutsu->effect_only)) {
                $player2_damage = $this->player1->calcDamageTaken($player2_damage, $player2_jutsu->jutsu_type);
                $this->player1->health -= $player2_damage;
                if($this->player1->health < 0) {
                    $this->player1->health = 0;
                }
            }

            // Weapon effect for taijutsu (IN PROGRESS)
            if($player2_jutsu->weapon_id) {
                if($this->player2->items[$this->player2_weapon_id]['effect'] != 'diffuse') {
                    $this->effects->setEffect($this->player2, $this->player1->combat_id, $player2_jutsu->weapon_effect,
                        $player2_raw_damage
                    );
                }
            }

            // Set cooldowns
            if($player2_jutsu->cooldown > 0) {
                $this->jutsu_cooldowns[$player2_jutsu->combat_id] = $player2_jutsu->cooldown;
            }

            // Genjutsu/effects
            if($player2_jutsu->hasEffect()) {
                if($player2_jutsu->use_type == Jutsu::USE_TYPE_BUFF || in_array($player2_jutsu->effect, BattleEffect::$buff_effects)) {
                    $target_id = $this->player2->combat_id;
                }
                else {
                    $target_id = $this->player1->combat_id;
                }
                $this->effects->setEffect($this->player2, $target_id, $player2_jutsu, $player2_raw_damage);
            }

            //set opponent jutsu text color
            $player2_jutsu_color = Battle::getJutsuTextColor($player2_jutsu->jutsu_type);

            $text = $player2_jutsu->battle_text;
            if($player2_jutsu->jutsu_type != Jutsu::TYPE_GENJUTSU && empty($player2_jutsu->effect_only)) {
                $text .= "<p style=\"font-weight:bold;\">
                            {$this->player2->getName()} deals
                                <span style=\"color:{$player2_jutsu_color}\">
                                    " . sprintf('%.2f', $player2_damage) . " damage
                                </span>
                            to {$this->player1->getName()}.
                        </p>";
            }
            if($this->effects->hasDisplays($this->player2)) {
                $text .= "<p>" . $this->effects->getDisplayText($this->player2) . "</p>";
            }

            if($player2_jutsu->hasEffect()){
                $text .= "<p style=\"font-style:italic;margin-top:3px;\">" .
                    $this->system->clean($this->effects->getAnnouncementText($player2_jutsu->effect)) .
                    "</p>";
            }

            if($player2_jutsu->weapon_id) {
                $text .= "<p style=\"font-style:italic;margin-top:3px;\">" .
                    $this->system->clean($this->effects->getAnnouncementText($player2_jutsu->weapon_effect->effect)) .
                    "</p>";
            }

            $this->battle_text .= $this->parseCombatText($text, $this->player2, $this->player1);
        }
        else {
            $this->battle_text .= $this->player2->getName() . ' stood still and did nothing.';
            if($this->effects->hasDisplays($this->player2)) {
                $this->battle_text .= "<p>" . $this->parseCombatText(
                    $this->effects->getDisplayText($this->player2),
                    $this->player2,
                    $this->player1
                ) . "</p>";
            }
        }

        // Update battle
        $this->turn_time = time();
        $this->player1_action = 0;
        $this->player2_action = 0;

        $this->player1_health = $this->player1->health;
        $this->player2_health = $this->player2->health;

        $this->player1->updateData();
        $this->player1->updateInventory();

        $this->player2->updateData();
        $this->player2->updateInventory();
    }

    /**
     * @throws Exception
     */
    public function renderBattle() {
        global $self_link;

        if($this->player === $this->player1) {
            $player = $this->player1;
            $opponent = $this->player2;
        }
        else if($this->player === $this->player2) {
            $player = $this->player2;
            $opponent = $this->player1;
        }
        else {
            $player = $this->player1;
            $opponent = $this->player2;
        }

        $refresh_link = $this->spectate ? "{$self_link}&battle_id={$this->battle_id}" : $self_link;

        $battle = $this;
        $system = $this->system;

        require 'templates/battle/battle_interface.php';
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

    public function jutsuCollision(
        Fighter $player1, Fighter $player2, &$player_damage, &$opponent_damage, $player_jutsu, $opponent_jutsu
    ) {
        $collision_text = '';
        /*
        $weapon = array(
                            'power' => $player1_jutsu->power,
                            'effect' => $player1->items[$battle['player1_weapon_id']]['effect'],
                            'effect_length' => 2,
                            'effect_amount' => $player1->items[$battle['player1_weapon_id']]['effect_amount'],
                            'jutsu_type' => 'taijutsu'
                        );
        */

        // Elemental interactions
        if(!empty($player_jutsu->element) && !empty($opponent_jutsu->element)) {
            $player_jutsu->element = strtolower($player_jutsu->element);
            $opponent_jutsu->element = strtolower($opponent_jutsu->element);
            // Fire > Wind > Lightning > Earth > Water > Fire
            if($player_jutsu->element == 'fire') {
                if($opponent_jutsu->element == 'wind') {
                    $opponent_damage *= 0.8;
                }
                else if($opponent_jutsu->element == 'water') {
                    $player_damage *= 0.8;
                }
            }
            else if($player_jutsu->element == 'wind') {
                if($opponent_jutsu->element == 'lightning') {
                    $opponent_damage *= 0.8;
                }
                else if($opponent_jutsu->element == 'fire') {
                    $player_damage *= 0.8;
                }
            }
            else if($player_jutsu->element == 'lightning') {
                if($opponent_jutsu->element == 'earth') {
                    $opponent_damage *= 0.8;
                }
                else if($opponent_jutsu->element == 'wind') {
                    $player_damage *= 0.8;
                }
            }
            else if($player_jutsu->element == 'earth') {
                if($opponent_jutsu->element == 'water') {
                    $opponent_damage *= 0.8;
                }
                else if($opponent_jutsu->element == 'lightning') {
                    $player_damage *= 0.8;
                }
            }
            else if($player_jutsu->element == 'water') {
                if($opponent_jutsu->element == 'fire') {
                    $opponent_damage *= 0.8;
                }
                else if($opponent_jutsu->element == 'earth') {
                    $player_damage *= 0.8;
                }
            }
        }

        // Barriers
        if($player_jutsu->use_type == Jutsu::USE_TYPE_BARRIER) {
            $player_jutsu->effect_amount = $player_damage;
            $player1->barrier += $player_damage;
            $player_damage = 0;
        }
        if($opponent_jutsu->use_type == Jutsu::USE_TYPE_BARRIER) {
            $opponent_jutsu->effect_amount = $opponent_damage;
            $player2->barrier += $opponent_damage;
            $opponent_damage = 0;
        }
        if($player1->barrier && $opponent_jutsu->jutsu_type != Jutsu::TYPE_GENJUTSU) {
            // Block damage from opponent's attack
            if($player1->barrier >= $opponent_damage) {
                $block_amount = $opponent_damage;
            }
            else {
                $block_amount = $player1->barrier;
            }
            $block_percent = ($opponent_damage >= 1) ? ($block_amount / $opponent_damage) * 100 : 100;
            $player1->barrier -= $block_amount;
            $opponent_damage -= $block_amount;
            if($player1->barrier < 0) {
                $player1->barrier = 0;
            }
            if($opponent_damage < 0) {
                $opponent_damage = 0;
            }
            // Set display
            $block_percent = round($block_percent, 1);
            $collision_text .= "[player]'s barrier blocked $block_percent% of [opponent]'s damage![br]";
        }
        if($player2->barrier && $player_jutsu->jutsu_type != Jutsu::TYPE_GENJUTSU) {
            // Block damage from opponent's attack
            if($player2->barrier >= $player_damage) {
                $block_amount = $player_damage;
            }
            else {
                $block_amount = $player2->barrier;
            }
            $block_percent = ($player_damage >= 1) ? ($block_amount / $player_damage) * 100 : 100;
            $player2->barrier -= $block_amount;
            $player_damage -= $block_amount;
            if($player2->barrier < 0) {
                $player2->barrier = 0;
            }
            if($player_damage < 0) {
                $player_damage = 0;
            }
            // Set display
            $block_percent = round($block_percent, 1);
            $collision_text .= "[opponent]'s barrier blocked $block_percent% of [player]'s damage![br]";
        }

        // Quit if barrier was used by one person (no collision remaining)
        if($player_jutsu->use_type == Jutsu::USE_TYPE_BARRIER or $opponent_jutsu->use_type == Jutsu::USE_TYPE_BARRIER) {
            return $this->parseCombatText(
                $collision_text,
                $player1,
                $player2
            );
        }

        // Weapon diffuse (tai diffuse nin)
        if($player_jutsu->weapon_id && $player_jutsu->weapon_effect->effect == 'diffuse' && $opponent_jutsu->jutsu_type == Jutsu::TYPE_NINJUTSU) {
            if($opponent_damage <= 0){
                $player_diffuse_percent = 0;
            }
            else {
                $player_diffuse_percent = round(
                    $player_damage / $opponent_damage * ($player_jutsu->weapon_effect->effect_amount / 100),
                    1
                );

                if($player_diffuse_percent > Battle::MAX_DIFFUSE_PERCENT) {
                    $player_diffuse_percent = Battle::MAX_DIFFUSE_PERCENT;
                }
            }
        }
        if($opponent_jutsu->weapon_id && $opponent_jutsu->weapon_effect->effect == 'diffuse' &&  $player_jutsu->jutsu_type == Jutsu::TYPE_NINJUTSU) {
            if($player_damage <= 0){
                $opponent_diffuse_percent = 0;
            }
            else {
                $opponent_diffuse_percent = round(
                    $opponent_damage / $player_damage * ($opponent_jutsu->weapon_effect->effect_amount / 100),
                    1
                );
            }

            if($opponent_diffuse_percent > Battle::MAX_DIFFUSE_PERCENT) {
                $opponent_diffuse_percent = Battle::MAX_DIFFUSE_PERCENT;
            }
        }
        if(!empty($player_diffuse_percent)) {
            $opponent_damage *= 1 - $player_diffuse_percent;
            $collision_text .= "[player] diffused " . ($player_diffuse_percent * 100) . "% of [opponent]'s damage![br]";
        }
        if(!empty($opponent_diffuse_percent)) {
            $player_damage *= 1 - $opponent_diffuse_percent;
            $collision_text .= "[opponent] diffused " . ($opponent_diffuse_percent * 100) . "% of [player]'s damage![br]";
        }

        if($player_jutsu->jutsu_type == Jutsu::TYPE_GENJUTSU or $opponent_jutsu->jutsu_type == Jutsu::TYPE_GENJUTSU) {
            return false;
        }

        // Apply buffs/nerfs
        $player_speed = $player1->speed + $player1->speed_boost - $player1->speed_nerf;
        $player_speed = 50 + ($player_speed * 0.5);
        if($player_speed <= 0) {
            $player_speed = 1;
        }
        $player_cast_speed = $player1->cast_speed + $player1->cast_speed_boost - $player1->cast_speed_nerf;
        $player_cast_speed = 50 + ($player_cast_speed * 0.5);
        if($player_cast_speed <= 0) {
            $player_cast_speed = 1;
        }

        $opponent_speed = $player2->speed + $player2->speed_boost - $player2->speed_nerf;
        $opponent_speed = 50 + ($opponent_speed * 0.5);
        if($opponent_speed <= 0) {
            $opponent_speed = 1;
        }
        $opponent_cast_speed = $player2->cast_speed + $player2->cast_speed_boost - $player2->cast_speed_nerf;
        $opponent_cast_speed = 50 + ($opponent_cast_speed * 0.5);
        if($opponent_cast_speed <= 0) {
            $opponent_cast_speed = 1;
        }

        if($this->system->debug['jutsu_collision']) {
            echo "Player1({$player1->getName()}): {$player1->speed} ({$player1->speed_boost} - {$player1->speed_nerf})<br />";
            echo "Player2({$player2->getName()}): {$player2->speed} ({$player2->speed_boost} - {$player2->speed_nerf})<br />";
        }

        // Ratios for damage reduction
        $speed_ratio = 0.8;
        $cast_speed_ratio = 0.8;
        $max_damage_reduction = 0.5;
        if($player_jutsu->jutsu_type == Jutsu::TYPE_NINJUTSU) {
            // Nin vs Nin
            if($opponent_jutsu->jutsu_type == Jutsu::TYPE_NINJUTSU) {
                if($player_cast_speed >= $opponent_cast_speed) {
                    $damage_reduction = ($player_cast_speed / $opponent_cast_speed) - 1.0;
                    $damage_reduction = round($damage_reduction * $cast_speed_ratio, 2);
                    if($damage_reduction > $max_damage_reduction) {
                        $damage_reduction = $max_damage_reduction;
                    }
                    if($damage_reduction >= 0.01) {
                        $opponent_damage *= 1 - $damage_reduction;
                        $collision_text .= "[player] cast [gender2] jutsu before [opponent] cast, negating " .
                            ($damage_reduction * 100) . "% of [opponent]'s damage!";
                    }
                }
                else {
                    $damage_reduction = ($opponent_cast_speed / $player_cast_speed) - 1.0;
                    $damage_reduction = round($damage_reduction * $cast_speed_ratio, 2);
                    if($damage_reduction > $max_damage_reduction) {
                        $damage_reduction = $max_damage_reduction;
                    }
                    if($damage_reduction >= 0.01) {
                        $player_damage *= 1 - $damage_reduction;
                        $collision_text .= "[opponent] cast their jutsu before [player] cast, negating " .
                            ($damage_reduction * 100) . "% of [player]'s damage!";
                    }
                }
            }
            // Nin vs Tai
            else if($opponent_jutsu->jutsu_type == Jutsu::TYPE_TAIJUTSU) {
                if($player_cast_speed >= $opponent_speed) {
                    $damage_reduction = ($player_cast_speed / $opponent_speed) - 1.0;
                    $damage_reduction = round($damage_reduction * $cast_speed_ratio, 2);
                    if($damage_reduction > $max_damage_reduction) {
                        $damage_reduction = $max_damage_reduction;
                    }
                    if($damage_reduction >= 0.01) {
                        $opponent_damage *= 1 - $damage_reduction;
                        $collision_text .= "[player] cast [gender2] jutsu before [opponent] attacked, negating " . ($damage_reduction * 100) .
                            "% of [opponent]'s damage!";
                    }
                }
                else {
                    $damage_reduction = ($opponent_speed / $player_cast_speed) - 1.0;
                    $damage_reduction = round($damage_reduction * $speed_ratio, 2);
                    if($damage_reduction > $max_damage_reduction) {
                        $damage_reduction = $max_damage_reduction;
                    }
                    if($damage_reduction >= 0.01) {
                        $player_damage *= 1 - $damage_reduction;
                        $collision_text .= "[opponent] swiftly evaded " . ($damage_reduction * 100) . "% of [player]'s damage!";
                    }
                }
            }
        }

        // Taijutsu clash
        else if($player_jutsu->jutsu_type == Jutsu::TYPE_TAIJUTSU) {
            // Tai vs Tai
            if($opponent_jutsu->jutsu_type == Jutsu::TYPE_TAIJUTSU) {
                if($player_speed >= $opponent_speed) {
                    $damage_reduction = ($player_speed / $opponent_speed) - 1.0;
                    $damage_reduction = round($damage_reduction * $speed_ratio, 2);
                    if($damage_reduction > $max_damage_reduction) {
                        $damage_reduction = $max_damage_reduction;
                    }
                    if($damage_reduction >= 0.01) {
                        $opponent_damage *= 1 - $damage_reduction;
                        $collision_text .= "[player] swiftly evaded " . ($damage_reduction * 100) . "% of [opponent]'s damage!";
                    }
                }
                else {
                    $damage_reduction = ($opponent_speed / $player_speed) - 1.0;
                    $damage_reduction = round($damage_reduction * $speed_ratio, 2);
                    if($damage_reduction > $max_damage_reduction) {
                        $damage_reduction = $max_damage_reduction;
                    }
                    if($damage_reduction >= 0.01) {
                        $player_damage *= 1 - $damage_reduction;
                        $collision_text .= "[opponent] swiftly evaded " . ($damage_reduction * 100) . "% of [player]'s damage!";
                    }
                }
            }
            else if($opponent_jutsu->jutsu_type == Jutsu::TYPE_NINJUTSU) {
                if($player_speed >= $opponent_cast_speed) {
                    $damage_reduction = ($player_speed / $opponent_cast_speed) - 1.0;
                    $damage_reduction = round($damage_reduction * $speed_ratio, 2);
                    if($damage_reduction > $max_damage_reduction) {
                        $damage_reduction = $max_damage_reduction;
                    }
                    if($damage_reduction >= 0.01) {
                        $opponent_damage *= 1 - $damage_reduction;
                        $collision_text .= "[player] swiftly evaded " . ($damage_reduction * 100) . "% of [opponent]'s damage!";
                    }
                }
                else {
                    $damage_reduction = ($opponent_cast_speed / $player_speed) - 1.0;
                    $damage_reduction = round($damage_reduction * $cast_speed_ratio, 2);
                    if($damage_reduction > $max_damage_reduction) {
                        $damage_reduction = $max_damage_reduction;
                    }
                    if($damage_reduction >= 0.01) {
                        $player_damage *= 1 - $damage_reduction;
                        $collision_text .= "[opponent] cast their jutsu before [player] attacked, negating " . ($damage_reduction * 100) .
                            "% of [player]'s damage!";
                    }
                }
            }
        }

        // Parse text
        $collision_text = $this->parseCombatText($collision_text, $player1, $player2);
        return $collision_text;
    }

    protected function setPlayerAction(Jutsu $jutsu, $weapon_id) {
        if($this->player_side == Battle::TEAM1) {
            $this->player1_action = 1;
            $this->player1_jutsu_id = $jutsu->id;
            $this->player1_weapon_id = $weapon_id;
            $this->player1_attack_type = $jutsu->purchase_type;

            if(isset($this->player1_jutsu_used[$jutsu->combat_id])) {
                $this->player1_jutsu_used[$jutsu->combat_id]['count']++;
            }
            else {
                $this->player1_jutsu_used[$jutsu->combat_id] = array();
                $this->player1_jutsu_used[$jutsu->combat_id]['jutsu_type'] = $jutsu->jutsu_type;
                $this->player1_jutsu_used[$jutsu->combat_id]['count'] = 1;
            }
        }
        else {
            $this->player2_action = 1;
            $this->player2_jutsu_id = $jutsu->id;
            $this->player2_weapon_id = $weapon_id;
            $this->player2_attack_type = $jutsu->purchase_type;

            if(isset($this->player2_jutsu_used[$jutsu->combat_id])) {
                $this->player2_jutsu_used[$jutsu->combat_id]['count']++;
            }
            else {
                $this->player2_jutsu_used[$jutsu->combat_id] = array();
                $this->player2_jutsu_used[$jutsu->combat_id]['jutsu_type'] = $jutsu->jutsu_type;
                $this->player2_jutsu_used[$jutsu->combat_id]['count'] = 1;
            }
        }
    }

    protected function chooseAndSetAIAction(AI $ai) {
        $jutsu = $ai->chooseMove();

        $attack_id = $jutsu->id;
        $weapon_id = 0;
        $attack_type = Jutsu::PURCHASE_TYPE_PURCHASEABLE;

        if($this->opponent_side == Battle::TEAM1) {
            $this->player1_action = 1;
            $this->player1_jutsu_id = $attack_id;
            $this->player1_weapon_id = $weapon_id;
            $this->player1_attack_type = $attack_type;
        }
        else {
            $this->player2_action = 1;
            $this->player2_jutsu_id = $attack_id;
            $this->player2_weapon_id = $weapon_id;
            $this->player2_attack_type = $attack_type;
        }
    }

    private function updateData() {
        if($this->spectate) {
            return;
        }

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

            `active_effects` = '" . json_encode($this->effects->active_effects) . "',
            `active_genjutsu` = '" . json_encode($this->effects->active_genjutsu) . "',

            `jutsu_cooldowns` = '" . json_encode($this->jutsu_cooldowns) . "',

            `player1_jutsu_used` = '" . json_encode($this->player1_jutsu_used) . "',
            `player2_jutsu_used` = '" . json_encode($this->player2_jutsu_used) . "',

            `turn_time` = {$this->turn_time},
            `winner` = '{$this->winner}'

        WHERE `battle_id` = '{$this->battle_id}' LIMIT 1");
    }

    protected function stopBattle() {
        $this->winner = Battle::DRAW;
        $this->updateData();
    }


    // Status checks

    public function isComplete(): bool {
        return $this->winner;
    }

    public function isPreparationPhase(): bool {
        return $this->prepTimeRemaining() > 0 && in_array($this->battle_type, [self::TYPE_FIGHT, self::TYPE_CHALLENGE]);
    }

    /**
     * @throws Exception
     */
    public function isPlayerWinner(): bool {
        if(!$this->isComplete()) {
            throw new Exception("Cannot call isPlayerWinner() check before battle is complete!");
        }

        return $this->winner === $this->player_side;
    }

    /**
     * @throws Exception
     */
    public function isOpponentWinner(): bool {
        if(!$this->isComplete()) {
            throw new Exception("Cannot call isPlayerWinner() check before battle is complete!");
        }

        return $this->winner === $this->opponent_side;
    }

    /**
     * @throws Exception
     */
    public function isDraw(): bool {
        if(!$this->isComplete()) {
            throw new Exception("Cannot call isPlayerWinner() check before battle is complete!");
        }

        return $this->winner === Battle::DRAW;
    }

    private function checkForWinner(): string {
        if($this->isComplete()) {
            return $this->winner;
        }

        if($this->player1->health > 0 && $this->player2->health <= 0) {
            $this->winner = Battle::TEAM1;
        }
        else if($this->player2->health > 0 && $this->player1->health <= 0) {
            $this->winner = Battle::TEAM2;
        }
        else if($this->player1->health <= 0 && $this->player2->health <= 0) {
            $this->winner = Battle::DRAW;
        }

        if($this->winner && !$this->spectate) {
            $this->player->updateInventory();
        }

        return $this->winner;
    }

    public function playerActionSubmitted(): bool {
        if($this->player_side == Battle::TEAM1 && $this->player1_action) {
            return true;
        }
        if($this->player_side == Battle::TEAM2 && $this->player2_action) {
            return true;
        }
        return false;
    }

    public function opponentActionSubmitted(): bool {
        if($this->opponent_side == Battle::TEAM1 && $this->player1_action) {
            return true;
        }
        if($this->opponent_side == Battle::TEAM2 && $this->player2_action) {
            return true;
        }
        return false;
    }

    public function timeRemaining(): int {
        return Battle::TURN_LENGTH - (time() - $this->turn_time);
    }

    public function prepTimeRemaining(): int {
        return Battle::PREP_LENGTH - (time() - $this->start_time);
    }

    // Utils
    protected function getDefaultAttacks(): array {
        $default_attacks = [];

        $query = "SELECT * FROM `jutsu` WHERE `purchase_type`='1'";
        $result = $this->system->query($query);
        while($row = $this->system->db_fetch($result)) {
            $default_attacks[$row['jutsu_id']] = Jutsu::fromArray($row['jutsu_id'], $row);
        }
        return $default_attacks;
    }

    private function parseCombatText(string $text, Fighter $attacker, Fighter $target): string {
        return str_replace(
            [
                '[player]',
                '[opponent]',
                '[gender]',
                '[gender2]'
            ],
            [
                $attacker->getName(),
                $target->getName(),
                $attacker->getSingularPronoun(),
                $attacker->getPossessivePronoun(),
            ],
            $text
        );
    }

    private static function getJutsuTextColor($jutsu_type): string {
        switch ($jutsu_type) {
            case Jutsu::TYPE_NINJUTSU:
                return "blue";
            case Jutsu::TYPE_TAIJUTSU:
                return "red";
            case Jutsu::TYPE_GENJUTSU:
                return "purple";
            case 'none':
            default:
                return "black";
        }
    }
}
