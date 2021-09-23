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

class Battle {
    const TYPE_AI_ARENA = 1;
    const TYPE_SPAR = 2;
    const TYPE_FIGHT = 3;
    const TYPE_CHALLENGE = 4;
    const TYPE_AI_MISSION = 5;
    const TYPE_AI_RANKUP = 6;

    const TURN_LENGTH = 40;

    const TEAM1 = 'T1';
    const TEAM2 = 'T2';
    const DRAW = 'DRAW';

    // Minimum % (of itself) a debuff can be reduced to with debuff resist
    const MIN_DEBUFF_RATIO = 0.1;
    const MAX_DIFFUSE_PERCENT = 0.75;

    private System $system;

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

    public $player1_jutsu_id;
    public $player2_jutsu_id;

    public $player1_weapon_id;
    public $player2_weapon_id;

    public ?string $player1_battle_text;
    public ?string $player2_battle_text;

    public string $battle_text;

    public $active_effects;
    public $active_genjutsu;

    public $jutsu_cooldowns;

    public $player_jutsu_used;
    public $player1_jutsu_used;
    public $player2_jutsu_used;

    public int $turn_time;
    public $winner;

    // Transient vars
    public array $default_attacks;

    /**
     * @param System  $system
     * @param Fighter $player1
     * @param Fighter $player2
     * @param int     $battle_type
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
                " . (time() + 20) . ",
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
        $battle_id = $system->db_insert_id;

        if($player1 instanceof User) {
            $player1->battle_id = $battle_id;
            $player1->updateData();
        }
        if($player2 instanceof User) {
            $player2->battle_id = $battle_id;
            $player2->updateData();
        }
    }

    /**
     * Battle constructor.
     * @param System $system
     * @param User   $player
     * @param int    $battle_id
     * @throws Exception
     */
    public function __construct(System $system, User $player, int $battle_id) {
        $this->system = $system;

        $this->battle_id = $battle_id;
        $this->player = $player;

        $result = $this->system->query(
            "SELECT * FROM `battles` WHERE `battle_id`='{$battle_id}' LIMIT 1"
        );
        if($this->system->db_num_rows == 0) {
            if($player->battle_id = $battle_id) {
                $player->battle_id = 0;
            }
            throw new Exception("Invalid battle!");
        }

        $battle = $this->system->db_fetch($result);

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
    
        $this->active_effects = json_decode($battle['active_effects'], true);
        $this->active_genjutsu = json_decode($battle['active_genjutsu'], true);

        $this->jutsu_cooldowns = json_decode($battle['jutsu_cooldowns'] ?? "[]", true);
    
        $this->player1_jutsu_used = json_decode($battle['player1_jutsu_used'], true);
        $this->player2_jutsu_used = json_decode($battle['player2_jutsu_used'], true);
    
        $this->turn_time = $battle['turn_time'];
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

        $this->default_attacks = $this->getDefaultAttacks();
    }

