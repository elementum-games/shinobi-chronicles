<?php

require_once __DIR__ . '/Battle.php';

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

class BattleManager {
    private System $system;

    private int $battle_id;

    private Battle $battle;

    public User $player;
    public Fighter $opponent;

    public string $player_side;
    public string $opponent_side;

    public array $player_jutsu_used = [];

    // Components

    private BattleEffectsManager $effects;

    /** @var Jutsu[] */
    public array $default_attacks;

    public bool $spectate = false;

    /**
     * BattleManager constructor.
     * @param System $system
     * @param User   $player
     * @param int    $battle_id
     * @param bool   $spectate
     * @param bool   $load_fighters
     * @throws Exception
     */
    public function __construct(System $system, User $player, int $battle_id, bool $spectate = false, bool $load_fighters = true) {
        $this->system = $system;
        $this->battle_id = $battle_id;
        $this->player = $player;
        $this->spectate = $spectate;
        $this->battle = new Battle($system, $player, $battle_id);

        $this->default_attacks = $this->getDefaultAttacks();

        $this->effects = new BattleEffectsManager(
            $system,
            json_decode($this->battle->raw_active_effects, true),
            json_decode($this->battle->raw_active_genjutsu, true)
        );

        if($load_fighters) {
            $this->loadFighters();

            $this->effects->applyPassiveEffects($this->battle->player1, $this->battle->player2);
        }
    }

    /**
     * @throws Exception
     */
    public function loadFighters() {
        if($this->player->id == $this->battle->player1_id) {
            $this->player_side = Battle::TEAM1;
            $this->opponent_side = Battle::TEAM2;

            $this->battle->player1 = $this->player;
            $this->player_jutsu_used =& $this->battle->player1_jutsu_used;
        }
        else if($this->player->id == $this->battle->player2_id) {
            $this->player_side = Battle::TEAM2;
            $this->opponent_side = Battle::TEAM1;

            $this->battle->player2 = $this->player;
            $this->player_jutsu_used =& $this->battle->player2_jutsu_used;
        }
        else {
            $this->player_side = Battle::TEAM1;
            $this->opponent_side = Battle::TEAM2;
        }

        $this->battle->loadFighters();

        if($this->player_side == Battle::TEAM1) {
            $this->opponent =& $this->battle->player2;
        }
        else {
            $this->opponent =& $this->battle->player1;
        }

        if(!$this->spectate && !$this->battle->isComplete()) {
            if($this->battle->player1 instanceof User && $this->battle->player1->battle_id != $this->battle_id) {
                $this->stopBattle();
                return;
            }
            if($this->battle->player2 instanceof User && $this->battle->player2->battle_id != $this->battle_id) {
                $this->stopBattle();
                return;
            }
        }
    }