    public function determineEffectAnnouncementText(string  $attackingPlayer, string $defendingPlayer, string $effect) : string{
        $announcement_text = "";
        switch($effect){
            case 'taijutsu_nerf':
                $announcement_text = "{$defendingPlayer}'s Taijutsu is being lowered.";
                break;
            case 'ninjutsu_nerf':
                $announcement_text = "{$defendingPlayer}'s Ninjutsu is being lowered.";
                break;
            case 'genjutsu_nerf':
                $announcement_text = "{$defendingPlayer}'s Genjutsu is being lowered.";
                break;
            case 'intelligence_nerf':
            case 'daze':
                $announcement_text = "{$defendingPlayer}'s Intelligence is being lowered.";
                break;
            case 'willpower_nerf':
                $announcement_text = "{$defendingPlayer}'s Willpower is being lowered.";
                break;
            case 'cast_speed_nerf':
                $announcement_text = "{$defendingPlayer}'s Cast Speed is being lowered.";
                break;
            case 'speed_nerf':
            case 'cripple':
                $announcement_text = "{$defendingPlayer}'s Speed is being lowered.";
                break;
            case 'residual_damage':
                $announcement_text = "{$defendingPlayer} is taking Residual Damage.";
                break;
            case 'drain_chakra':
                $announcement_text = "{$defendingPlayer}'s Chakra is being drained.";
                break;
            case 'drain_stamina':
                $announcement_text = "{$defendingPlayer}'s Stamina is being drained.";
                break;
            case 'taijutsu_boost':
                $announcement_text = "{$attackingPlayer}'s Taijutsu is being increased.";
                break;
            case 'ninjutsu_boost':
                $announcement_text = "{$attackingPlayer}'s Ninjutsu is being increased.";
                break;
            case 'genjutsu_boost':
                $announcement_text = "{$attackingPlayer}'s Genjutsu is being increased.";
                break;
            default:
                break;
        }

        return $announcement_text;
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public function checkTurn(): ?string {
        $this->loadFighters();

        // If turn is still active and user hasn't submitted their move, check for action
        if($this->timeRemaining() > 0 && !$this->playerActionSubmitted()) {
            if(!empty($_POST['attack'])) {
                // Run player attack
                /* notes: Handseal-based jutsu can uniquely fail, triggering a failed_jutsu attack type */
                try {
                    $jutsu_type = $_POST['jutsu_type'];

                    // Check for handseals if ninjutsu/genjutsu
                    if($jutsu_type == 'ninjutsu' or $jutsu_type == 'genjutsu') {
                        if(!$_POST['hand_seals']) {
                            throw new Exception("Please enter hand seals!");
                        }
                        if(is_array($_POST['hand_seals'])) {
                            $seals = array();
                            foreach($_POST['seals'] as $seal) {
                                if(!is_numeric($seal)) {
                                    break;
                                }
                                $seals[] = $seal;
                            }
                            $seal_string = implode('-', $seals);
                        }
                        else {
                            $raw_seals = explode('-', $_POST['hand_seals']);
                            $seals = array();
                            foreach($raw_seals as $seal) {
                                if(!is_numeric($seal)) {
                                    break;
                                }
                                $seals[] = $seal;
                            }
                            $seal_string = implode('-', $seals);
                        }

                        $jutsu_ok = false;

                        $attack_id = 0;
                        foreach($this->default_attacks as $id => $attack) {
                            if($attack->hand_seals == $seal_string) {
                                $jutsu_ok = true;
                                $attack_id = $id;
                                $purchase_type = 'default';
                                $player_jutsu = $attack;
                                break;
                            }
                        }
                        foreach($this->player->jutsu as $id => $jutsu) {
                            if($jutsu->hand_seals == $seal_string) {
                                $jutsu_ok = true;
                                $attack_id = $id;
                                $purchase_type = 'equipped';
                                $player_jutsu = $jutsu;
                                break;
                            }
                        }
                        $jutsu_unique_id = 'J:' . $attack_id . ':' . $this->player->combat_id;

                        // Layered genjutsu check
                        if($jutsu_ok && $jutsu_type == 'genjutsu' && !empty($player_jutsu->parent_jutsu)) {
                            $parent_genjutsu_id = $this->player->combat_id . ':J' . $player_jutsu->parent_jutsu;
                            $parent_jutsu = $this->player->jutsu[$player_jutsu->parent_jutsu];
                            if(!isset($this->active_genjutsu[$parent_genjutsu_id]) or
                                $this->active_genjutsu[$parent_genjutsu_id]['turns'] == $parent_jutsu->effect_length) {
                                throw new Exception($parent_jutsu->name .
                                    ' must be active for 1 turn before using this jutsu!'
                                );
                            }
                        }
                    }

                    // Check jutsu ID if taijutsu
                    else if($jutsu_type == 'taijutsu') {
                        $jutsu_ok = false;
                        $jutsu_id = (int)$_POST['jutsu_id'];
                        $attack_id = null;
                        if(isset($this->default_attacks[$jutsu_id]) && $this->default_attacks[$jutsu_id]->jutsu_type == 'taijutsu') {
                            $jutsu_ok = true;
                            $attack_id = $jutsu_id;
                            $purchase_type = 'default';
                            $player_jutsu = $this->default_attacks[$jutsu_id];
                        }
                        if(isset($this->player->jutsu[$jutsu_id]) && $this->player->jutsu[$jutsu_id]->jutsu_type == 'taijutsu') {
                            $jutsu_ok = true;
                            $attack_id = $jutsu_id;
                            $purchase_type = 'equipped';
                            $player_jutsu = $this->player->jutsu[$jutsu_id];
                        }
                        $jutsu_unique_id = 'J:' . $attack_id . ':' . $this->player->combat_id;
                    }

                    // Check BL jutsu ID if bloodline jutsu
                    else if($jutsu_type == 'bloodline_jutsu' && $this->player->bloodline_id) {
                        $jutsu_ok = false;
                        $jutsu_id = (int)$_POST['jutsu_id'];
                        $attack_id = null;
                        if(isset($this->player->bloodline->jutsu[$jutsu_id])) {
                            $jutsu_ok = true;
                            $attack_id = $jutsu_id;
                            $purchase_type = 'bloodline';
                            $player_jutsu = $this->player->bloodline->jutsu[$jutsu_id];
                        }
                        $jutsu_unique_id = 'BL_J:' . $attack_id . ':' . $this->player->combat_id;
                    }
                    else {
                        throw new Exception("Invalid jutsu selection!");
                    }

                    // Check jutsu cooldown
                    if($jutsu_ok && isset($this->jutsu_cooldowns[$jutsu_unique_id])) {
                        throw new Exception("Cannot use that jutsu, it is on cooldown for " . $this->jutsu_cooldowns[$jutsu_unique_id] . " more turns!");
                    }
                    if(!$jutsu_ok) {
                        throw new Exception("Invalid jutsu!");
                    }
                    if(!$this->player->useJutsu($player_jutsu, $purchase_type . '_jutsu')) {
                        throw new Exception($this->system->message);
                    }

                    // Check for weapon if non-BL taijutsu
                    $weapon_id = 0;
                    if($jutsu_type == 'taijutsu' && $purchase_type != 'bloodline' && $_POST['weapon_id']) {
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
                    if($purchase_type == 'default') {
                        if(!isset($this->default_attacks[$attack_id])) {
                            throw new Exception("Invalid attack!");
                        }
                        $attack_type = 'default_jutsu';
                    }
                    else if($purchase_type == 'bloodline') {
                        $attack_type = 'bloodline_jutsu';
                    }
                    else if(!$jutsu_ok) {
                        $attack_id = 0;
                        $attack_type = 'failed_jutsu';
                    }
                    else {
                        $attack_type = 'equipped_jutsu';
                    }

                    // Log jutsu used
                    $this->setPlayerAction($attack_id, $weapon_id, $attack_type, $jutsu_unique_id, $player_jutsu->jutsu_type);

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
                $effect_win = false;

                // Run turn effects
                $effect_display = '';
                $player1_effect_display = '';
                $player2_effect_display = '';

                if(!empty($this->active_effects)) {
                    foreach($this->active_effects as $id => $effect) {
                        if($effect['target'] == $this->player1->combat_id) {
                            $effect_target =& $this->player1;
                            $effect_display =& $player1_effect_display;
                        }
                        elseif($effect['target'] == $this->player2->combat_id) {
                            $effect_target =& $this->player2;
                            $effect_display =& $player2_effect_display;
                        }
                        else {
                            throw new Exception("Invalid effect target {$effect['target']}");
                        }

                        if($effect['user'] == $this->player1->combat_id) {
                            $effect_user =& $this->player1;
                        }
                        else if($effect['user'] == $this->player2->combat_id) {
                            $effect_user =& $this->player2;
                        }
                        else {
                            throw new Exception("Invalid effect user {$effect['user']}");
                        }

                        $this->applyActiveEffects(
                            $effect_target,
                            $effect_user,
                            $effect,
                            $effect_display,
                            $effect_win
                        );

                        $this->active_effects[$id]['turns']--;
                        if($this->active_effects[$id]['turns'] <= 0) {
                            unset($this->active_effects[$id]);
                        }
                    }
                }
                if(!empty($this->active_genjutsu)) {
                    foreach($this->active_genjutsu as $id => $genjutsu) {
                        if($genjutsu['target'] == $this->player1->combat_id) {
                            $effect_target =& $this->player1;
                            $effect_display =& $player1_effect_display;
                        }
                        else {
                            $effect_target =& $this->player2;
                            $effect_display =& $player2_effect_display;
                        }
                        if($genjutsu['user'] == $this->player1->combat_id) {
                            $effect_user =& $this->player1;
                        }
                        else {
                            $effect_user =& $this->player2;
                        }
                        $this->applyActiveEffects($effect_target, $effect_user, $genjutsu, $effect_display, $effect_win);
                        $this->active_genjutsu[$id]['turns']--;
                        $this->active_genjutsu[$id]['power'] *= 0.9;
                        if($this->active_genjutsu[$id]['turns'] <= 0) {
                            unset($this->active_genjutsu[$id]);
                        }
                        if(isset($genjutsu['first_turn'])) {
                            unset($genjutsu['first_turn']);
                        }
                    }
                }

                // Bloodline active effects
                if(!empty($this->player1->bloodline->combat_boosts)) {
                    foreach($this->player1->bloodline->combat_boosts as $id=>$effect) {
                        $this->applyActiveEffects(
                            $this->player1,
                            $this->player2,
                            $effect,
                            $player1_effect_display,
                            $effect_win
                        );
                    }
                }
                if(!empty($this->player2->bloodline->combat_boosts)) {
                    foreach($this->player2->bloodline->combat_boosts as $id=>$effect) {
                        $this->applyActiveEffects(
                            $this->player2, $this->player1, $effect, $player2_effect_display, $effect_win);
                    }
                }

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
                $player1_battle_text = '';
                $player2_battle_text = '';
                if($this->player1_action) {
                    /** @var Jutsu $player1_jutsu */
                    if($this->player1_attack_type == 'default_jutsu') {
                        $player1_jutsu = $this->default_attacks[$this->player1_jutsu_id];
                        $player1_damage = $this->player1->calcDamage($player1_jutsu, 'default_jutsu');
                        $player1_jutsu->unique_id = 'J:' . $player1_jutsu->id . ':' . $this->player1->combat_id;
                    }
                    else if($this->player1_attack_type == 'equipped_jutsu') {
                        $player1_jutsu = $this->player1->jutsu[$this->player1_jutsu_id];
                        $player1_damage = $this->player1->calcDamage($player1_jutsu, 'equipped_jutsu');
                        $player1_jutsu->unique_id = 'J:' . $player1_jutsu->id . ':' . $this->player1->combat_id;
                    }
                    else if($this->player1_attack_type == 'bloodline_jutsu') {
                        $player1_jutsu = $this->player1->bloodline->jutsu[$this->player1_jutsu_id];
                        $player1_damage = $this->player1->calcDamage($this->player1->bloodline->jutsu[$this->player1_jutsu_id], 'bloodline_jutsu');
                        $player1_jutsu->unique_id = 'BL_J:' . $player1_jutsu->id . ':' . $this->player1->combat_id;
                    }
                    else if($this->player1_attack_type == 'failed_jutsu') {
                        $this->player1_action = false;
                    }
                    // Set weapon data into jutsu
                    if(($this->player1_attack_type == 'default_jutsu' or $this->player1_attack_type == 'equipped_jutsu')
                        && $player1_jutsu->jutsu_type == 'taijutsu' && $this->player1_weapon_id) {
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
                    if($this->player2_attack_type == 'default_jutsu') {
                        $player2_jutsu = $this->default_attacks[$this->player2_jutsu_id];
                        $player2_damage = $this->player2->calcDamage($player2_jutsu, 'default_jutsu');
                        $player2_jutsu->unique_id = 'J:' . $player2_jutsu->id . ':' . $this->player2->combat_id;
                    }
                    else if($this->player2_attack_type == 'equipped_jutsu') {
                        $player2_jutsu = $this->player2->jutsu[$this->player2_jutsu_id];
                        $player2_damage = $this->player2->calcDamage($player2_jutsu, 'equipped_jutsu');
                        $player2_jutsu->unique_id = 'J:' . $player2_jutsu->id . ':' . $this->player2->combat_id;
                    }
                    else if($this->player2_attack_type == 'bloodline_jutsu') {
                        $player2_jutsu = $this->player2->bloodline->jutsu[$this->player2_jutsu_id];
                        $player2_damage = $this->player2->calcDamage($this->player2->bloodline->jutsu[$this->player2_jutsu_id], 'bloodline_jutsu');
                        $player2_jutsu->unique_id = 'BL_J:' . $player2_jutsu->id . ':' . $this->player2->combat_id;
                    }
                    else if($this->player2_attack_type == 'failed_jutsu') {
                        $this->player2_action = false;
                    }
                    // Set weapon data into jutsu
                    if(($this->player2_attack_type == 'default_jutsu' or $this->player2_attack_type == 'equipped_jutsu')
                        && $player2_jutsu->jutsu_type == 'taijutsu' && $this->player2_weapon_id) {
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

                // Buff jutsu (-1 = self effect move)
                if($player1_jutsu->use_type == 'buff') {
                    $player1_jutsu->weapon_id = 0;
                    $player1_jutsu->effect_only = true;
                }
                if($player2_jutsu->use_type == 'buff') {
                    $player2_jutsu->weapon_id = 0;
                    $player2_jutsu->effect_only = true;
                }

                // Barrier jutsu
                if($player1_jutsu->use_type == 'barrier') {
                    $player1_jutsu->weapon_id = 0;
                    $player1_jutsu->effect_only = true;
                }
                if($player2_jutsu->use_type == 'barrier') {
                    $player2_jutsu->weapon_id = 0;
                    $player2_jutsu->effect_only = true;
                }
                if($this->system->debug['battle']) {
                    echo 'P1: ' . $player1_damage . ' / P2: ' . $player2_damage . '<br />';
                }

                // Collision
                if($this->player1_action > 0 && $this->player2_action > 0) {
                    $collision_text = $this->jutsuCollision($this->player1, $this->player2, $player1_damage, $player2_damage, $player1_jutsu, $player2_jutsu);
                }

                // Apply remaining barrier
                if(isset($this->active_effects[$this->player1->combat_id . ':BARRIER'])) {
                    if($this->player1->barrier) {
                        $this->active_effects[$this->player1->combat_id . ':BARRIER']['effect_amount'] = $this->player1->barrier;
                    }
                    else {
                        unset($this->active_effects[$this->player1->combat_id . ':BARRIER']);
                    }
                }
                else if($player1_jutsu->use_type == 'barrier' && $this->player1->barrier) {
                    $effect_id = $this->player1->combat_id . ':BARRIER';
                    $barrier_jutsu = $player1_jutsu;
                    $barrier_jutsu->effect = 'barrier';
                    $barrier_jutsu->effect_length = 1;
                    $this->setEffect($this->player1, $this->player1->combat_id, $barrier_jutsu, $this->player1->barrier, $effect_id, $this->active_effects);
                }
                if(isset($this->active_effects[$this->player2->combat_id . ':BARRIER'])) {
                    if($this->player2->barrier) {
                        $this->active_effects[$this->player2->combat_id . ':BARRIER']['effect_amount'] = $this->player2->barrier;
                    }
                    else {
                        unset($this->active_effects[$this->player2->combat_id . ':BARRIER']);
                    }
                }
                else if($player2_jutsu->use_type == 'barrier' && $this->player2->barrier) {
                    $effect_id = $this->player2->combat_id . ':BARRIER';
                    $barrier_jutsu = $player2_jutsu;
                    $barrier_jutsu->effect = 'barrier';
                    $barrier_jutsu->effect_length = 1;
                    $this->setEffect($this->player2, $this->player2->combat_id, $barrier_jutsu, $this->player2->barrier, $effect_id, $this->active_effects);
                }

                // Apply damage/effects and set display
                if($this->player1_action) {
                    $player1_raw_damage = $player1_damage;
                    if($player1_jutsu->jutsu_type != 'genjutsu' && empty($player1_jutsu->effect_only)) {
                        $player1_damage = $this->player2->calcDamageTaken($player1_damage, $player1_jutsu->jutsu_type);
                        $this->player2->health -= $player1_damage;
                        if($this->player2->health < 0) {
                            $this->player2->health = 0;
                        }
                    }

                    // Weapon effect for taijutsu (IN PROGRESS)
                    if($player1_jutsu->weapon_id) {
                        $effect_id = $this->player1->combat_id . ':W' . $player1_jutsu->weapon_id;
                        if($this->player1->items[$this->player1_weapon_id]['effect'] != 'diffuse') {
                            $this->setEffect($this->player1, $this->player2->combat_id, $player1_jutsu->weapon_effect,
                                $player1_raw_damage, $effect_id, $this->active_effects);
                        }
                    }

                    // Set cooldowns
                    if($player1_jutsu->cooldown > 0) {
                        $this->jutsu_cooldowns[$player1_jutsu->unique_id] = $player1_jutsu->cooldown;
                    }

                    // Genjutsu/effects
                    if($player1_jutsu->jutsu_type == 'genjutsu' && $player1_jutsu->use_type != 'buff') {
                        $genjutsu_id = $this->player1->combat_id . ':J' . $player1_jutsu->id;
                        // Bloodline jutsu ID override
                        if($this->player1_attack_type == 'bloodline_jutsu') {
                            $genjutsu_id = $this->player1->combat_id . ':BL_J' . $player1_jutsu->id;
                        }

                        if($player1_jutsu->effect == 'release_genjutsu') {
                            $intelligence = ($this->player1->intelligence + $this->player1->intelligence_boost - $this->player1->intelligence_nerf);
                            if($intelligence <= 0) {
                                $intelligence = 1;
                            }
                            $release_power = $intelligence * $player1_jutsu->power;
                            foreach($this->active_genjutsu as $id => $genjutsu) {
                                if($genjutsu['target'] == $this->player1->combat_id && !isset($genjutsu['first_turn'])) {
                                    $r_power = $release_power * mt_rand(9, 11);
                                    $g_power = $genjutsu['power'] * mt_rand(9, 11);
                                    if($r_power > $g_power) {
                                        unset($this->active_genjutsu[$id]);
                                        $player1_effect_display .= '[br][player] broke free from [opponent]\'s Genjutsu!';
                                    }
                                }
                            }
                        }
                        else  {
                            $this->setEffect($this->player1, $this->player2->combat_id, $player1_jutsu, $player1_raw_damage, $genjutsu_id, $this->active_genjutsu);
                        }
                    }
                    else if($player1_jutsu->effect != 'none') {
                        $effect_id = $this->player1->combat_id . ':J' . $player1_jutsu->id;
                        // Bloodline jutsu ID override
                        if($this->player1_attack_type == 'bloodline_jutsu') {
                            $effect_id = $this->player1->combat_id . ':BL_J' . $player1_jutsu->id;
                        }
                        $target_id = $this->player2->combat_id;
                        if($player1_jutsu->use_type == 'buff' || ($player1_jutsu->use_type == 'projectile' && strpos($player1_jutsu->effect, '_boost'))) {
                            $target_id = $this->player1->combat_id;
                        }
                        $this->setEffect($this->player1, $target_id, $player1_jutsu, $player1_raw_damage, $effect_id, $this->active_effects);
                    }
                    $text = $player1_jutsu->battle_text;

                    //set player jutsu text color
                    $player1_jutsu_color = Battle::getJutsuTextColor($player1_jutsu->jutsu_type);

                    if($player1_jutsu->jutsu_type != 'genjutsu' && empty($player1_jutsu->effect_only)) {
                        $text .= "<p style=\"font-weight:bold;\">
                            {$this->player1->getName()} deals 
                                <span style=\"color:{$player1_jutsu_color}\">
                                    " . sprintf('%.2f', $player1_damage) . " damage
                                </span>
                            to {$this->player2->getName()}.
                        </p>";
                    }
                    if($player1_effect_display) {
                        $text .= $this->system->clean($player1_effect_display);
                    }

                    if($player1_jutsu->effect != 'none'){
                        $text .= "<br/> <p style=\"font-weight:bold;\">" . "{$this->system->clean($this->determineEffectAnnouncementText($this->player1->getName(), $this->player2->getName(), $player1_jutsu->effect))}" . "</p>";
                    }

                    if($player1_jutsu->weapon_id) {
                        $text .= "<br/> <p style=\"font-weight:bold;\">" . "{$this->system->clean($this->determineEffectAnnouncementText($this->player1->getName(), $this->player2->getName(), $player1_jutsu->weapon_effect->effect))}" . "</p>";
                    }

                    $this->battle_text .= $this->parseCombatText($text, $this->player1, $this->player2);
                }
                else {
                    // Failed jutsu or did nothing (display)
                    if($this->player1_attack_type == 'failed_jutsu') {
                        $this->battle_text .= $this->player1->getName() . ' attempted to perform a jutsu, but failed.';
                    }
                    else {
                        $this->battle_text .= $this->player1->getName() . ' stood still and did nothing.';
                    }
                    if($player1_effect_display) {
                        $this->battle_text .= $this->parseCombatText(
                            $this->system->clean($player1_effect_display),
                            $this->player1,
                            $this->player2
                        );
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
                    if($player2_jutsu->jutsu_type != 'genjutsu' && empty($player2_jutsu->effect_only)) {
                        $player2_damage = $this->player1->calcDamageTaken($player2_damage, $player2_jutsu->jutsu_type);
                        $this->player1->health -= $player2_damage;
                        if($this->player1->health < 0) {
                            $this->player1->health = 0;
                        }
                    }

                    // Weapon effect for taijutsu (IN PROGRESS)
                    if($player2_jutsu->weapon_id) {
                        $effect_id = $this->player2->combat_id . ':W' . $player2_jutsu->weapon_id;
                        if($this->player1->items[$this->player2_weapon_id]['effect'] != 'diffuse') {
                            $this->setEffect($this->player2, $this->player1->combat_id, $player2_jutsu->weapon_effect,
                                $player2_raw_damage, $effect_id, $this->active_effects);
                        }
                    }

                    // Set cooldowns
                    if($player2_jutsu->cooldown > 0) {
                        $this->jutsu_cooldowns[$player2_jutsu->unique_id] = $player2_jutsu->cooldown;
                    }

                    // Genjutsu/effects
                    if($player2_jutsu->jutsu_type == 'genjutsu' && $player2_jutsu->use_type != 'buff') {
                        $genjutsu_id = $this->player2->combat_id . ':J' . $player2_jutsu->id;
                        // Bloodline jutsu ID override
                        if($this->player2_attack_type == 'bloodline_jutsu') {
                            $genjutsu_id = $this->player2->combat_id . ':BL_J' . $player2_jutsu->id;
                        }

                        if($player2_jutsu->effect == 'release_genjutsu') {
                            $intelligence = ($this->player2->intelligence + $this->player2->intelligence_boost - $this->player2->intelligence_nerf);
                            if($intelligence <= 0) {
                                $intelligence = 1;
                            }
                            $release_power = $intelligence * $player2_jutsu->power;
                            foreach($this->active_genjutsu as $id => $genjutsu) {
                                if($genjutsu['target'] == $this->player2->combat_id && !isset($genjutsu['first_turn'])) {
                                    $r_power = $release_power * mt_rand(9, 11);
                                    $g_power = $genjutsu['power'] * mt_rand(9, 11);
                                    if($r_power > $g_power) {
                                        unset($this->active_genjutsu[$id]);
                                        $player1_effect_display .= '[br][player] broke free from [opponent]\'s Genjutsu!';
                                    }
                                }
                            }
                        }
                        else  {
                            $this->setEffect($this->player2, $this->player1->combat_id, $player2_jutsu, $player2_raw_damage, $genjutsu_id, $this->active_genjutsu);
                        }
                    }
                    else if($player2_jutsu->effect != 'none') {
                        $effect_id = $this->player2->combat_id . ':J' . $player2_jutsu->id;
                        // Bloodline jutsu ID override
                        if($this->player2_attack_type == 'bloodline_jutsu') {
                            $effect_id = $this->player2->combat_id . ':BL_J' . $player2_jutsu->id;
                        }
                        $target_id = $this->player1->combat_id;
                        if($player2_jutsu->use_type == 'buff' || ($player2_jutsu->use_type == 'projectile' && strpos($player2_jutsu->effect, '_boost'))) {
                            $target_id = $this->player2->combat_id;
                        }
                        $this->setEffect($this->player2, $target_id, $player2_jutsu, $player2_raw_damage, $effect_id, $this->active_effects);
                    }

                    //set opponent jutsu text color
                    $player2_jutsu_color = Battle::getJutsuTextColor($player2_jutsu->jutsu_type);

                    $text = $player2_jutsu->battle_text;
                    if($player2_jutsu->jutsu_type != 'genjutsu' && empty($player2_jutsu->effect_only)) {
                        $text .= "<p style=\"font-weight:bold;\">
                            {$this->player2->getName()} deals 
                                <span style=\"color:{$player2_jutsu_color}\">
                                    " . sprintf('%.2f', $player2_damage) . " damage
                                </span>
                            to {$this->player1->getName()}.
                        </p>";
					}
                    if($player2_effect_display) {
                        $text .= $this->system->clean($player2_effect_display);
                    }

                    if($player2_jutsu->effect != 'none'){
                        $text .= "<br/> <p style=\"font-weight:bold;\">" . "{$this->system->clean($this->determineEffectAnnouncementText($this->player2->getName(), $this->player1->getName(), $player2_jutsu->effect))}" . "</p>";
                    }

                    if($player2_jutsu->weapon_id) {
                        $text .= "<br/> <p style=\"font-weight:bold;\">" . "{$this->system->clean($this->determineEffectAnnouncementText($this->player2->getName(), $this->player1->getName(), $player1_jutsu->weapon_effect->effect))}" . "</p>";
                    }

                    $this->battle_text .= $this->parseCombatText($text, $this->player2, $this->player1);
                }
                else {
                    // Failed jutsu or did nothing (display)
                    if($this->player2_attack_type == 'failed_jutsu') {
                        $this->battle_text .= $this->player2->getName() . ' attempted to perform a jutsu, but failed.';
                    }
                    else {
                        $this->battle_text .= $this->player2->getName() . ' stood still and did nothing.';
                    }
                    if($player2_effect_display) {
                        $this->battle_text .= $this->parseCombatText(
                            $this->system->clean($player2_effect_display),
                            $this->player2,
                            $this->player1
                        );
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
            // If neither player moved, update turn timer only
            else {
                $this->turn_time = time();
            }
        }
        // Time is up - Player moved, opponent didn't
        // Time is up - Opponent moved, player didnt
        // Time is up - nobody moved
        else {

        }

        $this->checkForWinner();
        $this->updateData();


        return $this->winner;
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

        if($this->opponent instanceof User) {
            $this->opponent->loadData(1, true);
        }

        $this->player1->getInventory();
        $this->player2->getInventory();

        // Apply passive effects
        $effect_target = null;
        $effect_user = null;
        $player1_effect_display = '';
        $player2_effect_display = '';

        // Jutsu passive effects
        if(is_array($this->active_effects)) {
            foreach($this->active_effects as $id => $effect) {
                if($this->system->debug['battle']) {
                    echo "[$id] " . $effect['effect'] . '(' . $effect['effect_amount'] . ') ->' .
                        $effect['target'] . '(' . $effect['turns'] . ' turns left)<br />';
                }

                if($effect['target'] == $this->player1->combat_id) {
                    $effect_target =& $this->player1;
                }
                else {
                    $effect_target =& $this->player2;
                }
                if($effect['user'] == $this->player1->combat_id) {
                    $effect_user =& $this->player1;
                }
                else {
                    $effect_user =& $this->player2;
                }
                $this->applyPassiveEffects($effect_target, $effect_user, $effect);
            }
            unset($effect_target);
            unset($effect_user);
        }
        else {
            $this->active_effects = array();
        }

        // Apply genjutsu passive effects
        if(is_array($this->active_genjutsu)) {
            foreach($this->active_genjutsu as $id => $genjutsu) {
                if($this->system->debug['battle']) {
                    echo "[$id] " . $genjutsu['effect'] . '(' . $genjutsu['effect_amount'] . ') ->' .
                        $genjutsu['target'] . '(' . $genjutsu['turns'] . ' turns left)<br />';
                }

                if($genjutsu['target'] == $this->player1->combat_id) {
                    $effect_target =& $this->player1;
                }
                else {
                    $effect_target =& $this->player2;
                }
                if($genjutsu['user'] == $this->player1->combat_id) {
                    $effect_user =& $this->player1;
                }
                else {
                    $effect_user =& $this->player2;
                }
                $this->applyPassiveEffects($effect_target, $effect_user, $genjutsu);
            }
        }
        else {
            $this->active_genjutsu = array();
        }

        // Apply item passive effects
        if(!empty($this->player1->equipped_armor)) {
            foreach($this->player1->equipped_armor as $item_id) {
                if($this->player1->hasItem($item_id)) {
                    $effect = array(
                        'effect' => $this->player1->items[$item_id]['effect'],
                        'effect_amount' => $this->player1->items[$item_id]['effect_amount']
                    );
                    $this->applyPassiveEffects($this->player1, $this->player2, $effect, $player1_effect_display);
                }
            }
        }
        if(!empty($this->player2->equipped_armor)) {
            foreach($this->player2->equipped_armor as $item_id) {
                if($this->player2->hasItem($item_id)) {
                    $effect = array(
                        'effect' => $this->player2->items[$item_id]['effect'],
                        'effect_amount' => $this->player2->items[$item_id]['effect_amount']
                    );
                    $this->applyPassiveEffects($this->player2, $this->player1, $effect, $player2_effect_display);
                }
            }
        }
    }

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

        echo "<div class='submenu'>
            <ul class='submenu'>
                <li style='width:100%;'><a href='{$self_link}'>Refresh Battle</a></li>
            </ul>
        </div>
        <div class='submenuMargin'></div>";
            $this->system->printMessage();
            echo "<table class='table'>
            <tr>
                <th style='width:50%;'>{$player->getName()}</th>
                <th style='width:50%;'>{$opponent->getName()}</th>
            </tr>";
            $health_percent = round(($player->health / $player->max_health) * 100);
            $chakra_percent = round(($player->chakra / $player->max_chakra) * 100);
            $stamina_percent = round(($player->stamina / $player->max_stamina) * 100);
            $avatar_size = $player->getAvatarSize() . 'px';
        echo "<td>
        <img src='{$player->avatar_link}' style='display:block;max-width:$avatar_size;max-height:$avatar_size;margin:auto;' />
        <label style='width:80px;'>Health:</label>" .
                sprintf("%.2f", $player->health) . '/' . sprintf("%.2f", $player->max_health) . "<br />" .
                "<div style='height:6px;width:250px;border-style:solid;border-width:1px;'>" .
                "<div style='background-color:#C00000;height:6px;width:" . $health_percent . "%;' /></div>" . "</div>" .
                "<label style='width:80px;'>Chakra:</label>" .
                sprintf("%.2f", $player->chakra) . '/' . sprintf("%.2f", $player->max_chakra) . "<br />" .
                "<div style='height:6px;width:250px;border-style:solid;border-width:1px;'>" .
                "<div style='background-color:#0000B0;height:6px;width:" . $chakra_percent . "%;' /></div>" . "</div>" .
                "<label style='width:80px;'>Stamina:</label>" .
                sprintf("%.2f", $player->stamina) . '/' . sprintf("%.2f", $player->max_stamina) . "<br />" .
                "<div style='height:6px;width:250px;border-style:solid;border-width:1px;'>" .
                "<div style='background-color:#00B000;height:6px;width:" . $stamina_percent . "%;' /></div>" . "</div>" .
                "</td>
	<td>";
        $opponent_health_percent = round(($opponent->health / $opponent->max_health) * 100);
        $avatar_size = $opponent->getAvatarSize() . 'px';

        echo "
	<img src='{$opponent->avatar_link}' style='display:block;max-width:$avatar_size;max-height:$avatar_size;margin:auto;' />
	<label style='width:80px;'>Health:</label>" .
            sprintf("%.2f", $opponent->health) . '/' . sprintf("%.2f", $opponent->max_health) . "<br />" .
            "<div style='height:6px;width:250px;border-style:solid;border-width:1px;'>" .
            "<div style='background-color:#C00000;height:6px;width:" . $opponent_health_percent . "%;' /></div>" . "</div>";
        echo "</td></tr></table>";
        echo "<table class='table'>";

        // Battle text display
        if($this->battle_text) {
            $battle_text = $this->system->html_parse(stripslashes($this->battle_text));
            $battle_text = str_replace(array('[br]', '[hr]'), array('<br />', '<hr />'), $battle_text);
            echo "<tr><th colspan='2'>Last turn</th></tr>
		<tr><td style='text-align:center;' colspan='2'>" . $battle_text . "</td></tr>";
        }

        // Trigger win action or display action prompt
        if(!$this->winner) {
            // Prompt for move or display wait message
            echo "<tr><th colspan='2'>Select Action</th></tr>";

            if(!$this->playerActionSubmitted()) {
                $this->renderActionPrompt($player, $this->default_attacks);
            }
            else if(!$this->opponentActionSubmitted()) {
                echo "<tr><td colspan='2'>Please wait for {$opponent->getName()} to select an action.</td></tr>";
            }

            // Turn timer
            echo "<tr><td style='text-align:center;' colspan='2'>
			Time remaining: " . $this->timeRemaining() . " seconds</td></tr>";
        }

        echo "</table>";
    }

    public function isComplete(): bool {
        return $this->winner;
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

    /**
         * @param string $entity_id
         * @return
         * @throws Exception
         */
    protected function loadFighterFromEntityId(string $entity_id): Fighter {
    switch(Battle::getFighterEntityType($entity_id)) {
        case User::ID_PREFIX:
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

    /**
     * @param Fighter $player
     * @param Jutsu[] $default_attacks
     */
    protected function renderActionPrompt(Fighter $player, array $default_attacks) {
        global $self_link;

        $gold_color = '#FDD017';
        echo "<tr><td colspan='2'>
            <div style='margin:0px;position:relative;'>
            <style type='text/css'>
            #handSeals p {
                display: inline-block;
                width: 80px;
                height: 110px;
                margin: 4px;
                position:relative;
            }
            #handSeals img {
                height: 74px;
                width: 74px;
                position: relative;
                z-index: 1;
                border: 3px solid rgba(0,0,0,0);
                border-radius: 5px;
            }
            #handSeals .handsealNumber {
                display: none;
                width: 18px;
                position: absolute;
                z-index: 20;
                text-align: center;
                left: 31px;
                right: 31px;
                bottom: 35px;
                /* Style */
                font-size: 14px;
                font-weight: bold;
                background-color: $gold_color;
                border-radius: 10px;
            }
            #handSeals .handsealTooltip {
                display: block;
                margin: 0px;
                text-align: center;
                height: 16px;
            }
            #handsealOverlay{
                width:100%;
                position:absolute;
                top:0px;
                height:100%;
                background-color:rgba(255,255,255,0.9);
                z-index:50;
                display:none;
            }
            /* WEAPONS */
            #weapons {
                height: 236px;
                padding-left: 20px;
                padding-right: 20px;
            }
            #jutsu {
                padding-left: 5px;
            }
            #jutsu p {
                display:inline-block;
                margin:0px;
                vertical-align:top;
                margin-right:1%;
                text-align:center;
            }
            #jutsu .jutsuName {
                display: inline-block;
                padding: 5px 7px;
                margin-bottom: 10px;
                /* Style */
                background: linear-gradient(#EFEFEF, #E4E4E4);
                border: 1px solid #E0E0E0;
                border-radius: 15px;
                text-align:center;
                box-shadow: 0 0 4px 0 rgba(0,0,0,0);
            }
            #jutsu .jutsuName:last-child {
                margin-bottom: 1px;
            }
            #jutsu .jutsuName:hover {
                background: linear-gradient(#E4E4E4, #EFEFEF);
                cursor: pointer;
            }
            #weapons p.weapon {
                display: inline-block;
                padding: 8px 10px;
                margin-right: 15px;
                vertical-align:top;
                /* Style */
                background-color: rgba(255, 255, 255, 0.1);
                border: 1px solid #C0C0C0;
                border-radius: 10px;
                text-align:center;
                box-shadow: 0 0 4px 0 rgba(0,0,0,0);
            }
            #weapons p.weapon:last-child {
                margin-right: 1px;
            }
            #weapons p.weapon:hover {
                background: rgba(0, 0, 0, 0.1);
                cursor: pointer;
            }
            </style>
            <script type='text/javascript'>
            $(document).ready(function(){
                var hand_seals = new Array();
                var hand_seal_prompt = 'Please enter handseals (click jutsu name for hint):';
                var weapons_prompt = 'Please select a weapon to augment your Taijutsu with:';
                $('#handSeals p img').click(function() {
                    var parent = $(this).parent();
                    var seal = parent.attr('data-handseal');
                    // Select hand seal
                    if(parent.attr('data-selected') == 'no') {
                        parent.attr('data-selected', 'yes');
                        $(this).css('border-color', '$gold_color');
                        parent.children('.handsealNumber').show();
                        hand_seals.splice(hand_seals.length, 0, seal);
                    }
                    // De-select handseal
                    else if(parent.attr('data-selected') == 'yes') {
                        parent.attr('data-selected', 'no');
                        $(this).css('border-color', 'rgba(0,0,0,0)');
                        parent.children('.handsealNumber').hide();
                        for(var x in hand_seals) {
                            if(hand_seals[x] == seal) {
                                hand_seals.splice(x,1);
                                break;
                            }
                        }
                    }
                    // Update display
                    $('#hand_seal_input').val(hand_seals.join('-'));
                    var id = '';
                    for(var x in hand_seals) {
                        id = 'handseal_' + hand_seals[x];
                        $('#' + id).children('.handsealNumber').text((parseInt(x) + 1));
                    }
                });
                var currentlySelectedJutsu = false;
                var lastJutsu, firstJutsu = false;
                $('.jutsuName').click(function(){
        
                    if(lastJutsu != this && firstJutsu) {
        
                        var seals = $(lastJutsu).attr('data-handseals').split('-');
                        for(var ay in seals) {
                            if(!isNaN(parseInt(seals[ay]))) {
                                id = 'handseal_' + seals[ay];
                                $('#' + id + ' img').trigger('click');
                            }
                        }
        
                        lastJutsu = this;
        
                        var new_seals = $(lastJutsu).attr('data-handseals').split('-');
                        for(var ayy in new_seals) {
                            if(!isNaN(parseInt(new_seals[ayy]))) {
                                id = 'handseal_' + new_seals[ayy];
                                $('#' + id + ' img').trigger('click');
                            }
                        }
        
                    }
        
                    if(! firstJutsu) {
                        lastJutsu = this;
                        firstJutsu = true;
                        var seals = $(lastJutsu).attr('data-handseals').split('-');
                        for(var ay in seals) {
                            if(!isNaN(parseInt(seals[ay]))) {
                                id = 'handseal_' + seals[ay];
                                $('#' + id + ' img').trigger('click');
                            }
                        }
                    }
        
                    if(currentlySelectedJutsu != false) {
                        $(currentlySelectedJutsu).css('box-shadow', '0px');
                    }
                    currentlySelectedJutsu = this;
                    $(currentlySelectedJutsu).css('box-shadow', '0px 0px 4px 0px #000000');
                    $('.handsealTooltip').html('&nbsp;');
                    var handseal_string = $(this).attr('data-handseals');
                    var handseal_array = handseal_string.split('-');
                    for(var x in handseal_array) {
                        if(!isNaN(parseInt(handseal_array[x]))) {
                            id = 'handseal_' + handseal_array[x];
                            $('#' + id).children('.handsealTooltip').text((parseInt(x) + 1));
                        }
                    }
                });
                var currentlySelectedWeapon = $('p[data-id=0]');
                $('.weapon').click(function(){
                    if(currentlySelectedWeapon != false) {
                        $(currentlySelectedWeapon).css('box-shadow', '0px');
                    }
                    currentlySelectedWeapon = this;
                    $(currentlySelectedWeapon).css('box-shadow', '0px 0px 4px 0px #000000');
                    $('#weaponID').val( $(this).attr('data-id') );
                });
                var display_state = 'ninjutsu';
                $('#jutsu span.ninjutsu').click(function(){
                    if(display_state != 'ninjutsu' && display_state != 'genjutsu') {
                        $('#textPrompt').text(hand_seal_prompt);
                        $('#weapons').hide();
                        $('#handSeals').show();
                        $('#handsealOverlay').fadeOut();
                    }
                    display_state = 'ninjutsu';
                    $('#jutsuType').val('ninjutsu');
                });
                $('#jutsu span.genjutsu').click(function(){
                    if(display_state != 'genjutsu' && display_state != 'ninjutsu') {
                        $('#textPrompt').text(hand_seal_prompt);
                        $('#weapons').hide();
                        $('#handSeals').show();
                        $('#handsealOverlay').fadeOut();
                    }
                    display_state = 'genjutsu';
                    $('#jutsuType').val('genjutsu');
                });
                $('#jutsu span.taijutsu').click(function(){
                    if(display_state != 'taijutsu') {
                        $('#textPrompt').text(weapons_prompt);
                        $('#handSeals').hide();
                        $('#weapons').show();
                        if(display_state == 'bloodline_jutsu') {
                            $('#handsealOverlay').fadeOut();
                        }
                    }
                    display_state = 'taijutsu';
                    $('#jutsuType').val('taijutsu');
                    $('#jutsuID').val($(this).attr('data-id'));
                });
                $('#jutsu span.bloodline_jutsu').click(function(){
                    if(display_state != 'bloodline_jutsu') {
                        $('#handsealOverlay').fadeIn();
                    }
                    display_state = 'bloodline_jutsu';
                    $('#jutsuType').val('bloodline_jutsu');
                    $('#jutsuID').val($(this).attr('data-id'));
                });
            });
            </script>
            <!--DIV START-->
            <p id='textPrompt' style='text-align:center;'>Please enter handseals (click jutsu name for hint):</p>
            <div id='handSeals'>
            ";
                for($i = 1; $i <= 12; $i++) {
                    echo "<p id='handseal_$i' data-selected='no' data-handseal='$i'>
                    <img src='./images/handseal_$i.png' draggable='false' />
                    <span class='handsealNumber'>1</span>
                    <span class='handsealTooltip'>&nbsp;</span>
                </p>";
                    if($i == 6) {
                        echo "<br />";
                    }
                }
                echo "</div>
            <div id='weapons' style='display:none;'>
            <p class='weapon' data-id='0' style='box-shadow: 0 0 4px 0 #000000;margin-top:14px;'>
            <b>None</b>
            </p>
            ";
                if(is_array($player->equipped_weapons)) {
                    foreach($player->equipped_weapons as $item_id) {
                        echo "<p class='weapon' data-id='$item_id'>" .
                            "<b>" . $player->items[$item_id]['name'] . "</b><br />" .
                            ucwords(str_replace('_', ' ', $player->items[$item_id]['effect'])) .
                            " (" . $player->items[$item_id]['effect_amount'] . "%)" .
                            "</p>";
                    }
                }
                echo "</div>
            <div id='handsealOverlay'>
            </div>
        </td></tr>
        <tr><th colspan='2'>";
            if($player->bloodline_id) {
                $width = '24%';
            }
            else {
                $width = '32%';
            }
            echo "<span style='display:inline-block;width:$width;'>Ninjutsu</span>
            <span style='display:inline-block;width:$width;'>Taijutsu</span>
            <span style='display:inline-block;width:$width;'>Genjutsu</span>" .
                ($player->bloodline_id ? "<span style='display:inline-block;width:$width;'>Bloodline</span>" : '');
        echo "</th></tr>
        <tr><td colspan='2'>
        <div id='jutsu'>";

        // Keyboard hotkeys
        echo "<script type='text/javascript'>
            var nin = 78;
            var gen = 71;
            var tai = 84;
            var bl = 66;
            var def_ault = 68;
            var arr = [];
    
            $(document).keyup(function(event){
        
                //arr->array will hold 2 elements [JutsuName, Number];
        
                //enter key
                if(event.which === 13){
                    document.getElementById('submitbtn').click();
                }
        
                //(If Key is a Letter, Letter will be turned into string for Arr)
                if(event.which === nin) {
                    arr[0] = 'ninjutsu';
                }
                else if(event.which === gen) {
                    arr[0] = 'genjutsu';
                }
                else if(event.which === tai) {
                    arr[0] = 'taijutsu';
                }
                else if(event.which === bl) {
                    arr[0] = 'bloodline';
                }
                else if(event.which === def_ault) {
                    arr[0] = 'default'; /*default*/
                }
        
        
                //if arr[0] is not a valid string, arr will clear
                if(typeof(arr[0]) == null){
                    arr = [];
                }
        
        
                //if user presses correct number (between 0-9) store in Arr[1];
                var key = -1;
                switch (event.which){
                    case 48: 
                    case 96: 
                        key = 0;
                    break;
                    case 49:
                    case 97:
                        key = 1;
                    break;
                    case 50:
                    case 98:
                        key = 2;
                    break;
                    case 51:
                    case 99:
                        key = 3;
                    break;
                    case 52:
                    case 100:
                        key = 4;
                    break;
                    case 53:
                    case 101:
                        key = 5;
                    break;
                    case 54:
                    case 102:
                        key = 6;
                    break;
                    case 55:
                    case 103:
                        key = 7;
                    break;
                    case 56:
                    case 104:
                        key = 8;
                    break;
                    case 57:
                    case 105:
                        key = 9;
                    break;
                }
                arr[1] = key;
        
                //create the array example: array[ninjutsu, 0];
                var classname = arr[0] + arr[1];
                // console.log(classname + ' test input');
        
                //if arr[0] not a string, and arr[1] is not the default -1, continue;
                if(typeof(arr[0]) == 'string' && arr[1] !== -1){
                    //creating the ID name to get the Element to add the click() function to
                    var classname = arr[0] + arr[1];
                    console.log(classname);
                    console.log('selection successful');
                    document.getElementById(classname).click();
        
                    // document.getElementById(classname).addClass('focused') should add something like this
                    //for visual so user knows selection is made
                }
        
                //for this script to work had to add ID's to each jutsu during their button creation
                //needs refactoring for the future but this kinda works for now.
            });
        </script>";

        //Used to change ID #
        $c1 = 0;
        $c2 = 0;
        $c3 = 0;
        // Attack list
        $jutsu_types = array('ninjutsu', 'taijutsu', 'genjutsu');
        for($i = 0; $i < 3; $i++) {
            echo "<p style='width:$width;'>";
            foreach($default_attacks as $attack) {
                if($attack->jutsu_type != $jutsu_types[$i]) {
                    continue;
                }
                echo "<span id='default$c1' class='jutsuName {$jutsu_types[$i]}' data-handseals='" .
                    ($attack->jutsu_type != 'taijutsu' ? $attack->hand_seals : '') . "'
                data-id='{$attack->id}'>" . $attack->name . '<br /><strong>'.'D'.$c1.'</strong></span><br />';
                $c1++;
            }
            if(is_array($player->equipped_jutsu)) {
                foreach($player->equipped_jutsu as $jutsu) {
                    if($player->jutsu[$jutsu['id']]->jutsu_type != $jutsu_types[$i]) {
                        continue;
                    }
                    echo "<span id='{$jutsu_types[$i]}$c2' class='jutsuName {$jutsu_types[$i]}' data-handseals='{$player->jutsu[$jutsu['id']]->hand_seals}'
                    data-id='{$jutsu['id']}'>" . $player->jutsu[$jutsu['id']]->name . '<br /><strong>'.strtoupper($jutsu_types[$i][0]).$c2.'</strong></span><br />';
                    $c2++;
                }
                $c2 = 0;
            }
            echo "</p>";
        }
        // Display bloodline jutsu
        if($player->bloodline_id) {
            echo "<p style='width:$width;margin-right:0;'>";
            if(!empty($player->bloodline->jutsu)) {
                foreach($player->bloodline->jutsu as $id => $jutsu) {
                    echo "<span id='bloodline$c3' class='jutsuName bloodline_jutsu' data-handseals='" . $jutsu->hand_seals . "'" .
                        " data-id='$id'>" . $jutsu->name . '<br /><strong>B' . $c3 . '</strong></span><br />';
                    $c3++;
                }
            }
            echo "</p>";
        }

        $prefill_hand_seals = $_POST['hand_seals'] ?? '';
        $prefill_jutsu_type = $_POST['jutsu_type'] ?? 'ninjutsu';
        $prefill_weapon_id = $_POST['weapon_id'] ?? '0';
        $prefill_jutsu_id = $_POST['jutsu_id'] ?? '';
        echo "<form action='$self_link' method='post'>
            <input type='hidden' id='hand_seal_input' name='hand_seals' value='{$prefill_hand_seals}' />
            <input type='hidden' id='jutsuType' name='jutsu_type' value='{$prefill_jutsu_type}' />
            <input type='hidden' id='weaponID' name='weapon_id' value='{$prefill_weapon_id}' />
            <input type='hidden' id='jutsuID' name='jutsu_id' value='{$prefill_jutsu_id}' />
            <p style='display:block;text-align:center;margin:auto;'>
                <input id='submitbtn' type='submit' name='attack' value='Submit' />
            </p>
        </form>
        </div>";
        echo "</div>
    </td></tr>";
    }

    protected function applyPassiveEffects(Fighter &$target, Fighter &$attacker, &$effect, &$effect_display = ''): bool {
        // Buffs
        if($effect['effect'] == 'ninjutsu_boost') {
            $target->ninjutsu_boost += $effect['effect_amount'];
        }
        else if($effect['effect'] == 'taijutsu_boost') {
            $target->taijutsu_boost += $effect['effect_amount'];
        }
        else if($effect['effect'] == 'genjutsu_boost') {
            $target->genjutsu_boost += $effect['effect_amount'];
        }
        else if($effect['effect'] == 'cast_speed_boost') {
            $target->cast_speed_boost += $effect['effect_amount'];
        }
        else if($effect['effect'] == 'speed_boost' or $effect['effect'] == 'lighten') {
            $target->speed_boost += $effect['effect_amount'];
        }
        else if($effect['effect'] == 'intelligence_boost') {
            $target->intelligence_boost += $effect['effect_amount'];
        }
        else if($effect['effect'] == 'willpower_boost') {
            $target->willpower_boost += $effect['effect_amount'];
        }
        else if($effect['effect'] == 'ninjutsu_resist') {
            $target->ninjutsu_resist += $effect['effect_amount'];
        }
        else if($effect['effect'] == 'genjutsu_resist') {
            $target->genjutsu_resist += $effect['effect_amount'];
        }
        else if($effect['effect'] == 'taijutsu_resist' or $effect['effect'] == 'harden') {
            $target->taijutsu_resist += $effect['effect_amount'];
        }
        else if($effect['effect'] == 'barrier') {
            $target->barrier += $effect['effect_amount'];
        }

        // Debuffs
        $effect_amount = $effect['effect_amount'] - $target->getDebuffResist();
        if($effect_amount < $effect['effect_amount'] * Battle::MIN_DEBUFF_RATIO) {
            $effect_amount = $effect['effect_amount'] * Battle::MIN_DEBUFF_RATIO;
        }

        if($effect['effect'] == 'ninjutsu_nerf') {
            $target->ninjutsu_nerf += $effect_amount;
        }
        else if($effect['effect'] == 'taijutsu_nerf') {
            $target->taijutsu_nerf += $effect_amount;
        }
        else if($effect['effect'] == 'genjutsu_nerf') {
            $target->genjutsu_nerf += $effect_amount;
        }
        else if($effect['effect'] == 'cast_speed_nerf') {
            $target->cast_speed_nerf += $effect_amount;
        }
        else if($effect['effect'] == 'speed_nerf' or $effect['effect'] == 'cripple') {
            $target->speed_nerf += $effect_amount;
        }
        else if($effect['effect'] == 'intelligence_nerf' or $effect['effect'] == 'daze') {
            $target->intelligence_nerf += $effect_amount;
        }
        else if($effect['effect'] == 'willpower_nerf') {
            $target->willpower_nerf += $effect_amount;
        }
        return false;
    }