    /**
     * @throws Exception
     */
    public function runActions() {
        // Run turn effects
        $this->effects->applyActiveEffects($this->battle->player1, $this->battle->player2);

        // Decrement cooldowns
        if(!empty($this->battle->jutsu_cooldowns)) {
            foreach($this->battle->jutsu_cooldowns as $id=>$cooldown) {
                $this->battle->jutsu_cooldowns[$id]--;
                if($this->battle->jutsu_cooldowns[$id] == 0) {
                    unset($this->battle->jutsu_cooldowns[$id]);
                }
            }
        }

        // Check for each player's action
        $this->battle->battle_text = '';

        // Calculate damage
        $player1_damage = 0;
        $player2_damage = 0;

        /** @var ?Jutsu $player1_jutsu */
        $player1_jutsu = null;
        /** @var ?Jutsu $player2_jutsu */
        $player2_jutsu = null;

        if($this->battle->player1_action) {
            if($this->battle->player1_attack_type == Jutsu::PURCHASE_TYPE_DEFAULT) {
                $player1_jutsu = $this->default_attacks[$this->battle->player1_jutsu_id];
                $player1_damage = $this->battle->player1->calcDamage($player1_jutsu);
                $player1_jutsu->setCombatId($this->battle->player1->combat_id);
            }
            else if($this->battle->player1_attack_type == Jutsu::PURCHASE_TYPE_PURCHASEABLE) {
                $player1_jutsu = $this->battle->player1->jutsu[$this->battle->player1_jutsu_id];
                $player1_damage = $this->battle->player1->calcDamage($player1_jutsu);
                $player1_jutsu->setCombatId($this->battle->player1->combat_id);
            }
            else if($this->battle->player1_attack_type == Jutsu::PURCHASE_TYPE_BLOODLINE) {
                $player1_jutsu = $this->battle->player1->bloodline->jutsu[$this->battle->player1_jutsu_id];
                $player1_damage = $this->battle->player1->calcDamage($this->battle->player1->bloodline->jutsu[$this->battle->player1_jutsu_id]);
                $player1_jutsu->setCombatId($this->battle->player1->combat_id);
            }
            else {
                throw new Exception("Invalid p1 attack type! {$this->battle->player1_attack_type}");
            }

            // Set weapon data into jutsu
            if(($this->battle->player1_attack_type == Jutsu::PURCHASE_TYPE_DEFAULT or $this->battle->player1_attack_type == Jutsu::PURCHASE_TYPE_PURCHASEABLE)
                && $player1_jutsu->jutsu_type == Jutsu::TYPE_TAIJUTSU && $this->battle->player1_weapon_id) {
                // Apply element to jutsu
                if($this->battle->player1->items[$this->battle->player1_weapon_id]['effect'] == 'element') {
                    $player1_jutsu->element = $this->battle->player1->elements['first'];
                    $player1_damage *= 1 + ($this->battle->player1->items[$this->battle->player1_weapon_id]['effect_amount'] / 100);
                }
                // Set effect in jutsu
                else {
                    $player1_jutsu->setWeapon(
                        $this->battle->player1_weapon_id,
                        $this->battle->player1->items[$this->battle->player1_weapon_id]['effect'],
                        $this->battle->player1->items[$this->battle->player1_weapon_id]['effect_amount']
                    );
                }
            }

            if($player1_jutsu->isAllyTargetType()) {
                $player1_jutsu->weapon_id = 0;
                $player1_jutsu->effect_only = true;
            }
        }
        if($this->battle->player2_action) {
            if($this->battle->player2_attack_type == Jutsu::PURCHASE_TYPE_DEFAULT) {
                $player2_jutsu = $this->default_attacks[$this->battle->player2_jutsu_id];
                $player2_damage = $this->battle->player2->calcDamage($player2_jutsu);
                $player2_jutsu->setCombatId($this->battle->player2->combat_id);
            }
            else if($this->battle->player2_attack_type == Jutsu::PURCHASE_TYPE_PURCHASEABLE) {
                $player2_jutsu = $this->battle->player2->jutsu[$this->battle->player2_jutsu_id];
                $player2_damage = $this->battle->player2->calcDamage($player2_jutsu);
                $player2_jutsu->setCombatId($this->battle->player2->combat_id);
            }
            else if($this->battle->player2_attack_type == Jutsu::PURCHASE_TYPE_BLOODLINE) {
                $player2_jutsu = $this->battle->player2->bloodline->jutsu[$this->battle->player2_jutsu_id];
                $player2_damage = $this->battle->player2->calcDamage($this->battle->player2->bloodline->jutsu[$this->battle->player2_jutsu_id]);
                $player2_jutsu->setCombatId($this->battle->player2->combat_id);
            }
            else {
                throw new Exception("Invalid player 2 attack type {$this->battle->player2_attack_type}");
            }

            // Set weapon data into jutsu
            if(($this->battle->player2_attack_type == Jutsu::PURCHASE_TYPE_DEFAULT or $this->battle->player2_attack_type == Jutsu::PURCHASE_TYPE_PURCHASEABLE)
                && $player2_jutsu->jutsu_type == Jutsu::TYPE_TAIJUTSU && $this->battle->player2_weapon_id) {
                // Apply element to jutsu
                if($this->battle->player2->items[$this->battle->player2_weapon_id]['effect'] == 'element') {
                    $player2_jutsu->element = $this->battle->player2->elements['first'];
                    $player2_damage *= 1 + ($this->battle->player2->items[$this->battle->player2_weapon_id]['effect_amount'] / 100);
                }
                // Set effect in jutsu
                else {
                    $player2_jutsu->setWeapon(
                        $this->battle->player2_weapon_id,
                        $this->battle->player2->items[$this->battle->player2_weapon_id]['effect'],
                        $this->battle->player2->items[$this->battle->player2_weapon_id]['effect_amount'],
                    );
                }
            }

            if($player2_jutsu->isAllyTargetType()) {
                $player2_jutsu->weapon_id = 0;
                $player2_jutsu->effect_only = true;
            }
        }

        if($this->system->debug['battle']) {
            echo 'P1: ' . $player1_damage . ' / P2: ' . $player2_damage . '<br />';
        }

        // Collision
        $collision_text = null;
        if($this->battle->player1_action > 0 && $this->battle->player2_action > 0) {
            $collision_text = $this->jutsuCollision($this->battle->player1, $this->battle->player2, $player1_damage, $player2_damage, $player1_jutsu, $player2_jutsu);
        }

        // Apply remaining barrier
        if($player1_jutsu) {
            $this->effects->setBarrier($this->battle->player1, $player1_jutsu);
        }
        if($player2_jutsu) {
            $this->effects->setBarrier($this->battle->player2, $player2_jutsu);
        }

        // Apply damage/effects and set display
        if($this->battle->player1_action) {
            $player1_raw_damage = $player1_damage;
            if($player1_jutsu->jutsu_type != Jutsu::TYPE_GENJUTSU && empty($player1_jutsu->effect_only)) {
                $player1_damage = $this->battle->player2->calcDamageTaken($player1_damage, $player1_jutsu->jutsu_type);
                $this->battle->player2->health -= $player1_damage;
                if($this->battle->player2->health < 0) {
                    $this->battle->player2->health = 0;
                }
            }

            // Weapon effect for taijutsu (IN PROGRESS)
            if($player1_jutsu->weapon_id) {
                if($this->battle->player1->items[$this->battle->player1_weapon_id]['effect'] != 'diffuse') {
                    $this->effects->setEffect($this->battle->player1, $this->battle->player2->combat_id, $player1_jutsu->weapon_effect,
                        $player1_raw_damage
                    );
                }
            }

            // Set cooldowns
            if($player1_jutsu->cooldown > 0) {
                $this->battle->jutsu_cooldowns[$player1_jutsu->combat_id] = $player1_jutsu->cooldown;
            }

            // Effects
            if($player1_jutsu->hasEffect()) {
                if($player1_jutsu->use_type == Jutsu::USE_TYPE_BUFF || in_array($player1_jutsu->effect, BattleEffect::$buff_effects)) {
                    $target_id = $this->battle->player1->combat_id;
                }
                else {
                    $target_id = $this->battle->player2->combat_id;
                }

                $this->effects->setEffect(
                    $this->battle->player1,
                    $target_id,
                    $player1_jutsu,
                    $player1_raw_damage
                );
            }

            $text = $player1_jutsu->battle_text;
            $player1_jutsu_color = BattleManager::getJutsuTextColor($player1_jutsu->jutsu_type);

            if($player1_jutsu->jutsu_type != Jutsu::TYPE_GENJUTSU && empty($player1_jutsu->effect_only)) {
                $text .= "<p style=\"font-weight:bold;\">
                            {$this->battle->player1->getName()} deals
                                <span style=\"color:{$player1_jutsu_color}\">
                                    " . sprintf('%.2f', $player1_damage) . " damage
                                </span>
                            to {$this->battle->player2->getName()}.
                        </p>";
            }
            if($this->effects->hasDisplays($this->battle->player1)) {
                $text .= '<p>' . $this->effects->getDisplayText($this->battle->player1) . '</p>';
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

            $this->battle->battle_text .= $this->parseCombatText($text, $this->battle->player1, $this->battle->player2);
        }
        else {
            $this->battle->battle_text .= $this->battle->player1->getName() . ' stood still and did nothing.';
            if($this->effects->hasDisplays($this->battle->player1)) {
                $this->battle->battle_text .= '<p>' .
                    $this->parseCombatText(
                        $this->effects->getDisplayText($this->battle->player1),
                        $this->battle->player1,
                        $this->battle->player2
                    ) .
                    '</p>';
            }
        }

        if($collision_text) {
            $collision_text = $this->parseCombatText($collision_text, $this->battle->player1, $this->battle->player2);
            $this->battle->battle_text .= '[br][hr]' . $this->system->clean($collision_text);
        }
        $this->battle->battle_text .= '[br][hr]';

        // Apply damage/effects and set display
        if($this->battle->player2_action) {
            $player2_raw_damage = $player2_damage;
            if($player2_jutsu->jutsu_type != Jutsu::TYPE_GENJUTSU && empty($player2_jutsu->effect_only)) {
                $player2_damage = $this->battle->player1->calcDamageTaken($player2_damage, $player2_jutsu->jutsu_type);
                $this->battle->player1->health -= $player2_damage;
                if($this->battle->player1->health < 0) {
                    $this->battle->player1->health = 0;
                }
            }

            // Weapon effect for taijutsu (IN PROGRESS)
            if($player2_jutsu->weapon_id) {
                if($this->battle->player2->items[$this->battle->player2_weapon_id]['effect'] != 'diffuse') {
                    $this->effects->setEffect($this->battle->player2, $this->battle->player1->combat_id, $player2_jutsu->weapon_effect,
                        $player2_raw_damage
                    );
                }
            }

            // Set cooldowns
            if($player2_jutsu->cooldown > 0) {
                $this->battle->jutsu_cooldowns[$player2_jutsu->combat_id] = $player2_jutsu->cooldown;
            }

            // Genjutsu/effects
            if($player2_jutsu->hasEffect()) {
                if($player2_jutsu->use_type == Jutsu::USE_TYPE_BUFF || in_array($player2_jutsu->effect, BattleEffect::$buff_effects)) {
                    $target_id = $this->battle->player2->combat_id;
                }
                else {
                    $target_id = $this->battle->player1->combat_id;
                }
                $this->effects->setEffect($this->battle->player2, $target_id, $player2_jutsu, $player2_raw_damage);
            }

            //set opponent jutsu text color
            $player2_jutsu_color = BattleManager::getJutsuTextColor($player2_jutsu->jutsu_type);

            $text = $player2_jutsu->battle_text;
            if($player2_jutsu->jutsu_type != Jutsu::TYPE_GENJUTSU && empty($player2_jutsu->effect_only)) {
                $text .= "<p style=\"font-weight:bold;\">
                            {$this->battle->player2->getName()} deals
                                <span style=\"color:{$player2_jutsu_color}\">
                                    " . sprintf('%.2f', $player2_damage) . " damage
                                </span>
                            to {$this->battle->player1->getName()}.
                        </p>";
            }
            if($this->effects->hasDisplays($this->battle->player2)) {
                $text .= "<p>" . $this->effects->getDisplayText($this->battle->player2) . "</p>";
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

            $this->battle->battle_text .= $this->parseCombatText($text, $this->battle->player2, $this->battle->player1);
        }
        else {
            $this->battle->battle_text .= $this->battle->player2->getName() . ' stood still and did nothing.';
            if($this->effects->hasDisplays($this->battle->player2)) {
                $this->battle->battle_text .= "<p>" . $this->parseCombatText(
                        $this->effects->getDisplayText($this->battle->player2),
                        $this->battle->player2,
                        $this->battle->player1
                    ) . "</p>";
            }
        }

        // Update battle
        $this->battle->turn_time = time();
        $this->battle->player1_action = 0;
        $this->battle->player2_action = 0;

        $this->battle->player1_health = $this->battle->player1->health;
        $this->battle->player2_health = $this->battle->player2->health;

        $this->battle->player1->updateData();
        $this->battle->player1->updateInventory();

        $this->battle->player2->updateData();
        $this->battle->player2->updateInventory();
    }

    /**
     * @throws Exception
     * @noinspection PhpUnusedLocalVariableInspection
     */
    public function renderBattle() {
        global $self_link;

        if($this->battle->player === $this->battle->player1) {
            $player = $this->battle->player1;
            $opponent = $this->battle->player2;
        }
        else if($this->battle->player === $this->battle->player2) {
            $player = $this->battle->player2;
            $opponent = $this->battle->player1;
        }
        else {
            $player = $this->battle->player1;
            $opponent = $this->battle->player2;
        }

        $refresh_link = $this->spectate ? "{$self_link}&battle_id={$this->battle->battle_id}" : $self_link;

        $spectate = $this->spectate;
        $battleManager = $this;
        $battle = $this->battle;
        $system = $this->system;

        require 'templates/battle/battle_interface.php';
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public function checkTurn(): ?string {
        // If someone is not in battle, this will be set
        if($this->battle->winner) {
            return $this->battle->winner;
        }

        if($this->battle->isPreparationPhase()) {
            try {
                if (isset($_POST['attack'])) {
                    $item_id = $_POST['item_id'] ?? null;
                    if ($item_id && $this->battle->player->hasItem($item_id)) {
                        $item = $this->battle->player->items[$item_id];

                        $max_health = $this->battle->player->max_health * (Battle::MAX_PRE_FIGHT_HEAL_PERCENT / 100);

                        if ($this->battle->player->health >= $max_health) {
                            throw new Exception("You can't heal any further!");
                        }
                        if ($item['effect'] === 'heal') {
                            if (--$this->battle->player->items[$item_id]['quantity'] === 0) {
                                unset($this->battle->player->items[$item_id]);
                            }

                            $this->battle->player->health += $item['effect_amount'];
                            if ($this->battle->player->health >= $max_health) {
                                $this->battle->player->health = $max_health;
                            }

                            $this->battle->player->updateData();
                            $this->battle->player->updateInventory();
                            $this->battle->battle_text .= sprintf("%s used a %s and healed for %.2f[br]", $this->battle->player->user_name, $item['name'], $item['effect_amount']);
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
        if($this->battle->timeRemaining() > 0 && !$this->playerActionSubmitted()) {
            if(!empty($_POST['attack'])) {
                // Run player attack
                try {
                    $jutsu_type = $_POST['jutsu_type'];

                    // Check for handseals if ninjutsu/genjutsu
                    if($jutsu_type == Jutsu::TYPE_NINJUTSU or $jutsu_type == Jutsu::TYPE_GENJUTSU) {
                        if(!$_POST['hand_seals']) {
                            throw new Exception("Please enter hand seals!");
                        }

                        $player_jutsu = $this->getJutsuFromHandSeals($this->battle->player, $_POST['hand_seals']);

                        // Layered genjutsu check
                        if($player_jutsu && $player_jutsu->jutsu_type == Jutsu::TYPE_GENJUTSU && !empty($player_jutsu->parent_jutsu)) {
                            $this->effects->assertParentGenjutsuActive($this->battle->player, $player_jutsu);
                        }
                    }

                    // Check jutsu ID if taijutsu
                    else if($jutsu_type == Jutsu::TYPE_TAIJUTSU) {
                        $jutsu_id = (int)$_POST['jutsu_id'];

                        $player_jutsu = $this->getJutsuFromId($this->battle->player, $jutsu_id);
                    }
                    // Check BL jutsu ID if bloodline jutsu
                    else if($jutsu_type == 'bloodline_jutsu' && $this->battle->player->bloodline_id) {
                        $jutsu_id = (int)$_POST['jutsu_id'];

                        $player_jutsu = null;
                        if(isset($this->battle->player->bloodline->jutsu[$jutsu_id])) {
                            $player_jutsu = $this->battle->player->bloodline->jutsu[$jutsu_id];
                            $player_jutsu->setCombatId($this->battle->player->combat_id);
                        }
                    }
                    else {
                        throw new Exception("Invalid jutsu selection!");
                    }

                    // Check jutsu cooldown
                    if(!$player_jutsu) {
                        throw new Exception("Invalid jutsu!");
                    }
                    if(isset($this->battle->jutsu_cooldowns[$player_jutsu->combat_id])) {
                        throw new Exception("Cannot use that jutsu, it is on cooldown for " . $this->battle->jutsu_cooldowns[$player_jutsu->combat_id] . " more turns!");
                    }

                    if(!$this->battle->player->useJutsu($player_jutsu)) {
                        throw new Exception($this->system->message);
                    }

                    // Check for weapon if non-BL taijutsu
                    $weapon_id = 0;
                    if($jutsu_type == Jutsu::TYPE_TAIJUTSU && !empty($_POST['weapon_id'])) {
                        $weapon_id = (int)$this->system->clean($_POST['weapon_id']);
                        if($weapon_id && $this->battle->player->hasItem($weapon_id)) {
                            if(array_search($weapon_id, $this->battle->player->equipped_weapons) === false) {
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
        if($this->battle->timeRemaining() <= 0 || ($this->battle->player1_action && $this->battle->player2_action)) {
            if($this->battle->player1_action or $this->battle->player2_action) {
                $this->runActions();
            }
            // If neither player moved, update turn timer only
            else {
                $this->battle->turn_time = time();
            }
        }

        $this->checkForWinner();
        $this->updateData();

        return $this->battle->winner;
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
            $this->battle->player1_action = 1;
            $this->battle->player1_jutsu_id = $jutsu->id;
            $this->battle->player1_weapon_id = $weapon_id;
            $this->battle->player1_attack_type = $jutsu->purchase_type;

            if(isset($this->battle->player1_jutsu_used[$jutsu->combat_id])) {
                $this->battle->player1_jutsu_used[$jutsu->combat_id]['count']++;
            }
            else {
                $this->battle->player1_jutsu_used[$jutsu->combat_id] = array();
                $this->battle->player1_jutsu_used[$jutsu->combat_id]['jutsu_type'] = $jutsu->jutsu_type;
                $this->battle->player1_jutsu_used[$jutsu->combat_id]['count'] = 1;
            }
        }
        else {
            $this->battle->player2_action = 1;
            $this->battle->player2_jutsu_id = $jutsu->id;
            $this->battle->player2_weapon_id = $weapon_id;
            $this->battle->player2_attack_type = $jutsu->purchase_type;

            if(isset($this->battle->player2_jutsu_used[$jutsu->combat_id])) {
                $this->battle->player2_jutsu_used[$jutsu->combat_id]['count']++;
            }
            else {
                $this->battle->player2_jutsu_used[$jutsu->combat_id] = array();
                $this->battle->player2_jutsu_used[$jutsu->combat_id]['jutsu_type'] = $jutsu->jutsu_type;
                $this->battle->player2_jutsu_used[$jutsu->combat_id]['count'] = 1;
            }
        }
    }

    /**
     * @param Fighter $ai
     * @throws Exception
     */
    protected function chooseAndSetAIAction(Fighter $ai) {
        if(!($ai instanceof AI)) {
            throw new Exception("Calling chooseAndSetAIAction on non-AI!");
        }

        $jutsu = $ai->chooseMove();

        $attack_id = $jutsu->id;
        $weapon_id = 0;
        $attack_type = Jutsu::PURCHASE_TYPE_PURCHASEABLE;

        if($this->opponent_side == Battle::TEAM1) {
            $this->battle->player1_action = 1;
            $this->battle->player1_jutsu_id = $attack_id;
            $this->battle->player1_weapon_id = $weapon_id;
            $this->battle->player1_attack_type = $attack_type;
        }
        else {
            $this->battle->player2_action = 1;
            $this->battle->player2_jutsu_id = $attack_id;
            $this->battle->player2_weapon_id = $weapon_id;
            $this->battle->player2_attack_type = $attack_type;
        }
    }

    protected function stopBattle() {
        $this->battle->winner = Battle::DRAW;
        $this->updateData();
    }


    // Status checks
    public function isComplete(): bool {
        return $this->battle->isComplete();
    }

    private function checkForWinner(): string {
        if($this->battle->isComplete()) {
            return $this->battle->winner;
        }

        if($this->battle->player1->health > 0 && $this->battle->player2->health <= 0) {
            $this->battle->winner = Battle::TEAM1;
        }
        else if($this->battle->player2->health > 0 && $this->battle->player1->health <= 0) {
            $this->battle->winner = Battle::TEAM2;
        }
        else if($this->battle->player1->health <= 0 && $this->battle->player2->health <= 0) {
            $this->battle->winner = Battle::DRAW;
        }

        if($this->battle->winner && !$this->spectate) {
            $this->battle->player->updateInventory();
        }

        return $this->battle->winner;
    }

    public function playerActionSubmitted(): bool {
        if($this->player_side == Battle::TEAM1 && $this->battle->player1_action) {
            return true;
        }
        if($this->player_side == Battle::TEAM2 && $this->battle->player2_action) {
            return true;
        }
        return false;
    }

    public function opponentActionSubmitted(): bool {
        if($this->opponent_side == Battle::TEAM1 && $this->battle->player1_action) {
            return true;
        }
        if($this->opponent_side == Battle::TEAM2 && $this->battle->player2_action) {
            return true;
        }
        return false;
    }

    /**
     * @throws Exception
     */
    public function isPlayerWinner(): bool {
        if(!$this->isComplete()) {
            throw new Exception("Cannot call isPlayerWinner() check before battle is complete!");
        }

        return $this->battle->winner === $this->player_side;
    }

    /**
     * @throws Exception
     */
    public function isOpponentWinner(): bool {
        if(!$this->isComplete()) {
            throw new Exception("Cannot call isPlayerWinner() check before battle is complete!");
        }

        return $this->battle->winner === $this->opponent_side;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isDraw(): bool {
        if(!$this->isComplete()) {
            throw new Exception("Cannot call isDraw() check before battle is complete!");
        }

        return $this->battle->winner === Battle::DRAW;
    }

    public function updateData() {
        if($this->spectate) {
            return;
        }

        $this->battle->raw_active_effects = json_encode($this->effects->active_effects);
        $this->battle->raw_active_genjutsu = json_encode($this->effects->active_genjutsu);

        $this->battle->updateData();
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

    public function getBattleType(): int {
        return $this->battle->battle_type;
    }
}