    function jutsuCollision(
        Fighter &$player, Fighter &$opponent, &$player_damage, &$opponent_damage, $player_jutsu, $opponent_jutsu
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
        if($player_jutsu->use_type == 'barrier') {
            $player_jutsu->effect_amount = $player_damage;
            $player->barrier += $player_damage;
            $player_damage = 0;
        }
        if($opponent_jutsu->use_type == 'barrier') {
            $opponent_jutsu->effect_amount = $opponent_damage;
            $opponent->barrier += $opponent_damage;
            $opponent_damage = 0;
        }
        if($player->barrier && $opponent_jutsu->jutsu_type != 'genjutsu') {
            // Block damage from opponent's attack
            if($player->barrier >= $opponent_damage) {
                $block_amount = $opponent_damage;
            }
            else {
                $block_amount = $player->barrier;
            }
            $block_percent = ($opponent_damage >= 1) ? ($block_amount / $opponent_damage) * 100 : 100;
            $player->barrier -= $block_amount;
            $opponent_damage -= $block_amount;
            if($player->barrier < 0) {
                $player->barrier = 0;
            }
            if($opponent_damage < 0) {
                $opponent_damage = 0;
            }
            // Set display
            $block_percent = round($block_percent, 1);
            $collision_text .= "[player]'s barrier blocked $block_percent% of [opponent]'s damage![br]";
        }
        if($opponent->barrier && $player_jutsu->jutsu_type != 'genjutsu') {
            // Block damage from opponent's attack
            if($opponent->barrier >= $player_damage) {
                $block_amount = $player_damage;
            }
            else {
                $block_amount = $opponent->barrier;
            }
            $block_percent = ($player_damage >= 1) ? ($block_amount / $player_damage) * 100 : 100;
            $opponent->barrier -= $block_amount;
            $player_damage -= $block_amount;
            if($opponent->barrier < 0) {
                $opponent->barrier = 0;
            }
            if($player_damage < 0) {
                $player_damage = 0;
            }
            // Set display
            $block_percent = round($block_percent, 1);
            $collision_text .= "[opponent]'s barrier blocked $block_percent% of [player]'s damage![br]";
        }

        // Quit if barrier was used by one person (no collision remaining)
        if($player_jutsu->use_type == 'barrier' or $opponent_jutsu->use_type == 'barrier') {
            if(isset($player->user_name)) {
                $player_name = $player->user_name;
            }
            else {
                $player_name = $player->name;
            }
            if(isset($opponent->user_name)) {
                $opponent_name = $opponent->user_name;
            }
            else {
                $opponent_name = $opponent->name;
            }
            $collision_text = str_replace(
                array('[player]', '[opponent]',
                    '[gender]', '[gender2]'),
                array($player_name, $opponent_name,
                    ($player->gender == 'Male' ? 'he' : 'she'), ($player->gender == 'Male' ? 'his' : 'her')),
                $collision_text);
            return $collision_text;
        }

        // Weapon diffuse (tai diffuse nin)
        if($player_jutsu->weapon_id && $player_jutsu->weapon_effect->effect == 'diffuse') {
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
        if($opponent_jutsu->weapon_id && $opponent_jutsu->weapon_effect->effect == 'diffuse') {
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

        if($player_jutsu->jutsu_type == 'genjutsu' or $opponent_jutsu->jutsu_type == 'genjutsu') {
            return false;
        }

        $player_min_damage = $player_damage * 0.5;
        if($player_min_damage < 1) {
            $player_min_damage = 1;
        }
        $opponent_min_damage = $opponent_damage * 0.5;
        if($opponent_min_damage < 1) {
            $opponent_min_damage = 1;
        }

        // Apply buffs/nerfs
        $player_speed = $player->speed + $player->speed_boost - $player->speed_nerf;
        $player_speed = 50 + ($player_speed * 0.5);
        if($player_speed <= 0) {
            $player_speed = 1;
        }
        $player_cast_speed = $player->cast_speed + $player->cast_speed_boost - $player->cast_speed_nerf;
        $player_cast_speed = 50 + ($player_cast_speed * 0.5);
        if($player_cast_speed <= 0) {
            $player_cast_speed = 1;
        }

        $opponent_speed = $opponent->speed + $opponent->speed_boost - $opponent->speed_nerf;
        $opponent_speed = 50 + ($opponent_speed * 0.5);
        if($opponent_speed <= 0) {
            $opponent_speed = 1;
        }
        $opponent_cast_speed = $opponent->cast_speed + $opponent->cast_speed_boost - $opponent->cast_speed_nerf;
        $opponent_cast_speed = 50 + ($opponent_cast_speed * 0.5);
        if($opponent_cast_speed <= 0) {
            $opponent_cast_speed = 1;
        }

        // Ratios for damage reduction
        $speed_ratio = 0.8;
        $cast_speed_ratio = 0.8;
        $max_damage_reduction = 0.5;
        if($player_jutsu->jutsu_type == 'ninjutsu') {
            // Nin vs Nin
            if($opponent_jutsu->jutsu_type == 'ninjutsu') {
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
            else if($opponent_jutsu->jutsu_type == 'taijutsu') {
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
        else if($player_jutsu->jutsu_type == 'taijutsu') {
            // Tai vs Tai
            if($opponent_jutsu->jutsu_type == 'taijutsu') {
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
            else if($opponent_jutsu->jutsu_type == 'ninjutsu') {
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
        if(isset($player->user_name)) {
            $player_name = $player->user_name;
        }
        else {
            $player_name = $player->name;
        }
        if(isset($opponent->user_name)) {
            $opponent_name = $opponent->user_name;
        }
        else {
            $opponent_name = $opponent->name;
        }

        $collision_text = str_replace(
            array('[player]', '[opponent]',
                '[gender]', '[gender2]'),
            array($player_name, $opponent_name,
                ($player->gender == 'Male' ? 'he' : 'she'), ($player->gender == 'Male' ? 'his' : 'her')),
            $collision_text);
        return $collision_text;
    }

    protected function setEffect(Fighter &$user, $target_id, Jutsu $jutsu, $raw_damage, $effect_id, &$active_effects) {
        $apply_effect = true;

        $debuff_power = ($jutsu->power <= 0) ? 0 : $raw_damage / $jutsu->power / 15;

        if($this->system->debug['battle_effects']) {
            echo sprintf("JP: %s (%s)<br />", $jutsu->power, $jutsu->effect);
            echo sprintf("%s / %s<br />", $raw_damage, $debuff_power);
        }

        if($jutsu->jutsu_type == 'genjutsu' && !empty($jutsu->parent_jutsu)) {
            $parent_genjutsu_id = $user->combat_id . ':J' . $jutsu->parent_jutsu;
            if(!empty($active_effects[$parent_genjutsu_id]['layer_active'])) {
                $active_effects[$parent_genjutsu_id]['layer_active'] = true;
                $active_effects[$parent_genjutsu_id]['power'] *= 1.1;
            }
            $jutsu->power *= 1.1;
            $jutsu->effect_amount *= 1.1;
        }

        switch($jutsu->effect) {
            case 'residual_damage':
            case 'ninjutsu_boost':
            case 'taijutsu_boost':
            case 'genjutsu_boost':
            case 'ninjutsu_nerf':
            case 'taijutsu_nerf':
            case 'genjutsu_nerf':
            case 'ninjutsu_resist':
            case 'taijutsu_resist':
            case 'genjutsu_resist':
                $jutsu->effect_amount = round($raw_damage * ($jutsu->effect_amount / 100), 2);
                break;
            case 'absorb_chakra':
            case 'absorb_stamina':
                $jutsu->effect_amount = round($raw_damage * ($jutsu->effect_amount / 600), 2);
                break;
            case 'drain_chakra':
            case 'drain_stamina':
                $jutsu->effect_amount = round($raw_damage * ($jutsu->effect_amount / 300), 2);
                break;
            case 'speed_boost':
            case 'cast_speed_boost':
            case 'intelligence_boost':
            case 'willpower_boost':
            case 'cast_speed_nerf':
            case 'speed_nerf':
            case 'intelligence_nerf':
            case 'willpower_nerf':
                $jutsu->effect_amount = round($debuff_power * ($jutsu->effect_amount / 100), 2);
                break;
            case 'barrier':
                $jutsu->effect_amount = $raw_damage;
                break;
            default:
                $apply_effect = false;
                break;
        }

        if($apply_effect) {
            $active_effects[$effect_id] = array(
                'user' => $user->combat_id,
                'target' => $target_id,
                'turns' => $jutsu->effect_length,
                'effect' => $jutsu->effect,
                'effect_amount' => $jutsu->effect_amount,
                'effect_type' => $jutsu->jutsu_type
            );
            if($jutsu->jutsu_type == 'genjutsu') {
                $intelligence = ($user->intelligence + $user->intelligence_boost - $user->intelligence_nerf);
                if($intelligence <= 0) {
                    $intelligence = 1;
                }
                $active_effects[$effect_id]['power'] = $intelligence * $jutsu->power;
                $active_effects[$effect_id]['first_turn'] = true;
            }
        }
    }

    protected function applyActiveEffects(Fighter &$target, Fighter &$attacker, &$effect, &$effect_display, &$winner): bool {
        if($winner && $winner != $target->combat_id) {
            return false;
        }
        if($effect['effect'] == 'residual_damage' || $effect['effect'] == 'bleed') {
            $damage = $target->calcDamageTaken($effect['effect_amount'], $effect['effect_type']);
            $effect_display .= '[br]-'. (isset($target->user_name) ? $target->user_name : $target->name) .
                " takes $damage residual damage-";
            $target->health -= $damage;
            if($target->health < 0) {
                $target->health = 0;
            }
        }
        else if($effect['effect'] == 'heal') {
            $heal = $effect['effect_amount'];
            $effect_display .= '[br]-'. (isset($target->user_name) ? $target->user_name : $target->name) .
                " heals $heal health-";
            $target->health += $heal;
            if($target->health > $target->max_health) {
                $target->health = $target->max_health;
            }
        }
        else if($effect['effect'] == 'drain_chakra') {
            $drain = $target->calcDamageTaken($effect['effect_amount'], $effect['effect_type']);
            $effect_display .= '[br]-'. $attacker->user_name . " drains $drain of " .
                (isset($target->user_name) ? $target->user_name : $target->name) . "'s chakra-";
            $target->chakra -= $drain;
            if($target->chakra < 0) {
                $target->chakra = 0;
            }
        }
        else if($effect['effect'] == 'drain_stamina') {
            $drain = $target->calcDamageTaken($effect['effect_amount'], $effect['effect_type']);
            $effect_display .= '[br]-'. $attacker->user_name . " drains $drain of " .
                (isset($target->user_name) ? $target->user_name : $target->name) . "'s stamina-";
            $target->stamina -= $drain;
            if($target->stamina < 0) {
                $target->stamina = 0;
            }
        }
        else if($effect['effect'] == 'absorb_chakra') {
            $drain = $target->calcDamageTaken($effect['effect_amount'], $effect['effect_type']);
            $effect_display .= '[br]-'. $attacker->user_name . " absorbs $drain of " .
                (isset($target->user_name) ? $target->user_name : $target->name) . "'s chakra-";
            $target->chakra -= $drain;
            if($target->chakra < 0) {
                $target->chakra = 0;
            }
            $attacker->chakra += $drain;
            if($attacker->chakra > $attacker->max_chakra) {
                $attacker->chakra = $attacker->max_chakra;
            }
        }
        else if($effect['effect'] == 'absorb_stamina') {
            $drain = $target->calcDamageTaken($effect['effect_amount'], $effect['effect_type']);
            $effect_display .= '[br]-'. $attacker->user_name . " absorbs $drain of " .
                (isset($target->user_name) ? $target->user_name : $target->name) . "'s stamina-";
            $target->stamina -= $drain;
            if($target->stamina < 0) {
                $target->stamina = 0;
            }
            $attacker->stamina += $drain;
            if($attacker->stamina > $attacker->max_stamina) {
                $attacker->stamina = $attacker->max_stamina;
            }
        }
        if($target->health <= 0) {
            $winner = $attacker->combat_id;
        }
        return false;
    }

    protected function setPlayerAction($attack_id, $weapon_id, $attack_type, string $jutsu_unique_id, string $jutsu_type) {
        if($this->player_side == Battle::TEAM1) {
            $this->player1_action = 1;
            $this->player1_jutsu_id = $attack_id;
            $this->player1_weapon_id = $weapon_id;
            $this->player1_attack_type = $attack_type;

            if($attack_type != 'failed_jutsu') {
                if(isset($this->player1_jutsu_used[$jutsu_unique_id])) {
                    $this->player1_jutsu_used[$jutsu_unique_id]['count']++;
                }
                else {
                    $this->player1_jutsu_used[$jutsu_unique_id] = array();
                    $this->player1_jutsu_used[$jutsu_unique_id]['jutsu_type'] = $jutsu_type;
                    $this->player1_jutsu_used[$jutsu_unique_id]['count'] = 1;
                }
            }
        }
        else {
            $this->player2_action = 1;
            $this->player2_jutsu_id = $attack_id;
            $this->player2_weapon_id = $weapon_id;
            $this->player2_attack_type = $attack_type;

            if($attack_type != 'failed_jutsu') {
                if(isset($this->player2_jutsu_used[$jutsu_unique_id])) {
                    $this->player2_jutsu_used[$jutsu_unique_id]['count']++;
                }
                else {
                    $this->player2_jutsu_used[$jutsu_unique_id] = array();
                    $this->player2_jutsu_used[$jutsu_unique_id]['jutsu_type'] = $jutsu_type;
                    $this->player2_jutsu_used[$jutsu_unique_id]['count'] = 1;
                }
            }
        }
    }

    protected function chooseAndSetAIAction(AI $ai) {
        $jutsu = $ai->chooseMove();

        $attack_id = $jutsu->id;
        $weapon_id = 0;
        $attack_type = 'equipped_jutsu';

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
        
            `active_effects` = '" . json_encode($this->active_effects) . "',
            `active_genjutsu` = '" . json_encode($this->active_genjutsu) . "',
        
            `jutsu_cooldowns` = '" . json_encode($this->jutsu_cooldowns) . "',
        
            `player1_jutsu_used` = '" . json_encode($this->player1_jutsu_used) . "',
            `player2_jutsu_used` = '" . json_encode($this->player2_jutsu_used) . "',
        
            `turn_time` = {$this->turn_time},
            `winner` = '{$this->winner}'

        WHERE `battle_id` = '{$this->battle_id}' LIMIT 1");
    }

    protected function getDefaultAttacks(): array {
        $default_attacks = [];

        $query = "SELECT * FROM `jutsu` WHERE `purchase_type`='1'";
        $result = $this->system->query($query);
        while($row = $this->system->db_fetch($result)) {
            $default_attacks[$row['jutsu_id']] = Jutsu::fromArray($row['jutsu_id'], $row);
        }
        return $default_attacks;
    }

    protected function timeRemaining(): int {
        return Battle::TURN_LENGTH - (time() - $this->turn_time);
    }

    private function playerActionSubmitted(): bool {
        if($this->player_side == Battle::TEAM1 && $this->player1_action) {
            return true;
        }
        if($this->player_side == Battle::TEAM2 && $this->player2_action) {
            return true;
        }
        return false;
    }

    private function opponentActionSubmitted(): bool {
        if($this->opponent_side == Battle::TEAM1 && $this->player1_action) {
            return true;
        }
        if($this->opponent_side == Battle::TEAM2 && $this->player2_action) {
            return true;
        }
        return false;
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

        if($this->winner) {
            $this->player->updateInventory();
        }

        return $this->winner;
    }

    private static function getJutsuTextColor($jutsu_type): string {
        switch ($jutsu_type) {
            case 'ninjutsu':
                return "blue";
            case 'taijutsu':
                return "red";
            case 'genjutsu':
                return "purple";
            case 'none':
            default:
                return "black";
        }
    }

}